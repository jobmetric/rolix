<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when a membership violates the unique membership constraint.
 */
class MembershipDuplicateException extends Exception
{
    /**
     * @param string $action Translation key suffix under exceptions.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $action = 'membership_already_exists',
        int $code = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct(trans('rolix::base.exceptions.' . $action), $code, $previous);
    }
}
