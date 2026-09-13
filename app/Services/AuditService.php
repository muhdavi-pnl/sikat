<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditService
{
    /**
     * Hidden/sensitive fields to sanitize from logs.
     */
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'access_token',
        'secret',
        'token',
    ];

    /**
     * Record an authentication event.
     */
    public function logAuth(
        string $eventType,
        string $action,
        ?User $user = null,
        array $metadata = [],
        string $status = 'success'
    ): ?AuditLog {
        $request = request();
        $user = $user ?? Auth::user();

        $accountDetails = $this->resolveUserDetails($user, $metadata);

        $payload = [
            'user_id' => $user?->id,
            'user_name' => $accountDetails['name'],
            'user_email' => $accountDetails['email'],
            'role' => $accountDetails['role'],
            'event_type' => $eventType,
            'action' => $action,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $this->getUserAgent($request),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'status' => $status,
            'properties' => $this->sanitizeData($metadata),
        ];

        return $this->persistLog($payload);
    }

    /**
     * Record a model database modification (created, updated, deleted, restored).
     */
    public function logModel(
        string $eventType,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ?AuditLog {
        $request = request();
        $user = Auth::user();
        $accountDetails = $this->resolveUserDetails($user);

        $modelName = class_basename($model);
        $action = $description ?? $this->formatModelAction($eventType, $model);

        $payload = [
            'user_id' => $user?->id,
            'user_name' => $accountDetails['name'],
            'user_email' => $accountDetails['email'],
            'role' => $accountDetails['role'],
            'event_type' => $eventType,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => (string) $model->getKey(),
            'old_values' => $oldValues ? $this->sanitizeData($oldValues) : null,
            'new_values' => $newValues ? $this->sanitizeData($newValues) : null,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $this->getUserAgent($request),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'status' => 'success',
            'properties' => [
                'model_class' => get_class($model),
                'model_id' => $model->getKey(),
            ],
        ];

        return $this->persistLog($payload);
    }

    /**
     * Record a security / access control event.
     */
    public function logSecurity(
        string $eventType,
        string $action,
        array $metadata = [],
        string $status = 'warning'
    ): ?AuditLog {
        $request = request();
        $user = Auth::user();
        $accountDetails = $this->resolveUserDetails($user, $metadata);

        $payload = [
            'user_id' => $user?->id,
            'user_name' => $accountDetails['name'],
            'user_email' => $accountDetails['email'],
            'role' => $accountDetails['role'],
            'event_type' => $eventType,
            'action' => $action,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $this->getUserAgent($request),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'status' => $status,
            'properties' => $this->sanitizeData($metadata),
        ];

        return $this->persistLog($payload);
    }

    /**
     * Persist log to database and mirror to application log file.
     */
    protected function persistLog(array $payload): ?AuditLog
    {
        try {
            $auditLog = AuditLog::create($payload);

            $this->writeToStructuredFileLog($payload);

            return $auditLog;
        } catch (Throwable $e) {
            Log::error('Failed to create audit log: ' . $e->getMessage(), [
                'payload' => $payload,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Mirror audit entry to Laravel Log channel for syslog/SIEM compatibility.
     */
    protected function writeToStructuredFileLog(array $payload): void
    {
        $logContext = [
            'event' => $payload['event_type'],
            'user_id' => $payload['user_id'],
            'user_email' => $payload['user_email'],
            'role' => $payload['role'],
            'ip' => $payload['ip_address'],
            'status' => $payload['status'],
        ];

        if ($payload['status'] === 'failed' || str_starts_with($payload['event_type'], 'security.')) {
            Log::warning("[AUDIT] {$payload['action']}", $logContext);
        } else {
            Log::info("[AUDIT] {$payload['action']}", $logContext);
        }
    }

    /**
     * Resolve user name, email, and role snapshots.
     */
    protected function resolveUserDetails(?User $user, array $metadata = []): array
    {
        if ($user) {
            $roleName = method_exists($user, 'getRoleNames')
                ? ($user->getRoleNames()->first() ?? 'user')
                : 'user';

            return [
                'name' => $user->name ?? 'User #' . $user->id,
                'email' => $user->email,
                'role' => $roleName,
            ];
        }

        return [
            'name' => $metadata['attempted_name'] ?? ($metadata['name'] ?? null),
            'email' => $metadata['attempted_email'] ?? ($metadata['email'] ?? null),
            'role' => 'guest',
        ];
    }

    /**
     * Sanitize sensitive attributes recursively.
     */
    public function sanitizeData(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $clean = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $this->sensitiveFields, true)) {
                $clean[$key] = '********';
            } elseif (is_array($value)) {
                $clean[$key] = $this->sanitizeData($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /**
     * Format descriptive model action label.
     */
    protected function formatModelAction(string $eventType, Model $model): string
    {
        $base = class_basename($model);
        $identifier = $model->name ?? $model->nama ?? $model->nip ?? $model->email ?? '#' . $model->getKey();

        return match ($eventType) {
            'model.created' => "Menambahkan data {$base} ({$identifier})",
            'model.updated' => "Memperbarui data {$base} ({$identifier})",
            'model.deleted' => "Menghapus data {$base} ({$identifier})",
            'model.restored' => "Memulihkan data {$base} ({$identifier})",
            'model.force_deleted' => "Menghapus permanen data {$base} ({$identifier})",
            default => "Aktivitas pada {$base} ({$identifier})",
        };
    }

    /**
     * Safely resolve client IP address.
     */
    protected function getClientIp(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return $request->ip() ?: $request->server('REMOTE_ADDR');
    }

    /**
     * Safely resolve user agent string.
     */
    protected function getUserAgent(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return substr((string) $request->userAgent(), 0, 500);
    }
}
