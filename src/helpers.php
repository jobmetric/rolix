<?php

use Illuminate\Database\Eloquent\Model;

if (! function_exists('rolix_has_permission')) {
    /**
     * Check whether a personable model has a Rolix permission.
     *
     * @param mixed $user
     * @param string $permission
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return bool
     */
    function rolix_has_permission(
        mixed $user,
        string $permission,
        ?Model $context = null,
        ?string $collection = null
    ): bool {
        if ($user === null || ! is_object($user) || ! method_exists($user, 'hasPermission')) {
            return false;
        }

        return (bool) $user->hasPermission($permission, $context, $collection);
    }
}
