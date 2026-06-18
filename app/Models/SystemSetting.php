<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::where('group', $group)->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        return is_array($value) && array_key_exists('data', $value) ? $value['data'] : $value;
    }

    public static function setValue(string $group, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => ['data' => $value]],
        );
    }

    public static function platformCurrency(): string
    {
        return strtoupper((string) self::getValue('billing', 'currency', 'USD'));
    }

    public static function defaultChurchCurrency(): string
    {
        return self::platformCurrency();
    }

    public static function churchMaintenanceEnabled(): bool
    {
        return (bool) self::getValue('system', 'maintenance_mode', false);
    }

    public static function churchMaintenanceMessage(): string
    {
        $message = trim((string) self::getValue('system', 'maintenance_message', ''));

        return $message !== ''
            ? $message
            : 'The system is temporarily unavailable for maintenance. Please try again later.';
    }

    /**
     * @return array{api_url: string, username: string, password: string, sender_id: string}
     */
    public static function smsGatewayConfig(): array
    {
        return [
            'api_url' => (string) (self::getValue('sms', 'api_url') ?: config('sms.api_url')),
            'username' => (string) (self::getValue('sms', 'username') ?: config('sms.username')),
            'password' => (string) (self::getValue('sms', 'password') ?: config('sms.password')),
            'sender_id' => (string) (self::getValue('sms', 'sender_id') ?: config('sms.sender_id')),
        ];
    }

    public static function smsGatewayConfigured(): bool
    {
        $config = self::smsGatewayConfig();

        return $config['username'] !== '' && $config['password'] !== '';
    }
}
