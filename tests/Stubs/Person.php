<?php

namespace JobMetric\Rolix\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Rolix\Traits\HasRole;

/**
 * Stub personable model for Rolix tests.
 *
 * @package JobMetric\Rolix\Tests
 */
class Person extends Model
{
    use HasRole;

    /**
     * @var string
     */
    protected $table = 'rolix_test_persons';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'country',
        'city',
    ];
}
