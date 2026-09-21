<?php

namespace JobMetric\Rolix\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RoleActivityLog;
use Throwable;

/**
 * Writes role/membership audit rows to role_activity_logs.
 */
class ActivityLogger
{
    /**
     * Persist an activity log entry for a CRUD operation on a model.
     *
     * @param string $operation
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public static function log(string $operation, Model $model, array $data = []): void
    {
        try {
            $action = ActivityActions::fromOperation($operation, $model);
            $actor = Auth::user();

            $payload = [
                'action'       => $action,
                'subject_type' => $model->getMorphClass(),
                'subject_id'   => $model->getKey(),
                'reason'       => $data['reason'] ?? null,
                'ip_address'   => request()?->ip(),
                'user_agent'   => request()?->userAgent(),
                'performed_at' => now(),
            ];

            if ($actor instanceof Model) {
                $payload['actor_type'] = $actor->getMorphClass();
                $payload['actor_id'] = $actor->getKey();
            }
            else {
                $payload['actor_type'] = 'system';
                $payload['actor_id'] = 0;
            }

            if ($model instanceof Membership) {
                $payload['target_type'] = $model->personable_type;
                $payload['target_id'] = $model->personable_id;
                $payload['context_type'] = $model->memberable_type;
                $payload['context_id'] = $model->memberable_id;
            }
            else if ($model instanceof Role) {
                $payload['target_type'] = $model->getMorphClass();
                $payload['target_id'] = $model->getKey();
                $payload['context_type'] = null;
                $payload['context_id'] = null;
            }
            else {
                $payload['target_type'] = $model->getMorphClass();
                $payload['target_id'] = $model->getKey();
            }

            RoleActivityLog::query()->create($payload);
        } catch (Throwable) {
            // Audit logging must not break domain mutations.
        }
    }
}
