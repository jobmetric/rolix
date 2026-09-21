<?php

namespace JobMetric\Rolix\Tests\Unit\Exceptions;

use Exception;
use JobMetric\Rolix\Exceptions\MembershipMemberableMismatchException;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for MembershipMemberableMismatchException.
 */
class MembershipMemberableMismatchExceptionTest extends TestCase
{
    /**
     * Exception extends base Exception.
     */
    public function test_extends_exception(): void
    {
        $e = new MembershipMemberableMismatchException;
        $this->assertInstanceOf(Exception::class, $e);
    }

    /**
     * Default code is 400.
     */
    public function test_default_code_is_400(): void
    {
        $e = new MembershipMemberableMismatchException;
        $this->assertSame(400, $e->getCode());
    }

    /**
     * Message is translated and non-empty.
     */
    public function test_message_is_translated(): void
    {
        $e = new MembershipMemberableMismatchException;
        $this->assertNotEmpty($e->getMessage());
    }
}
