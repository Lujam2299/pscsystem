<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

final class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'remember_token', 'token',
        'api_token', 'current_password', 'rfc', 'curp', 'nss',
        'cuenta_bancaria', 'bank_account',
    ];

    public function record(
        string $module,
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'metadata' => $this->sanitize($metadata),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    private function sanitize(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && $this->isSensitiveKey($key)) {
                continue;
            }

            $sanitized[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        return in_array($normalized, self::SENSITIVE_KEYS, true)
            || str_contains($normalized, 'password')
            || str_contains($normalized, 'token');
    }
}
