<?php

namespace JobMetric\Rolix\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when a role type is not registered in RoleTypeRegistry.
 */
class RoleTypeNotFoundException extends Exception
{
    /**
     * @param string $type Role type name that was not found.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $type, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('rolix::base.exceptions.role_type_not_found', [
            'type' => $type,
        ]), $code, $previous);
    }
}
