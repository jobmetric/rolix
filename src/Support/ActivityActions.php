<?php

namespace JobMetric\Rolix\Support;

use JobMetric\Rolix\Models\Role;

/**
 * Canonical activity log action names.
 */
class ActivityActions
{
    public const CREATE_ROLE = 'create_role';

    public const UPDATE_ROLE = 'update_role';

    public const DELETE_ROLE = 'delete_role';

    public const ASSIGN_ROLE = 'assign_role';

    public const REMOVE_ROLE = 'remove_role';

    public const UPDATE_MEMBERSHIP = 'update_membership';

    public const RESTORE_MEMBERSHIP = 'restore_membership';

    public const FORCE_DELETE_MEMBERSHIP = 'force_delete_membership';

    /**
     * Map CRUD operation + subject class to an action name.
     *
     * @param string $operation
     * @param object $subject
     *
     * @return string
     */
    public static function fromOperation(string $operation, object $subject): string
    {
        $isRole = $subject instanceof Role;

        return match ($operation) {
            'store' => $isRole ? self::CREATE_ROLE : self::ASSIGN_ROLE,
            'update' => $isRole ? self::UPDATE_ROLE : self::UPDATE_MEMBERSHIP,
            'destroy' => $isRole ? self::DELETE_ROLE : self::REMOVE_ROLE,
            'restore' => self::RESTORE_MEMBERSHIP,
            'forceDelete' => self::FORCE_DELETE_MEMBERSHIP,
            default => $operation,
        };
    }
}
