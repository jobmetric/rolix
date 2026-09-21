<?php

namespace JobMetric\Rolix\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

/**
 * Versioned permission cache for personable models.
 *
 * Uses a per-personable version and a global roles version so membership and
 * role mutations invalidate previously remembered permission snapshots.
 */
class PermissionCache
{
    /**
     * Whether shared cache is enabled.
     *
     * @return bool
     */
    public static function enabled(): bool
    {
        return (bool) config('rolix.cache.enabled', true);
    }

    /**
     * Remember a serializable value for a personable scope bucket.
     *
     * @param Model $personable
     * @param string $bucket
     * @param callable(): mixed $callback
     *
     * @return mixed
     */
    public static function remember(Model $personable, string $bucket, callable $callback): mixed
    {
        if (! self::enabled() || ! $personable->exists) {
            return $callback();
        }

        try {
            return self::store()
                ->remember(self::payloadKey($personable, $bucket), (int) config('rolix.cache.ttl', 60), $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    /**
     * Invalidate cached permission data for a personable model.
     *
     * @param Model $personable
     *
     * @return void
     */
    public static function forget(Model $personable): void
    {
        if (method_exists($personable, 'forgetRolixCache')) {
            $personable->forgetRolixCache();
        }

        if (! self::enabled() || ! $personable->exists) {
            return;
        }

        self::bumpVersionKey(self::personableVersionKey($personable->getMorphClass(), $personable->getKey()));
    }

    /**
     * Invalidate cached permission data by morph identity.
     *
     * @param string|null $type
     * @param int|string|null $id
     *
     * @return void
     */
    public static function forgetByMorph(?string $type, int|string|null $id): void
    {
        if ($type === null || $type === '' || $id === null || $id === '') {
            return;
        }

        if (! self::enabled()) {
            return;
        }

        self::bumpVersionKey(self::personableVersionKey($type, $id));
    }

    /**
     * Bump the global roles version after role allow/deny/rule changes.
     *
     * @return void
     */
    public static function bumpRoles(): void
    {
        if (! self::enabled()) {
            return;
        }

        self::bumpVersionKey(self::rolesVersionKey());
    }

    /**
     * Generate a cache key for a personable scope bucket.
     *
     * @param Model $personable
     * @param string $bucket
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected static function payloadKey(Model $personable, string $bucket): string
    {
        return implode(':', [
            self::prefix(),
            'r' . self::rolesVersion(),
            'p' . self::personableVersion($personable->getMorphClass(), $personable->getKey()),
            $personable->getMorphClass(),
            (string) $personable->getKey(),
            $bucket,
        ]);
    }

    /**
     * Get the current personable version for a given morph identity.
     *
     * @param string $type
     * @param int|string $id
     *
     * @return int
     * @throws InvalidArgumentException
     */
    protected static function personableVersion(string $type, int|string $id): int
    {
        return (int) self::store()->get(self::personableVersionKey($type, $id), 1);
    }

    /**
     * Get the current global roles version.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    protected static function rolesVersion(): int
    {
        return (int) self::store()->get(self::rolesVersionKey(), 1);
    }

    /**
     * Generate a cache key for a personable version.
     *
     * @param string $type
     * @param int|string $id
     *
     * @return string
     */
    protected static function personableVersionKey(string $type, int|string $id): string
    {
        return self::prefix() . ':v:person:' . $type . ':' . $id;
    }

    /**
     * Generate a cache key for the global roles version.
     *
     * @return string
     */
    protected static function rolesVersionKey(): string
    {
        return self::prefix() . ':v:roles';
    }

    /**
     * Bump a version key in the cache.
     *
     * @param string $key
     *
     * @return void
     */
    protected static function bumpVersionKey(string $key): void
    {
        try {
            $store = self::store();
            $store->forever($key, ((int) $store->get($key, 1)) + 1);
        } catch (Throwable) {
            // Cache backend failures must not break domain mutations.
        }
    }

    /**
     * Get the cache key prefix from configuration.
     *
     * @return string
     */
    protected static function prefix(): string
    {
        return (string) config('rolix.cache.prefix', 'rolix');
    }

    /**
     * Get the cache store instance from configuration.
     *
     * @return Repository
     */
    protected static function store(): Repository
    {
        $store = config('rolix.cache.store');

        return $store ? Cache::store($store) : Cache::store();
    }
}
