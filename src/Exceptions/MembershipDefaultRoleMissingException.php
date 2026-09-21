<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when no default role exists for the resolved membership role type.
 */
class MembershipDefaultRoleMissingException extends Exception
{
    /**
     * @param string $action Translation key suffix under exceptions.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $action = 'membership_default_role_missing',
        int $code = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct(trans('rolix::base.exceptions.' . $action), $code, $previous);
    }
}
