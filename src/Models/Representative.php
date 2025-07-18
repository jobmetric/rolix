<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Rolix\Events\Resources\CancelByResourceEvent;
use JobMetric\Rolix\Events\Resources\DelegatableResourceEvent;
use JobMetric\Rolix\Events\Resources\RejectByResourceEvent;
use JobMetric\Rolix\Events\Resources\ToPersonableResourceEvent;

/**
 * JobMetric\Rolix\Models\Representative
 *
 * @property int $id
 * @property int $from_membership_id
 * @property string $to_personable_type
 * @property int $to_personable_id
 * @property int $role_id
 * @property string $delegatable_type
 * @property int $delegatable_id
 * @property string|null $reason
 * @property Carbon|null $started_at
 * @property Carbon|null $expired_at
 * @property string|null $activation_token
 * @property Carbon|null $activation_expires_at
 * @property Carbon|null $activated_at
 * @property string $status
 * @property Carbon|null $active_at
 * @property Carbon|null $cancel_at
 * @property string|null $cancel_by_type
 * @property int|null $cancel_by_id
 * @property string|null $cancel_actor
 * @property string|null $cancel_reason
 * @property Carbon|null $reject_at
 * @property string|null $reject_by_type
 * @property int|null $reject_by_id
 * @property string|null $reject_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Membership $fromMembership
 * @property-read mixed $toPersonable
 * @property-read mixed $to_personable_resource
 * @property-read Role $role
 * @property-read mixed $delegatable
 * @property-read mixed $delegatable_resource
 * @property-read mixed $cancelBy
 * @property-read mixed $cancel_by_resource
 * @property-read mixed $rejectBy
 * @property-read mixed $reject_by_resource
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Representative extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_membership_id',
        'to_personable_type',
        'to_personable_id',
        'role_id',
        'delegatable_type',
        'delegatable_id',
        'reason',
        'started_at',
        'expired_at',
        'activation_token',
        'activation_expires_at',
        'activated_at',
        'status',
        'active_at',
        'cancel_at',
        'cancel_by_type',
        'cancel_by_id',
        'cancel_actor',
        'cancel_reason',
        'reject_at',
        'reject_by_type',
        'reject_by_id',
        'reject_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'from_membership_id' => 'integer',
        'to_personable_type' => 'string',
        'to_personable_id' => 'integer',
        'role_id' => 'integer',
        'delegatable_type' => 'string',
        'delegatable_id' => 'integer',
        'reason' => 'string',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'activation_token' => 'string',
        'activation_expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'status' => 'string',
        'active_at' => 'datetime',
        'cancel_at' => 'datetime',
        'cancel_by_type' => 'string',
        'cancel_by_id' => 'integer',
        'cancel_actor' => 'string',
        'cancel_reason' => 'string',
        'reject_at' => 'datetime',
        'reject_by_type' => 'string',
        'reject_by_id' => 'integer',
        'reject_reason' => 'string',
    ];

    public function getTable()
    {
        return config('rolix.tables.representative', parent::getTable());
    }

    /**
     * from_membership relation.
     *
     * @return BelongsTo
     */
    public function fromMembership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'from_membership_id');
    }

    /**
     * toPersonable relation.
     *
     * @return MorphTo
     */
    public function toPersonable(): MorphTo
    {
        return $this->morphTo('toPersonable', 'to_personable_type', 'to_personable_id');
    }

    /**
     * role relation.
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * delegatable relation.
     *
     * @return MorphTo
     */
    public function delegatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * cancelBy relation.
     *
     * @return MorphTo
     */
    public function cancelBy(): MorphTo
    {
        return $this->morphTo('cancelBy', 'cancel_by_type', 'cancel_by_id');
    }

    /**
     * rejectBy relation.
     *
     * @return MorphTo
     */
    public function rejectBy(): MorphTo
    {
        return $this->morphTo('rejectBy', 'reject_by_type', 'reject_by_id');
    }

    /**
     * Get the to personable resource attribute.
     */
    public function getToPersonableResourceAttribute()
    {
        $event = new ToPersonableResourceEvent($this->toPersonable);
        event($event);

        return $event->resource;
    }

    /**
     * Get the delegatable resource attribute.
     */
    public function getDelegatableResourceAttribute()
    {
        $event = new DelegatableResourceEvent($this->delegatable);
        event($event);

        return $event->resource;
    }

    /**
     * Get the cancel by resource attribute.
     */
    public function getCancelByResourceAttribute()
    {
        $event = new CancelByResourceEvent($this->cancelBy);
        event($event);

        return $event->resource;
    }

    /**
     * Get the reject by resource attribute.
     */
    public function getRejectByResourceAttribute()
    {
        $event = new RejectByResourceEvent($this->rejectBy);
        event($event);

        return $event->resource;
    }
}
