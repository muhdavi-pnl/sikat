<?php

namespace App\Models\Concerns;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            if ($model->shouldAudit('created')) {
                $newValues = $model->getAuditAttributes();
                app(AuditService::class)->logModel('model.created', $model, null, $newValues);
            }
        });

        static::updated(function (Model $model) {
            if ($model->shouldAudit('updated')) {
                $dirty = $model->getDirty();
                $ignored = $model->getAuditIgnoredAttributes();

                $oldValues = [];
                $newValues = [];

                foreach ($dirty as $key => $newValue) {
                    if (in_array($key, $ignored, true)) {
                        continue;
                    }

                    $oldValues[$key] = $model->getOriginal($key);
                    $newValues[$key] = $newValue;
                }

                if (! empty($newValues)) {
                    app(AuditService::class)->logModel('model.updated', $model, $oldValues, $newValues);
                }
            }
        });

        static::deleted(function (Model $model) {
            if ($model->shouldAudit('deleted')) {
                $isForceDelete = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();
                $eventType = $isForceDelete ? 'model.force_deleted' : 'model.deleted';
                $oldValues = $model->getAuditAttributes();

                app(AuditService::class)->logModel($eventType, $model, $oldValues, null);
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                if ($model->shouldAudit('restored')) {
                    $newValues = $model->getAuditAttributes();
                    app(AuditService::class)->logModel('model.restored', $model, null, $newValues);
                }
            });
        }
    }

    /**
     * Determine if an event should be audited.
     */
    public function shouldAudit(string $event): bool
    {
        return property_exists($this, 'enableAudit') ? (bool) $this->enableAudit : true;
    }

    /**
     * Attributes that should not trigger updates or appear in changes.
     */
    public function getAuditIgnoredAttributes(): array
    {
        $default = ['updated_at'];

        if (property_exists($this, 'auditIgnored')) {
            return array_merge($default, (array) $this->auditIgnored);
        }

        return $default;
    }

    /**
     * Get model attributes for audit log snapshot.
     */
    public function getAuditAttributes(): array
    {
        $attributes = $this->attributesToArray();
        $ignored = $this->getAuditIgnoredAttributes();

        foreach ($ignored as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
