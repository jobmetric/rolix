<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when a role hierarchy parent reference is invalid.
 */
class RoleHierarchyException extends Exception
{
    /**
     * @param string $action Translation key suffix under exceptions.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $action = 'role_hierarchy_invalid', int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('rolix::base.exceptions.' . $action), $code, $previous);
    }
}
