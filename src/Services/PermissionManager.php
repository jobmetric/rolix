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
                            'perm' => $permission,
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
     * @param string|null $context
     * @param string $view
     *
     * @return array
     */
    public function getPermissions(string $context = null, string $view = 'assoc'): array
    {
        return match ($view) {
            'flat' => $this->getFlatPermissions($context),
            'lang' => $this->getLangPermissions($context),
            'flat_lang' => $this->getFlatLangPermissions($context),
            default => $this->getAssocPermissions($context),
        };
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
                if ($perm['perm'] === $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all context permission
     *
     * @return array
     */
    public function getContextPermission(): array
    {
        return array_keys($this->permissions);
    }

    /**
     * Get all permissions in associative format
     *
     * @param string|null $context
     *
     * @return array
     */
    public function getFlatPermissions(string $context = null): array
    {
        if ($context) {
            return array_map(function ($perm) {
                return $perm['perm'];
            }, $this->permissions[$context] ?? []);
        } else {
            return array_merge(...array_map(function ($permissions) {
                return array_map(function ($perm) {
                    return $perm['perm'];
                }, $permissions);
            }, $this->permissions));
        }
    }

    /**
     * Get all permissions in language format
     *
     * @param string|null $context
     *
     * @return array
     */
    public function getLangPermissions(string $context = null): array
    {
        if ($context) {
            return array_map(function ($perm) {
                return $perm['lang'];
            }, $this->permissions[$context] ?? []);
        } else {
            return array_merge(...array_map(function ($permissions) {
                return array_map(function ($perm) {
                    return $perm['lang'];
                }, $permissions);
            }, $this->permissions));
        }
    }

    /**
     * Get all permissions in flat language format
     *
     * @param string|null $context
     *
     * @return array
     */
    public function getFlatLangPermissions(string $context = null): array
    {
        if ($context) {
            return $this->permissions[$context] ?? [];
        } else {
            return $this->permissions;
        }
    }

    /**
     * Get all permissions in associative format
     *
     * @param string|null $context
     *
     * @return array
     */
    public function getAssocPermissions(string $context = null): array
    {
        if ($context) {
            return array_reduce($this->permissions[$context] ?? [], function ($carry, $perm) {
                $carry[$perm['perm']] = $perm['lang'];
                return $carry;
            }, []);
        } else {
            $permissions = [];
            foreach ($this->permissions as $index => $perms) {
                $permissions[$index] = $this->getAssocPermissions($index);
            }

            return $permissions;
        }
    }
}
