<?php

namespace App\Services\Owner;

use App\Models\DatabaseBackupLog;
use App\Models\SystemSetting;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupService
{
    public function __construct(
        private readonly SmsGatewayService $sms,
    ) {}

    /**
     * @return array{ok: bool, log: DatabaseBackupLog}
     */
    public function run(): array
    {
        $startedAt = now();
        $filename = $this->backupFilename();
        $localGz = null;

        try {
            $this->assertReady();

            $sqlPath = $this->dumpDatabase($filename);
            $localGz = $this->gzip($sqlPath, $this->localDirectory().DIRECTORY_SEPARATOR.$filename);
            @unlink($sqlPath);

            $size = filesize($localGz) ?: 0;
            $fileId = $this->driveClient()->upload($localGz, $filename, $this->folderId() ?: null);

            $this->pruneOldDriveBackups();

            $log = DatabaseBackupLog::create([
                'filename' => $filename,
                'google_file_id' => $fileId,
                'size_bytes' => $size,
                'status' => DatabaseBackupLog::STATUS_SUCCESS,
                'message' => 'Uploaded to Google Drive.',
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $this->notifyOwnerBySms($log);

            return ['ok' => true, 'log' => $log];
        } catch (Throwable $e) {
            $log = DatabaseBackupLog::create([
                'filename' => $filename,
                'google_file_id' => null,
                'size_bytes' => $localGz && is_file($localGz) ? (filesize($localGz) ?: null) : null,
                'status' => DatabaseBackupLog::STATUS_FAILED,
                'message' => Str::limit($e->getMessage(), 2000),
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $this->notifyOwnerBySms($log);

            return ['ok' => false, 'log' => $log];
        } finally {
            if (is_string($localGz) && is_file($localGz)) {
                @unlink($localGz);
            }
        }
    }

    public function isEnabled(): bool
    {
        return (bool) SystemSetting::getValue('backup', 'enabled', config('backup.enabled'));
    }

    public function isConfigured(): bool
    {
        return $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->refreshToken() !== '';
    }

    public function connectedEmail(): ?string
    {
        $email = trim((string) SystemSetting::getValue('backup', 'google_email', ''));

        return $email !== '' ? $email : null;
    }

    public function redirectUri(): string
    {
        return route('owner.settings.backup.google.callback');
    }

    /**
     * @return array{
     *     enabled: bool,
     *     folder_id: string,
     *     keep_count: int,
     *     run_at: string,
     *     configured: bool,
     *     connected: bool,
     *     connected_email: ?string,
     *     client_id: string,
     *     has_client_secret: bool,
     *     redirect_uri: string,
     *     notify_sms: bool,
     *     notify_phone: string,
     *     sms_gateway_ready: bool
     * }
     */
    public function settings(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'folder_id' => $this->folderId(),
            'keep_count' => $this->keepCount(),
            'run_at' => $this->runAt(),
            'configured' => $this->isConfigured(),
            'connected' => $this->refreshToken() !== '',
            'connected_email' => $this->connectedEmail(),
            'client_id' => $this->clientId(),
            'has_client_secret' => $this->clientSecret() !== '',
            'redirect_uri' => $this->redirectUri(),
            'notify_sms' => $this->smsNotifyEnabled(),
            'notify_phone' => $this->notifyPhone(),
            'sms_gateway_ready' => $this->sms->isConfigured(),
        ];
    }

    public function saveSettings(
        bool $enabled,
        string $folderId,
        int $keepCount,
        string $runAt,
        ?string $clientId = null,
        ?string $clientSecret = null,
        bool $notifySms = false,
        string $notifyPhone = '',
    ): void {
        SystemSetting::setValue('backup', 'enabled', $enabled);
        SystemSetting::setValue('backup', 'folder_id', trim($folderId));
        SystemSetting::setValue('backup', 'keep_count', max(1, min(90, $keepCount)));
        SystemSetting::setValue('backup', 'run_at', $runAt);
        SystemSetting::setValue('backup', 'notify_sms', $notifySms);
        SystemSetting::setValue('backup', 'notify_phone', $this->sms->normalizePhone($notifyPhone));

        if (is_string($clientId)) {
            SystemSetting::setValue('backup', 'google_client_id', trim($clientId));
        }

        if (is_string($clientSecret) && trim($clientSecret) !== '') {
            SystemSetting::setValue('backup', 'google_client_secret', trim($clientSecret));
        }
    }

    public function connectUrl(): string
    {
        $clientId = $this->clientId();

        if ($clientId === '' || $this->clientSecret() === '') {
            throw new RuntimeException('Save the Google Client ID and Client secret first.');
        }

        $state = Str::random(40);
        session(['google_drive_oauth_state' => $state]);

        return GoogleDriveBackupClient::authorizationUrl($clientId, $this->redirectUri(), $state);
    }

    public function completeConnection(string $code, string $state): void
    {
        $expected = (string) session('google_drive_oauth_state');
        session()->forget('google_drive_oauth_state');

        if ($expected === '' || ! hash_equals($expected, $state)) {
            throw new RuntimeException('Google connection expired. Try Connect Google Drive again.');
        }

        $tokens = GoogleDriveBackupClient::exchangeCode(
            $this->clientId(),
            $this->clientSecret(),
            $this->redirectUri(),
            $code,
        );

        SystemSetting::setValue('backup', 'google_refresh_token', $tokens['refresh_token']);
        SystemSetting::setValue('backup', 'google_email', $tokens['email'] ?? '');
    }

    public function disconnect(): void
    {
        SystemSetting::setValue('backup', 'google_refresh_token', '');
        SystemSetting::setValue('backup', 'google_email', '');
    }

    private function assertReady(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Save the Google OAuth client and click Connect Google Drive first.');
        }
    }

    private function dumpDatabase(string $filename): string
    {
        File::ensureDirectoryExists($this->localDirectory());

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $sqlPath = $this->localDirectory().DIRECTORY_SEPARATOR.Str::replaceLast('.sql.gz', '.sql', $filename);

        return match ($driver) {
            'mysql', 'mariadb' => $this->dumpMysql($sqlPath),
            'sqlite' => $this->dumpSqlite($sqlPath),
            default => throw new RuntimeException("Database driver [{$driver}] is not supported for backups."),
        };
    }

    private function dumpMysql(string $sqlPath): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $mysqldump = $this->mysqldumpPath();
        $defaultsFile = $this->writeMysqlDefaultsFile($config);

        try {
            $process = new Process([
                $mysqldump,
                '--defaults-extra-file='.$defaultsFile,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                $config['database'],
            ]);
            $process->setTimeout(600);
            $handle = fopen($sqlPath, 'wb');

            if ($handle === false) {
                throw new RuntimeException('Could not create the database dump file.');
            }

            try {
                $process->run(function (string $type, string $buffer) use ($handle): void {
                    if ($type === Process::OUT) {
                        fwrite($handle, $buffer);
                    }
                });
            } finally {
                fclose($handle);
            }

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'mysqldump failed.');
            }
        } finally {
            @unlink($defaultsFile);
        }

        if (! is_file($sqlPath) || filesize($sqlPath) === 0) {
            throw new RuntimeException('mysqldump produced an empty backup file.');
        }

        return $sqlPath;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function writeMysqlDefaultsFile(array $config): string
    {
        $path = $this->localDirectory().DIRECTORY_SEPARATOR.'mysqldump-'.Str::uuid().'.cnf';
        $password = str_replace(['\\', '"'], ['\\\\', '\"'], (string) ($config['password'] ?? ''));

        File::put($path, implode("\n", [
            '[client]',
            'user="'.str_replace('"', '\"', (string) ($config['username'] ?? '')).'"',
            'password="'.$password.'"',
            'host="'.($config['host'] ?? '127.0.0.1').'"',
            'port="'.($config['port'] ?? '3306').'"',
            '',
        ]));

        return $path;
    }

    private function dumpSqlite(string $sqlPath): string
    {
        $database = (string) config('database.connections.'.config('database.default').'.database');

        if ($database === ':memory:' || $database === '') {
            throw new RuntimeException('SQLite in-memory databases cannot be backed up to Google Drive.');
        }

        if (! is_file($database)) {
            throw new RuntimeException('SQLite database file was not found.');
        }

        if (! copy($database, $sqlPath)) {
            throw new RuntimeException('Could not copy the SQLite database file.');
        }

        return $sqlPath;
    }

    private function gzip(string $source, string $destination): string
    {
        $in = fopen($source, 'rb');
        $out = gzopen($destination, 'wb9');

        if ($in === false || $out === false) {
            throw new RuntimeException('Could not compress the database backup.');
        }

        try {
            while (! feof($in)) {
                $chunk = fread($in, 1024 * 1024);
                if ($chunk === false) {
                    throw new RuntimeException('Could not read the database dump while compressing.');
                }
                gzwrite($out, $chunk);
            }
        } finally {
            fclose($in);
            gzclose($out);
        }

        return $destination;
    }

    private function pruneOldDriveBackups(): void
    {
        $keep = $this->keepCount();
        $files = $this->driveClient()->listBackupFiles($this->folderId() ?: null);
        $backups = array_values(array_filter(
            $files,
            fn (array $file): bool => str_ends_with((string) ($file['name'] ?? ''), '.sql.gz')
        ));
        $overflow = count($backups) - $keep;

        if ($overflow <= 0) {
            return;
        }

        foreach (array_slice($backups, 0, $overflow) as $file) {
            if (! empty($file['id'])) {
                $this->driveClient()->delete((string) $file['id']);
            }
        }
    }

    private function driveClient(): GoogleDriveBackupClient
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Connect Google Drive in Owner Settings before running a backup.');
        }

        return new GoogleDriveBackupClient([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $this->refreshToken(),
        ]);
    }

    private function clientId(): string
    {
        return trim((string) SystemSetting::getValue('backup', 'google_client_id', config('backup.google_client_id')));
    }

    private function clientSecret(): string
    {
        return trim((string) SystemSetting::getValue('backup', 'google_client_secret', config('backup.google_client_secret')));
    }

    private function refreshToken(): string
    {
        return trim((string) SystemSetting::getValue('backup', 'google_refresh_token', ''));
    }

    private function folderId(): string
    {
        return trim((string) SystemSetting::getValue('backup', 'folder_id', config('backup.folder_id')));
    }

    private function keepCount(): int
    {
        return max(1, (int) SystemSetting::getValue('backup', 'keep_count', config('backup.keep_count')));
    }

    public function runAt(): string
    {
        $value = trim((string) SystemSetting::getValue('backup', 'run_at', config('backup.run_at')));

        return preg_match('/^\d{2}:\d{2}$/', $value) ? $value : '02:00';
    }

    public function smsMessage(DatabaseBackupLog $log): string
    {
        $app = $this->appDisplayName();

        if ($log->isSuccess()) {
            $size = $log->size_bytes ? number_format($log->size_bytes / 1048576, 2).' MB' : '';

            return trim("{$app}: Backup imefanikiwa. {$log->filename}".($size !== '' ? " ({$size})" : ''));
        }

        $reason = trim((string) $log->message);
        if (strlen($reason) > 120) {
            $reason = substr($reason, 0, 117).'...';
        }

        return trim("{$app}: Backup imeshindikana.".($reason !== '' ? " {$reason}" : ''));
    }

    private function appDisplayName(): string
    {
        try {
            $name = trim((string) SystemSetting::getValue('general', 'app_name', config('app.name')));
        } catch (Throwable) {
            $name = (string) config('app.name', 'Waumini Link');
        }

        return $name !== '' ? $name : 'Waumini Link';
    }

    private function notifyOwnerBySms(DatabaseBackupLog $log): void
    {
        if (! $this->smsNotifyEnabled()) {
            return;
        }

        $phone = $this->notifyPhone();

        if ($phone === '' || ! $this->sms->isConfigured()) {
            Log::warning('Backup SMS skipped: phone or SMS gateway is not configured.');

            return;
        }

        try {
            $result = $this->sms->send($phone, $this->smsMessage($log));

            if (! ($result['ok'] ?? false)) {
                Log::warning('Backup SMS was not delivered', [
                    'reason' => $result['reason'] ?? $result['body'] ?? 'unknown',
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Backup SMS failed: '.$e->getMessage());
        }
    }

    private function smsNotifyEnabled(): bool
    {
        return (bool) SystemSetting::getValue('backup', 'notify_sms', false);
    }

    private function notifyPhone(): string
    {
        $saved = $this->sms->normalizePhone(
            (string) SystemSetting::getValue('backup', 'notify_phone', '')
        );

        if ($saved !== '') {
            return $saved;
        }

        return $this->sms->normalizePhone(
            (string) SystemSetting::getValue('general', 'support_phone', '')
        );
    }

    private function localDirectory(): string
    {
        return (string) config('backup.local_path');
    }

    private function backupFilename(): string
    {
        $name = Str::slug((string) config('app.name', 'waumini-link')) ?: 'waumini-link';

        return $name.'-'.now()->format('Y-m-d-His').'.sql.gz';
    }

    private function mysqldumpPath(): string
    {
        $configured = trim((string) config('backup.mysqldump_path'));

        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = array_filter([
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump' || is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('mysqldump was not found. Set BACKUP_MYSQLDUMP_PATH in .env.');
    }
}
