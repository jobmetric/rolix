<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when a super role cannot be deleted or demoted.
 */
class RoleIsSuperException extends Exception
{
    /**
     * @param string $action Translation key suffix under exceptions.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $action = 'role_is_super_protected', int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('rolix::base.exceptions.' . $action), $code, $previous);
    }
}
