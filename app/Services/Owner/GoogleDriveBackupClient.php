<?php

namespace App\Services\Owner;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveBackupClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';

    private const FILES_URL = 'https://www.googleapis.com/drive/v3/files';

    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    public const SCOPES = [
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    /**
     * @param  array{client_id: string, client_secret: string, refresh_token: string}  $oauth
     */
    public function __construct(
        private readonly array $oauth,
    ) {}

    public static function authorizationUrl(string $clientId, string $redirectUri, string $state): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);
    }

    /**
     * @return array{refresh_token: string, access_token: string, email: ?string}
     */
    public static function exchangeCode(string $clientId, string $clientSecret, string $redirectUri, string $code): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google OAuth token exchange failed: '.$response->body());
        }

        $refreshToken = $response->json('refresh_token');
        $accessToken = $response->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Google did not return an access token.');
        }

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw new RuntimeException('Google did not return a refresh token. Disconnect the app from your Google account and connect again.');
        }

        $email = null;
        $profile = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        if ($profile->successful() && is_string($profile->json('email'))) {
            $email = $profile->json('email');
        }

        return [
            'refresh_token' => $refreshToken,
            'access_token' => $accessToken,
            'email' => $email,
        ];
    }

    public function upload(string $localPath, string $filename, ?string $folderId = null): string
    {
        $token = $this->accessToken();
        $size = filesize($localPath);

        if ($size === false) {
            throw new RuntimeException('Could not read backup file size.');
        }

        $metadata = [
            'name' => $filename,
            'mimeType' => 'application/gzip',
        ];

        if (is_string($folderId) && $folderId !== '') {
            $metadata['parents'] = [$folderId];
        }

        $session = Http::withToken($token)
            ->withHeaders([
                'X-Upload-Content-Type' => 'application/gzip',
                'X-Upload-Content-Length' => (string) $size,
            ])
            ->post(self::UPLOAD_URL.'?uploadType=resumable&supportsAllDrives=true', $metadata);

        if (! $session->successful()) {
            throw new RuntimeException('Google Drive upload session failed: '.$session->body());
        }

        $location = $session->header('Location');

        if (! is_string($location) || $location === '') {
            throw new RuntimeException('Google Drive did not return an upload URL.');
        }

        $stream = fopen($localPath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Could not open backup file for upload.');
        }

        try {
            $upload = Http::withToken($token)
                ->withBody($stream, 'application/gzip')
                ->withHeaders([
                    'Content-Length' => (string) $size,
                ])
                ->put($location);
        } finally {
            fclose($stream);
        }

        if (! $upload->successful()) {
            throw new RuntimeException('Google Drive upload failed: '.$upload->body());
        }

        $fileId = $upload->json('id');

        if (! is_string($fileId) || $fileId === '') {
            throw new RuntimeException('Google Drive upload succeeded but no file ID was returned.');
        }

        return $fileId;
    }

    public function accountEmail(): ?string
    {
        $profile = Http::withToken($this->accessToken())
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if ($profile->successful() && is_string($profile->json('email'))) {
            return $profile->json('email');
        }

        return null;
    }

    /**
     * @return list<array{id: string, name: string, createdTime: string}>
     */
    public function listBackupFiles(?string $folderId = null): array
    {
        $token = $this->accessToken();
        $clauses = ['trashed = false', "name contains '.sql.gz'"];

        if (is_string($folderId) && $folderId !== '') {
            $clauses[] = sprintf("'%s' in parents", $folderId);
        }

        $response = Http::withToken($token)
            ->get(self::FILES_URL, [
                'q' => implode(' and ', $clauses),
                'spaces' => 'drive',
                'fields' => 'files(id,name,createdTime)',
                'orderBy' => 'createdTime',
                'pageSize' => 100,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Could not list Google Drive backups: '.$response->body());
        }

        $files = $response->json('files') ?? [];

        return is_array($files) ? array_values($files) : [];
    }

    public function delete(string $fileId): void
    {
        $response = Http::withToken($this->accessToken())
            ->delete(self::FILES_URL.'/'.$fileId.'?supportsAllDrives=true');

        if (! $response->successful() && $response->status() !== 404) {
            throw new RuntimeException('Could not delete old Google Drive backup: '.$response->body());
        }
    }

    private function accessToken(): string
    {
        $clientId = $this->oauth['client_id'] ?? '';
        $clientSecret = $this->oauth['client_secret'] ?? '';
        $refreshToken = $this->oauth['refresh_token'] ?? '';

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            throw new RuntimeException('Save the Google Client ID, Client secret, and Refresh Token in Owner Settings.');
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google Drive authentication failed: '.$response->body());
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Google Drive did not return an access token.');
        }

        return $token;
    }
}
