<?php

namespace App\Services\Sms;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    /**
     * @return array{ok: bool, reason?: string, status?: int, body?: string, request?: array<string, mixed>}
     */
    public function send(string $toPhone, string $message, bool $debug = false): array
    {
        if (! $this->isConfigured()) {
            Log::warning('SMS send skipped: gateway not configured');

            return ['ok' => false, 'reason' => 'config_missing'];
        }

        $config = SystemSetting::smsGatewayConfig();
        $normalizedPhone = $this->normalizePhone($toPhone);

        if ($normalizedPhone === '') {
            return ['ok' => false, 'reason' => 'invalid_phone'];
        }

        $query = [
            'username' => $config['username'],
            'password' => $config['password'],
            'from' => $config['sender_id'],
            'to' => $normalizedPhone,
            'text' => $message,
        ];

        $requestMeta = [
            'method' => 'GET',
            'url' => $config['api_url'],
            'to' => $normalizedPhone,
            'from' => $config['sender_id'],
        ];

        try {
            $response = Http::timeout((int) config('sms.timeout', 15))
                ->acceptJson()
                ->get($config['api_url'], $query);

            $body = $response->body();

            if ($response->successful()) {
                $rejection = $this->detectProviderRejection($body);

                if ($rejection !== null) {
                    Log::error('SMS rejected by provider', [
                        'to' => $normalizedPhone,
                        'reason' => $rejection,
                        'response' => $body,
                    ]);

                    return $debug
                        ? ['ok' => false, 'status' => $response->status(), 'body' => $body, 'reason' => $rejection, 'request' => $requestMeta]
                        : ['ok' => false, 'reason' => $rejection];
                }

                Log::info('SMS sent', ['to' => $normalizedPhone]);

                return $debug
                    ? ['ok' => true, 'status' => $response->status(), 'body' => $body, 'request' => $requestMeta]
                    : ['ok' => true, 'body' => $body];
            }

            Log::error('SMS send failed', [
                'status' => $response->status(),
                'body' => $body,
            ]);

            return $debug
                ? ['ok' => false, 'status' => $response->status(), 'body' => $body, 'request' => $requestMeta]
                : ['ok' => false, 'reason' => 'http_error', 'body' => $body];
        } catch (\Throwable $e) {
            Log::error('SMS send exception: '.$e->getMessage());

            return $debug
                ? ['ok' => false, 'error' => $e->getMessage(), 'request' => $requestMeta]
                : ['ok' => false, 'reason' => 'exception'];
        }
    }

    public function isConfigured(): bool
    {
        $config = SystemSetting::smsGatewayConfig();

        return $config['username'] !== '' && $config['password'] !== '';
    }

    public function normalizePhone(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        $value = preg_replace('/\s+/', '', $phone) ?? '';
        $value = ltrim($value, '+');

        if (str_starts_with($value, '255')) {
            return $value;
        }

        if (str_starts_with($value, '0')) {
            return '255'.substr($value, 1);
        }

        if (preg_match('/^\d{9}$/', $value)) {
            return '255'.$value;
        }

        return $value;
    }

    private function detectProviderRejection(string $body): ?string
    {
        $data = json_decode($body, true);

        if (! is_array($data) || ! isset($data['messages']) || ! is_array($data['messages'])) {
            return null;
        }

        foreach ($data['messages'] as $msg) {
            $status = $msg['status'] ?? null;

            if (! is_array($status)) {
                continue;
            }

            $group = (string) ($status['groupName'] ?? '');

            if (preg_match('/REJECTED|FAILED|ERROR/i', $group)) {
                return (string) ($status['description'] ?? $status['name'] ?? 'Message rejected by provider');
            }
        }

        return null;
    }
}
