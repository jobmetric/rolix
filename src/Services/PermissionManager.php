<?php

namespace JobMetric\Rolix\Services;

use InvalidArgumentException;
use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;

/**
 * Loads and serves permission definitions grouped by context and optional model.
 *
 * When model is omitted, permissions are stored under the system sentinel key
 * and apply system-wide.
 *
 * @package JobMetric\Rolix
 */
class PermissionManager
{
    /**
     * Sentinel key used when no model is provided (system-wide permissions).
     */
    public const SYSTEM_MODEL_KEY = '__system__';

    /**
     * Nested permissions: modelKey => context => list of perm/lang pairs.
     *
     * @var array<string, array<string, array<int, array{perm: string, lang: string}>>>
     */
    protected array $permissions = [];

    /**
     * Add a permission file path for a context and optional model.
     *
     * @param string $context
     * @param string $path
     * @param string|null $model Fully-qualified model class, or null for system-wide.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function addPermissionFile(string $context, string $path, ?string $model = null): void
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException("Permission file does not exist: {$path}");
        }

        $permissions = require $path;

        if (! is_array($permissions)) {
            throw new InvalidArgumentException("Permission file is not an array: {$path}");
        }

        $modelKey = $this->resolveModelKey($model);

        foreach ($permissions as $permission => $permission_lang) {
            if (! is_string($permission) || ! is_string($permission_lang)) {
                throw new InvalidArgumentException("Invalid permission format in file: {$path}");
            }

            $this->permissions[$modelKey][$context][] = [
                'perm' => $permission,
                'lang' => $permission_lang,
            ];
        }
    }

    /**
     * Get all permissions for a specific context and optional model.
     *
     * @param string|null $context
     * @param string $view
     * @param string|null $model
     *
     * @return array
     */
    public function getPermissions(string $context = null, string $view = 'assoc', ?string $model = null): array
    {
        return match ($view) {
            'flat' => $this->getFlatPermissions($context, $model),
            'lang' => $this->getLangPermissions($context, $model),
            'flat_lang' => $this->getFlatLangPermissions($context, $model),
            'tree' => $this->getPermissionTree($model),
            default => $this->getAssocPermissions($context, $model),
        };
    }

