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
use JobMetric\Rolix\Exceptions\MembershipMemberableMismatchException;
use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Factories\MembershipFactory;

/**
 * Represents a person's membership in a memberable entity or system-wide.
 *
 * When memberable_type and memberable_id are both null, the membership is
 * system-scoped. Otherwise, it binds the person to a specific memberable.
 *
 * @package JobMetric\Rolix
 *
 * @property int $id
 * @property string $personable_type
 * @property int $personable_id
 * @property string|null $memberable_type
 * @property int|null $memberable_id
 * @property int|null $role_id
 * @property string|null $collection
 * @property bool $is_owner
 * @property Carbon|null $expired_at
 * @property array|null $allow
 * @property array|null $deny
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read mixed $personable
 * @property-read mixed $personable_resource
 * @property-read mixed $memberable
 * @property-read mixed $memberable_resource
 * @property-read Role|null $role
 *
 * @method static Builder|Membership byCollection(string $collection)
 * @method static Builder|Membership system()
 * @method static Builder|Membership forMemberable(Model $memberable)
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Membership extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
        'personable_id'   => 'integer',
        'memberable_type' => 'string',
        'memberable_id'   => 'integer',
        'role_id'         => 'integer',
        'collection'      => 'string',
        'is_owner'        => 'boolean',
        'expired_at'      => 'datetime',
        'allow'           => 'array',
        'deny'            => 'array',
    ];

    /**
     * Bootstrap model events.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(function (Membership $membership): void {
            $membership->assertMemberableMatchesRoleType();
        });
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return MembershipFactory
     */
    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
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
     *
     * @return Builder
     */
    public function scopeByCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Scope to system-wide memberships (memberable is null).
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('memberable_type')->whereNull('memberable_id');
    }

    /**
     * Scope to memberships for a specific memberable entity.
     *
     * @param Builder $query
     * @param Model $memberable
     *
     * @return Builder
     */
    public function scopeForMemberable(Builder $query, Model $memberable): Builder
    {
        return $query->where('memberable_type', $memberable->getMorphClass())
            ->where('memberable_id', $memberable->getKey());
    }

    /**
     * Get the personable resource attribute.
     *
     * @return mixed
     */
    public function getPersonableResourceAttribute(): mixed
    {
        $event = new PersonableResourceEvent($this->personable);
        event($event);

        return $event->resource;
    }

    /**
     * Get the memberable resource attribute.
     *
     * @return mixed
     */
    public function getMemberableResourceAttribute(): mixed
    {
        if ($this->memberable_type === null && $this->memberable_id === null) {
            return null;
        }

        $event = new MemberableResourceEvent($this->memberable);
        event($event);

        return $event->resource;
    }

    /**
     * Ensure memberable fields match the role type's model option.
     *
     * @return void
     * @throws MembershipMemberableMismatchException
     * @throws RoleTypeNotFoundException
     */
    protected function assertMemberableMatchesRoleType(): void
    {
        if ($this->role_id === null) {
            return;
        }

        $role = $this->relationLoaded('role') ? $this->role : Role::query()->find($this->role_id);

        if ($role === null || $role->type === null || $role->type === '') {
            return;
        }

        RoleTypeRegistry::ensure($role->type);

        $expectedModel = RoleTypeRegistry::getModel($role->type);

        $hasMemberable = $this->memberable_type !== null || $this->memberable_id !== null;
        $bothNull = $this->memberable_type === null && $this->memberable_id === null;
        $bothFilled = $this->memberable_type !== null && $this->memberable_id !== null;

        if (! $bothNull && ! $bothFilled) {
            throw new MembershipMemberableMismatchException('membership_memberable_pair_invalid');
        }

        if ($expectedModel === null) {
            if ($hasMemberable) {
                throw new MembershipMemberableMismatchException('membership_system_requires_null_memberable');
            }

            return;
        }

        if (! $bothFilled) {
            throw new MembershipMemberableMismatchException('membership_model_requires_memberable');
        }

        $expectedMorph = (new $expectedModel)->getMorphClass();

        if ($this->memberable_type !== $expectedMorph) {
            throw new MembershipMemberableMismatchException('membership_memberable_type_mismatch');
        }
    }
}
