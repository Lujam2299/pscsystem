<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TraccarClient
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function devices(): array
    {
        return $this->request()
            ->get($this->endpoint('devices'))
            ->throw()
            ->json();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function positions(): array
    {
        return $this->request()
            ->get($this->endpoint('positions'))
            ->throw()
            ->json();
    }

    /**
     * Generate a short-lived token for the browser WebSocket connection.
     */
    public function generateSocketToken(): string
    {
        $ttl = max(1, min((int) config('services.traccar.socket_token_ttl', 1), 5));
        $expiration = now()->addMinutes($ttl)->utc()->format('Y-m-d\TH:i:s\Z');

        $token = $this->request()
            ->asForm()
            ->post($this->endpoint('session/token'), [
                'expiration' => $expiration,
            ])
            ->throw()
            ->body();

        $token = trim($token);

        if ($token === '') {
            throw new RuntimeException('Traccar returned an empty WebSocket token.');
        }

        return $token;
    }

    public function socketTokenExpiresAt(): string
    {
        $ttl = max(1, min((int) config('services.traccar.socket_token_ttl', 1), 5));

        return now()->addMinutes($ttl)->utc()->toIso8601String();
    }

    private function request(): PendingRequest
    {
        $token = trim((string) config('services.traccar.token'));

        if ($token === '') {
            throw new RuntimeException('Traccar API token is not configured.');
        }

        return Http::acceptJson()
            ->withToken($token)
            ->timeout(max(1, (int) config('services.traccar.timeout', 5)));
    }

    private function endpoint(string $path): string
    {
        $apiUrl = rtrim(trim((string) config('services.traccar.api_url')), '/');

        if ($apiUrl === '') {
            throw new RuntimeException('Traccar API URL is not configured.');
        }

        return $apiUrl.'/'.ltrim($path, '/');
    }
}