    /**
     * Check if a permission exists for a specific context and optional model.
     *
     * @param string $context
     * @param string $permission
     * @param string|null $model
     *
     * @return bool
     */
    public function hasPermission(string $context, string $permission, ?string $model = null): bool
    {
        $modelKey = $this->resolveModelKey($model);
        $items = $this->permissions[$modelKey][$context] ?? [];

        foreach ($items as $perm) {
            if ($perm['perm'] === $permission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all context names for an optional model scope.
     *
     * @param string|null $model
     *
     * @return array
     */
    public function getContextPermission(?string $model = null): array
    {
        $modelKey = $this->resolveModelKey($model);

        return array_keys($this->permissions[$modelKey] ?? []);
    }

    /**
     * Get all permissions in flat format.
     *
     * @param string|null $context
     * @param string|null $model
     *
     * @return array
     */
    public function getFlatPermissions(string $context = null, ?string $model = null): array
    {
        $bucket = $this->bucketFor($model);

        if ($context) {
            return array_map(fn ($perm) => $perm['perm'], $bucket[$context] ?? []);
        }

        if ($bucket === []) {
            return [];
        }

        return array_merge(...array_map(function ($permissions) {
            return array_map(fn ($perm) => $perm['perm'], $permissions);
        }, array_values($bucket)));
    }

    /**
     * Get all permissions in language format.
     *
     * @param string|null $context
     * @param string|null $model
     *
     * @return array
     */
    public function getLangPermissions(string $context = null, ?string $model = null): array
    {
        $bucket = $this->bucketFor($model);

        if ($context) {
            return array_map(fn ($perm) => $perm['lang'], $bucket[$context] ?? []);
        }

        if ($bucket === []) {
            return [];
        }

        return array_merge(...array_map(function ($permissions) {
            return array_map(fn ($perm) => $perm['lang'], $permissions);
        }, array_values($bucket)));
    }

    /**
     * Get all permissions in flat language format.
     *
     * @param string|null $context
     * @param string|null $model
     *
     * @return array
     */
    public function getFlatLangPermissions(string $context = null, ?string $model = null): array
    {
        $bucket = $this->bucketFor($model);

        if ($context) {
            return $bucket[$context] ?? [];
        }

        return $bucket;
    }

    /**
     * Get all permissions in associative format.
     *
     * @param string|null $context
     * @param string|null $model
     *
     * @return array
     */
    public function getAssocPermissions(string $context = null, ?string $model = null): array
    {
        $bucket = $this->bucketFor($model);

        if ($context) {
            return array_reduce($bucket[$context] ?? [], function ($carry, $perm) {
                $carry[$perm['perm']] = $perm['lang'];

                return $carry;
            }, []);
        }

        $permissions = [];
        foreach (array_keys($bucket) as $index) {
            $permissions[$index] = $this->getAssocPermissions($index, $model);
        }

        return $permissions;
    }

    /**
     * Build a nested permission tree from dotted keys for a model scope.
     *
     * @param string|null $model
     *
     * @return array<int, array{perm: string, lang: string|null, children: array}>
     */
    public function getPermissionTree(?string $model = null): array
    {
        $flatLang = [];

        foreach ($this->bucketFor($model) as $items) {
            foreach ($items as $item) {
                if (! isset($item['perm'])) {
                    continue;
                }

                $flatLang[$item['perm']] = $item['lang'] ?? null;
            }
        }

        ksort($flatLang);

        $nodes = [];

        foreach ($flatLang as $perm => $lang) {
            $nodes[$perm] = [
                'perm'     => $perm,
                'lang'     => $lang,
                'children' => [],
            ];
        }

        $roots = [];

        foreach (array_keys($nodes) as $perm) {
            $parentPerm = $this->parentPermissionKey($perm);

            if ($parentPerm !== null && isset($nodes[$parentPerm])) {
                $nodes[$parentPerm]['children'][] = &$nodes[$perm];
            }
            else {
                $roots[] = &$nodes[$perm];
            }
        }

        return $this->detachTreeReferences($roots);
    }

    /**
     * Clone tree nodes to break PHP reference links.
     *
     * @param array $nodes
     *
     * @return array
     */
    protected function detachTreeReferences(array $nodes): array
    {
        $result = [];

        foreach ($nodes as $node) {
            $result[] = [
                'perm'     => $node['perm'],
                'lang'     => $node['lang'],
                'children' => $this->detachTreeReferences($node['children'] ?? []),
            ];
        }

        return $result;
    }

    /**
     * Build a permission tree for a registered role type.
     *
     * @param string $type
     *
     * @return array<int, array{perm: string, lang: string|null, children: array}>
     * @throws RoleTypeNotFoundException
     */
    public function getPermissionTreeForType(string $type): array
    {
        RoleTypeRegistry::ensure($type);

        return $this->getPermissionTree(RoleTypeRegistry::getModel($type));
    }

    /**
     * Resolve the parent dotted permission key, if any.
     *
     * @param string $perm
     *
     * @return string|null
     */
    protected function parentPermissionKey(string $perm): ?string
    {
        $pos = strrpos($perm, '.');

        if ($pos === false) {
            return null;
        }

        return substr($perm, 0, $pos);
    }

    /**
     * Resolve storage key for a model scope.
     *
     * @param string|null $model
     *
     * @return string
     */
    protected function resolveModelKey(?string $model): string
    {
        return $model === null || $model === '' ? self::SYSTEM_MODEL_KEY : $model;
    }

    /**
     * Get the permission bucket for a model scope.
     *
     * @param string|null $model
     *
     * @return array<string, array<int, array{perm: string, lang: string}>>
     */
    protected function bucketFor(?string $model): array
    {
        return $this->permissions[$this->resolveModelKey($model)] ?? [];
    }
}
