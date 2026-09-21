<?php

namespace JobMetric\Rolix\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use JobMetric\Rolix\Facades\Permission;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry;
use JobMetric\Rolix\Models\Role;

/**
 * Validation rules for updating a role.
 */
class UpdateRoleRequest extends FormRequest
{
    /**
     * Optional context (e.g. role id) when used via dto().
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Set dto context.
     *
     * @param array<string, mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * Build validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::rulesFor($this->all(), $this->context);
    }

    /**
     * Rules usable by dto() with optional role id context.
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        return [
            'type'            => 'sometimes|string|max:255',
            'parent_id'       => 'nullable|integer|exists:' . config('rolix.tables.role') . ',id',
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'allow'           => 'sometimes|array',
            'allow.*'         => 'string',
            'deny'            => 'sometimes|array',
            'deny.*'          => 'string',
            'is_default'      => 'sometimes|boolean',
            'is_super'        => 'sometimes|boolean',
            'ordering'        => 'sometimes|integer|min:0',
            'rules'           => 'sometimes|array',
            'rules.*.driver'  => 'required_with:rules|string',
            'rules.*.payload' => 'nullable|array',
        ];
    }

    /**
     * Cross-field validation for type, parent, permissions, and rule drivers.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $v->getData();
            $roleId = $this->context['id'] ?? $this->context['role_id'] ?? null;
            $role = $roleId ? Role::query()->find($roleId) : null;
            $type = $data['type'] ?? $role?->type ?? 'system';

            if (! RoleTypeRegistry::has($type)) {
                $v->errors()->add('type', trans('rolix::base.exceptions.role_type_not_found', ['type' => $type]));

                return;
            }

            if ($role !== null && $role->is_super && array_key_exists('is_super', $data) && ! $data['is_super']) {
                $v->errors()->add('is_super', trans('rolix::base.exceptions.role_is_super_cannot_demote'));
            }

            if (! empty($data['is_super']) && ! ($role?->is_super)) {
                $existsQuery = Role::query()->where('type', $type)->where('is_super', true);

                if ($roleId !== null) {
                    $existsQuery->where('id', '!=', $roleId);
                }

                if ($existsQuery->exists()) {
                    $v->errors()->add('is_super', trans('rolix::base.validation.role.is_super_already_exists'));
                }
            }

            $parentId = $data['parent_id'] ?? null;

            if ($parentId !== null && $roleId !== null && (int) $parentId === (int) $roleId) {
                $v->errors()->add('parent_id', trans('rolix::base.validation.role.parent_self'));
            }

            if (! empty($parentId)) {
                $parent = Role::query()->find($parentId);

                if ($parent !== null && $parent->type !== $type) {
                    $v->errors()->add('parent_id', trans('rolix::base.validation.role.parent_type_mismatch'));
                }

                if ($role !== null && $parent !== null && $this->wouldCreateCycle($role, (int) $parentId)) {
                    $v->errors()->add('parent_id', trans('rolix::base.validation.role.parent_cycle'));
                }
            }

            if (empty($data['is_super']) && ! ($role?->is_super && ! array_key_exists('is_super', $data))) {
                $model = RoleTypeRegistry::getModel($type);
                $known = Permission::getFlatPermissions(null, $model);

                if ($known !== []) {
                    foreach (['allow', 'deny'] as $field) {
                        foreach ($data[$field] ?? [] as $permission) {
                            if (! in_array($permission, $known, true)) {
                                $v->errors()
                                    ->add($field, trans('rolix::base.validation.role.permission_not_registered', [
                                        'permission' => $permission,
                                    ]));
                            }
                        }
                    }
                }
            }

            foreach ($data['rules'] ?? [] as $index => $rule) {
                $driver = $rule['driver'] ?? null;

                if (! is_string($driver) || ! RuleEvaluatorRegistry::has($driver)) {
                    $v->errors()->add("rules.$index.driver", trans('rolix::base.validation.role.rule_driver_invalid'));
                }
            }
        });
    }

    /**
     * Whether assigning parentId would create a hierarchy cycle.
     *
     * @param Role $role
     * @param int $parentId
     *
     * @return bool
     */
    protected function wouldCreateCycle(Role $role, int $parentId): bool
    {
        return Role::query()->where('id', $parentId)->whereHas('paths', function ($query) use ($role) {
            $query->where('path_id', $role->id)->where('level', '>', 0);
        })->exists();
    }

    /**
     * Attributes via language keys.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type'        => trans('rolix::base.fields.type'),
            'parent_id'   => trans('rolix::base.fields.parent_id'),
            'name'        => trans('rolix::base.fields.name'),
            'description' => trans('rolix::base.fields.description'),
            'allow'       => trans('rolix::base.fields.allow'),
            'deny'        => trans('rolix::base.fields.deny'),
            'is_default'  => trans('rolix::base.fields.is_default'),
            'is_super'    => trans('rolix::base.fields.is_super'),
            'ordering'    => trans('rolix::base.fields.ordering'),
            'rules'       => trans('rolix::base.fields.rules'),
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
