<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * JobMetric\Rolix\Models\Role
 *
 * @property int $id
 * @property string|null $type
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property array|null $allow
 * @property array|null $deny
 * @property boolean $is_default
 * @property int $ordering
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static ofType(string $type)
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        'name',
        'description',
        'allow',
        'deny',
        'is_default',
        'ordering',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'parent_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'allow' => 'array',
        'deny' => 'array',
        'is_default' => 'boolean',
        'ordering' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getTable()
    {
        return config('rolix.tables.role', parent::getTable());
    }

    /**
     * path relation.
     *
     * @return HasMany
     */
    public function paths(): HasMany
    {
        return $this->hasMany(RolePath::class, 'role_id');
    }

    /**
     * children relation.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
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
}
