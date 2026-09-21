<?php

namespace JobMetric\Rolix\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use JobMetric\PackageCore\Output\Response;
use JobMetric\PackageCore\Services\AbstractCrudService;
use JobMetric\Rolix\Events\Membership\MembershipDeleteEvent;
use JobMetric\Rolix\Events\Membership\MembershipForceDeleteEvent;
use JobMetric\Rolix\Events\Membership\MembershipRestoreEvent;
use JobMetric\Rolix\Events\Membership\MembershipStoreEvent;
use JobMetric\Rolix\Events\Membership\MembershipUpdateEvent;
use JobMetric\Rolix\Exceptions\MembershipDefaultRoleMissingException;
use JobMetric\Rolix\Exceptions\MembershipDuplicateException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Http\Requests\Membership\StoreMembershipRequest;
use JobMetric\Rolix\Http\Requests\Membership\UpdateMembershipRequest;
use JobMetric\Rolix\Http\Resources\MembershipResource;
use JobMetric\Rolix\Models\Membership as MembershipModel;
use JobMetric\Rolix\Models\Role as RoleModel;
use JobMetric\Rolix\Support\ActivityLogger;
use JobMetric\Rolix\Support\PermissionCache;
use Throwable;

/**
 * CRUD and management service for Membership entities.
 *
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Resolve default roles when role_id is omitted
 * - Normalize allow/deny permission overrides
 * - Map unique constraint failures to domain exceptions
 *
 * @package JobMetric\Rolix
 */
class Membership extends AbstractCrudService
{
    /**
     * Enable soft-deletes + restore/forceDelete APIs.
     *
     * @var bool
     */
    protected bool $softDelete = true;

    /**
     * Enable restore API.
     *
     * @var bool
     */
    protected bool $hasRestore = true;

    /**
     * Enable forceDelete API.
     *
     * @var bool
     */
    protected bool $hasForceDelete = true;

    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'rolix::base.entity_names.membership';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = MembershipModel::class;

    /**
     * @var class-string
     */
    protected static string $resourceClass = MembershipResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'personable_type',
        'personable_id',
        'memberable_type',
        'memberable_id',
        'role_id',
        'collection',
        'is_owner',
        'expired_at',
        'allow',
        'deny',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = MembershipStoreEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $updateEventClass = MembershipUpdateEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $deleteEventClass = MembershipDeleteEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $restoreEventClass = MembershipRestoreEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $forceDeleteEventClass = MembershipForceDeleteEvent::class;

    /**
     * Create and persist a new record, mapping duplicate key violations.
     *
     * @param array<string, mixed> $data
     * @param array<int, string> $with
     *
     * @return Response
     * @throws Throwable
     */
    public function store(array $data, array $with = []): Response
    {
        try {
            return parent::store($data, $with);
        } catch (QueryException $e) {
            $this->rethrowIfDuplicate($e);
        }
    }

    /**
     * Update a record, mapping duplicate key violations.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @param array<int, string> $with
     *
     * @return Response
     * @throws Throwable
     */
    public function update(int $id, array $data, array $with = []): Response
    {
        try {
            return parent::update($id, $data, $with);
        } catch (QueryException $e) {
            $this->rethrowIfDuplicate($e);
        }
    }

    /**
     * Mutate/validate payload before create.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     * @throws MembershipDefaultRoleMissingException
     */
    protected function changeFieldStore(array &$data): void
    {
        $data = dto($data, StoreMembershipRequest::class);

        $this->normalizePermissions($data);
        $this->resolveRoleId($data);
    }

    /**
     * Mutate/validate payload before update.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var MembershipModel $model */
        $data = dto($data, UpdateMembershipRequest::class, ['id' => $model->id]);

        $this->normalizePermissions($data);
    }

    /**
     * Log membership mutations when ActivityLogger is available.
     *
     * @param string $operation
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function afterCommon(string $operation, Model $model, array $data = []): void
    {
        if (! in_array($operation, ['store', 'update', 'destroy', 'restore', 'forceDelete'], true)) {
            return;
        }

        /** @var MembershipModel $model */
        try {
            $personable = $model->personable;

            if ($personable instanceof Model) {
                PermissionCache::forget($personable);
            }
            else {
                PermissionCache::forgetByMorph($model->personable_type, $model->personable_id);
            }
        } catch (Throwable) {
            PermissionCache::forgetByMorph($model->personable_type, $model->personable_id);
        }

        ActivityLogger::log($operation, $model, $data);
    }

    /**
     * Normalize allow/deny arrays.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function normalizePermissions(array &$data): void
    {
        if (array_key_exists('allow', $data)) {
            $data['allow'] = array_values(array_unique($data['allow'] ?? []));
        }

        if (array_key_exists('deny', $data)) {
            $data['deny'] = array_values(array_unique($data['deny'] ?? []));
        }
    }

    /**
     * Assign default role_id when omitted.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws MembershipDefaultRoleMissingException
     */
    protected function resolveRoleId(array &$data): void
    {
        if (! empty($data['role_id'])) {
            return;
        }

        $type = $this->resolveRoleTypeForMembership($data);

        $role = RoleModel::query()->where('type', $type)->where('is_default', true)->first();

        if ($role === null) {
            throw new MembershipDefaultRoleMissingException();
        }

        $data['role_id'] = $role->id;
    }

    /**
     * Resolve rolix role type from memberable morph or system scope.
     *
     * @param array<string, mixed> $data
     *
     * @return string
     * @throws MembershipDefaultRoleMissingException
     */
    protected function resolveRoleTypeForMembership(array $data): string
    {
        $memberableType = $data['memberable_type'] ?? null;
        $memberableId = $data['memberable_id'] ?? null;

        if ($memberableType === null && $memberableId === null) {
            return 'system';
        }

        foreach (RoleTypeRegistry::all() as $type => $_options) {
            $modelClass = RoleTypeRegistry::getModel($type);

            if ($modelClass === null) {
                continue;
            }

            if ((new $modelClass)->getMorphClass() === $memberableType) {
                return $type;
            }
        }

        throw new MembershipDefaultRoleMissingException();
    }

    /**
     * Re-throw duplicate membership constraint failures as domain exceptions.
     *
     * @param QueryException $e
     *
     * @return never
     * @throws MembershipDuplicateException
     */
    protected function rethrowIfDuplicate(QueryException $e): never
    {
        if ($this->isDuplicateMembership($e)) {
            throw new MembershipDuplicateException('membership_already_exists', 422, $e);
        }

        throw $e;
    }

    /**
     * Detect unique constraint violations for memberships.
     *
     * @param QueryException $e
     *
     * @return bool
     */
    protected function isDuplicateMembership(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = $e->errorInfo[1] ?? null;
        $message = strtolower($e->getMessage());

        return $sqlState === '23000' || $driverCode === 1062 || str_contains($message, 'membership_unique');
    }
}
