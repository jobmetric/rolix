<?php

namespace JobMetric\Rolix\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * JobMetric\Rolix\Models\RolePath
 *
 * @property int $type
 * @property int $role_id
 * @property int $path_id
 * @property int $level
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class RolePath extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'role_id',
        'path_id',
        'level'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'role_id' => 'integer',
        'path_id' => 'integer',
        'level' => 'integer'
    ];

    public function getTable()
    {
        return config('rolix.tables.role_path', parent::getTable());
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
     * path relation.
     *
     * @return BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'path_id');
    }
}
