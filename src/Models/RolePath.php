<?php

namespace JobMetric\Rolix\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JobMetric\Rolix\Factories\RolePathFactory;

/**
 * Closure-table row linking a role to an ancestor (or itself at level 0).
 *
 * @property string $type
 * @property int $role_id
 * @property int $path_id
 * @property int $level
 *
 * @property-read Role $role
 * @property-read Role $path
 */
class RolePath extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'role_id',
        'path_id',
        'level',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type'    => 'string',
        'role_id' => 'integer',
        'path_id' => 'integer',
        'level'   => 'integer',
    ];

    /**
     * @return RolePathFactory
     */
    protected static function newFactory(): RolePathFactory
    {
        return RolePathFactory::new();
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return config('rolix.tables.role_path', parent::getTable());
    }

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * @return BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'path_id');
    }

    /**
     * Paths for a given role id.
     *
     * @param Builder $query
     * @param int $roleId
     *
     * @return Builder
     */
    public function scopeForRole(Builder $query, int $roleId): Builder
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Rows under a path root (subtree including the root at level 0).
     *
     * @param Builder $query
     * @param int $pathId
     *
     * @return Builder
     */
    public function scopeUnderPath(Builder $query, int $pathId): Builder
    {
        return $query->where('path_id', $pathId);
    }
}
