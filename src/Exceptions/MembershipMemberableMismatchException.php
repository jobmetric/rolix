<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when membership memberable fields do not match the role type rules.
 */
class MembershipMemberableMismatchException extends Exception
{
    /**
     * @param string $message Translation key suffix or resolved message context.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'membership_memberable_mismatch',
        int $code = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct(trans('rolix::base.exceptions.' . $message), $code, $previous);
    }
}
