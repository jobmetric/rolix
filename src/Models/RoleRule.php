<?php

namespace JobMetric\Rolix\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * JobMetric\Rolix\Models\RoleRule
 *
 * @property int $id
 * @property int|null $role_id
 * @property string $driver
 * @property array $payload
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role $role
 *
 * @method static find(int $int)
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class RoleRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'driver',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role_id' => 'integer',
        'driver' => 'string',
        'payload' => 'array',
    ];

    public function getTable()
    {
        return config('rolix.tables.role_rule', parent::getTable());
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
}
