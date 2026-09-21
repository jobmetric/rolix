<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use JobMetric\Rolix\Factories\RoleFactory;

/**
 * Represents a role with optional hierarchy, permissions, and conditional rules.
 *
 * @package JobMetric\Rolix
 *
 * @property int $id
 * @property string $type
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property array|null $allow
 * @property array|null $deny
 * @property bool $is_default
 * @property bool $is_super
 * @property int $ordering
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role|null $parent
 * @property-read Role[] $children
 * @property-read RolePath[] $paths
 * @property-read RoleRule[] $rules
 *
 * @method static Builder|Role ofType(string $type)
 * @method static Builder|Role inSubtree(int $rootId)
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'parent_id',
        'name',
        'description',
        'allow',
        'deny',
        'is_default',
        'is_super',
        'ordering',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type'        => 'string',
        'parent_id'   => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'allow'       => 'array',
        'deny'        => 'array',
        'is_default'  => 'boolean',
        'is_super'    => 'boolean',
        'ordering'    => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return RoleFactory
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('rolix.tables.role', parent::getTable());
    }

    /**
     * Whether this role is a super role.
     *
     * @return bool
     */
    public function isSuper(): bool
    {
        return (bool) $this->is_super;
    }

    /**
     * Parent role relation.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Path relation (closure-table rows for this role).
     *
     * @return HasMany
     */
    public function paths(): HasMany
    {
        return $this->hasMany(RolePath::class, 'role_id');
    }

    /**
     * Children roles.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Conditional rules attached to this role.
     *
     * @return HasMany
     */
    public function rules(): HasMany
    {
        return $this->hasMany(RoleRule::class, 'role_id');
    }

    /**
     * Ancestor roles ordered by depth (immediate parent first).
     *
     * @return Collection<int, Role>
     */
    public function ancestors(): Collection
    {
        $pathIds = RolePath::query()
            ->where('role_id', $this->id)
            ->where('level', '>', 0)
            ->orderBy('level')
            ->pluck('path_id');

        if ($pathIds->isEmpty()) {
            return collect();
        }

        /** @var EloquentCollection<int, Role> $roles */
        $roles = self::query()->whereIn('id', $pathIds)->get()->keyBy('id');

        return $pathIds->map(fn ($id) => $roles->get($id))->filter()->values();
    }

    /**
     * Descendant roles (roles that have this role as an ancestor).
     *
     * @return Collection<int, Role>
     */
    public function descendants(): Collection
    {
        $roleIds = RolePath::query()
            ->where('path_id', $this->id)
            ->where('level', '>', 0)
            ->orderBy('level')
            ->pluck('role_id');

        if ($roleIds->isEmpty()) {
            return collect();
        }

        /** @var EloquentCollection<int, Role> $roles */
        $roles = self::query()->whereIn('id', $roleIds)->get()->keyBy('id');

        return $roleIds->map(fn ($id) => $roles->get($id))->filter()->values();
    }

    /**
     * Scope a query to only include roles of a given type.
     *
     * @param Builder $query
     * @param string $type
     *
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Roles in the subtree of $rootId (including the root itself).
     *
     * @param Builder $query
     * @param int $rootId
     *
     * @return Builder
     */
    public function scopeInSubtree(Builder $query, int $rootId): Builder
    {
        return $query->whereIn('id', RolePath::query()->where('path_id', $rootId)->select('role_id'));
    }
}
