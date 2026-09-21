<?php

namespace JobMetric\Rolix\Tests\Unit\Exceptions;

use Exception;
use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for RoleTypeNotFoundException.
 */
class RoleTypeNotFoundExceptionTest extends TestCase
{
    /**
     * Exception extends base Exception.
     */
    public function test_extends_exception(): void
    {
        $e = new RoleTypeNotFoundException('unknown');
        $this->assertInstanceOf(Exception::class, $e);
    }

    /**
     * Default code is 400.
     */
    public function test_default_code_is_400(): void
    {
        $e = new RoleTypeNotFoundException('unknown');
        $this->assertSame(400, $e->getCode());
    }

    /**
     * Message is translated and non-empty.
     */
    public function test_message_is_translated(): void
    {
        $e = new RoleTypeNotFoundException('unknown');
        $this->assertNotEmpty($e->getMessage());
        $this->assertStringContainsString('unknown', $e->getMessage());
    }
}
