<?php

namespace JobMetric\Rolix\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;

/**
 * Stub memberable model for Rolix tests.
 *
 * @package JobMetric\Rolix\Tests
 */
class Tenant extends Model
{
    /**
     * @var string
     */
    protected $table = 'rolix_test_tenants';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];
}
