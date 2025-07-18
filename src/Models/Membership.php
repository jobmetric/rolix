<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Rolix\Events\Resources\MemberableResourceEvent;
use JobMetric\Rolix\Events\Resources\PersonableResourceEvent;

/**
 * JobMetric\Rolix\Models\Membership
 *
 * @property int $id
 * @property string $personable_type
 * @property int $personable_id
 * @property string $memberable_type
 * @property int $memberable_id
 * @property int|null $role_id
 * @property string|null $collection
 * @property boolean $is_owner
 * @property Carbon $expired_at
 * @property array $allow
 * @property array $deny
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read mixed $personable
 * @property-read mixed $personable_resource
 * @property-read mixed $memberable
 * @property-read mixed $memberable_resource
 * @property-read Role $role
 *
 * @method static byCollection(string $collection)
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'personable_type',
        'personable_id',
        'memberable_type',
        'memberable_id',
        'role_id',
        'collection',
        'is_owner',
        'expired_at',
        'allow',
        'deny',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personable_type' => 'string',
        'personable_id' => 'integer',
        'memberable_type' => 'string',
        'memberable_id' => 'integer',
        'role_id' => 'integer',
        'collection' => 'string',
        'is_owner' => 'boolean',
        'expired_at' => 'datetime',
        'allow' => 'array',
        'deny' => 'array',
    ];

    public function getTable()
    {
        return config('rolix.tables.membership', parent::getTable());
    }

    /**
     * personable relation.
     *
     * @return MorphTo
     */
    public function personable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * memberable relation.
     *
     * @return MorphTo
     */
    public function memberable(): MorphTo
    {
        return $this->morphTo();
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
     * Scope a query to only include membership of a given type.
     *
     * @param Builder $query
     * @param string $collection
     * @return Builder
     */
    public function scopeByCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Get the personable resource attribute.
     */
    public function getPersonableResourceAttribute()
    {
        $event = new PersonableResourceEvent($this->personable);
        event($event);

        return $event->resource;
    }

    /**
     * Get the memberable resource attribute.
     */
    public function getMemberableResourceAttribute()
    {
        $event = new MemberableResourceEvent($this->memberable);
        event($event);

        return $event->resource;
    }
}
