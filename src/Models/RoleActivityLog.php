<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Rolix\Events\Resources\ActorResourceEvent;
use JobMetric\Rolix\Events\Resources\ContextResourceEvent;
use JobMetric\Rolix\Events\Resources\SubjectResourceEvent;
use JobMetric\Rolix\Events\Resources\TargetResourceEvent;

/**
 * JobMetric\Rolix\Models\RoleActivityLog
 *
 * @property int $id
 * @property string $action
 * @property string $actor_type
 * @property int $actor_id
 * @property string $target_type
 * @property int $target_id
 * @property string|null $context_type
 * @property int|null $context_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string|null $reason
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $performed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read mixed $actor
 * @property-read mixed $actor_resource
 * @property-read mixed $target
 * @property-read mixed $target_resource
 * @property-read mixed $context
 * @property-read mixed $context_resource
 * @property-read mixed $subject
 * @property-read mixed $subject_resource
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class RoleActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'actor_type',
        'actor_id',
        'target_type',
        'target_id',
        'context_type',
        'context_id',
        'subject_type',
        'subject_id',
        'reason',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action'       => 'string',
        'actor_type'   => 'string',
        'actor_id'     => 'integer',
        'target_type'  => 'string',
        'target_id'    => 'integer',
        'context_type' => 'string',
        'context_id'   => 'integer',
        'subject_type' => 'string',
        'subject_id'   => 'integer',
        'reason'       => 'string',
        'ip_address'   => 'string',
        'user_agent'   => 'string',
        'performed_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('rolix.tables.role_activity_log', parent::getTable());
    }

    /**
     * actor relation.
     *
     * @return MorphTo
     */
    public function actor(): MorphTo
    {
        return $this->morphTo('actor', 'actor_type', 'actor_id');
    }

    /**
     * target relation.
     *
     * @return MorphTo
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    /**
     * context relation.
     *
     * @return MorphTo
     */
    public function context(): MorphTo
    {
        return $this->morphTo('context', 'context_type', 'context_id');
    }

    /**
     * subject relation.
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * Get the actor resource attribute.
     */
    public function getActorResourceAttribute()
    {
        $event = new ActorResourceEvent($this->actor);
        event($event);

        return $event->resource;
    }

    /**
     * Get the target resource attribute.
     */
    public function getTargetResourceAttribute()
    {
        $event = new TargetResourceEvent($this->target);
        event($event);

        return $event->resource;
    }

    /**
     * Get the context resource attribute.
     */
    public function getContextResourceAttribute()
    {
        $event = new ContextResourceEvent($this->context);
        event($event);

        return $event->resource;
    }

    /**
     * Get the subject resource attribute.
     */
    public function getSubjectResourceAttribute()
    {
        $event = new SubjectResourceEvent($this->subject);
        event($event);

        return $event->resource;
    }
}
