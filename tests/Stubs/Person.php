<?php

namespace JobMetric\Rolix\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JobMetric\Rolix\Traits\HasRole;

/**
 * Stub personable model for Rolix tests.
 *
 * @package JobMetric\Rolix\Tests
 */
class Person extends Authenticatable
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
