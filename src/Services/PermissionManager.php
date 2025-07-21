<?php

namespace JobMetric\Rolix\Services;

use InvalidArgumentException;

class PermissionManager
{
    protected array $permissions = [];

    /**
     * Add a permission file path
     *
     * @param string $context
     * @param string $path
     *
     * @return void
     */
    public function addPermissionFile(string $context, string $path): void
    {
        if (file_exists($path)) {
            $permissions = require $path;

            // check flat array
            if (is_array($permissions)) {
                foreach ($permissions as $permission => $permission_lang) {
                    if (is_string($permission) && is_string($permission_lang)) {
                        $this->permissions[$context][] = [
                            'permission' => $permission,
                            'lang' => $permission_lang
                        ];
                    } else {
                        throw new InvalidArgumentException("Invalid permission format in file: {$path}");
                    }
                }
            } else {
                throw new InvalidArgumentException("Permission file is not an array: {$path}");
            }
        } else {
            throw new InvalidArgumentException("Permission file does not exist: {$path}");
        }
    }

    /**
     * Get all permissions for a specific context
     *
     * @param string $context
     *
     * @return array
     */
    public function getPermissions(string $context): array
    {
        return $this->permissions[$context] ?? [];
    }

    /**
     * Get all permissions across all contexts
     *
     * @return array
     */
    public function getAllPermissions(): array
    {
        $allPermissions = [];
        foreach ($this->permissions as $context => $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[] = [
                    'context' => $context,
                    'permission' => $permission['permission'],
                    'lang' => $permission['lang']
                ];
            }
        }

        return $allPermissions;
    }

    /**
     * Check if a permission exists for a specific context
     *
     * @param string $context
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission(string $context, string $permission): bool
    {
        if (isset($this->permissions[$context])) {
            foreach ($this->permissions[$context] as $perm) {
                if ($perm['permission'] === $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clear all permissions
     *
     * @return void
     */
    public function clearPermissions(): void
    {
        $this->permissions = [];
    }

    /**
     * Get all contexts with their permissions
     *
     * @return array
     */
    public function getContextsWithPermissions(): array
    {
        $contexts = [];
        foreach ($this->permissions as $context => $permissions) {
            $contexts[$context] = array_map(function ($permission) {
                return $permission['permission'];
            }, $permissions);
        }

        return $contexts;
    }
}
