<?php

namespace JobMetric\Rolix\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use JobMetric\Rolix\Contracts\RuleEvaluatorContract;
use JobMetric\Rolix\Facades\Permission;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Models\Role;

/**
 * Validation rules for creating a role.
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * Build validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type'            => 'sometimes|nullable|string|max:255',
            'parent_id'       => 'nullable|integer|exists:' . config('rolix.tables.role') . ',id',
            'name'            => 'required|string|max:255',
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
            $type = $data['type'] ?? 'system';

            if (! RoleTypeRegistry::has($type)) {
                $v->errors()->add('type', trans('rolix::base.exceptions.role_type_not_found', ['type' => $type]));

                return;
            }

            $this->validateParent($v, $data, $type);
            $this->validatePermissions($v, $data, $type);
            $this->validateRuleDrivers($v, $data);
            $this->validateUniqueSuper($v, $data, $type);
        });
    }

    /**
     * Ensure at most one is_super role exists per type.
     *
     * @param Validator $v
     * @param array<string, mixed> $data
     * @param string $type
     *
     * @return void
     */
    protected function validateUniqueSuper(Validator $v, array $data, string $type): void
    {
        if (empty($data['is_super'])) {
            return;
        }

        if (Role::query()->where('type', $type)->where('is_super', true)->exists()) {
            $v->errors()->add('is_super', trans('rolix::base.validation.role.is_super_already_exists'));
        }
    }

    /**
     * Validate parent_id belongs to the same type.
     *
     * @param Validator $v
     * @param array<string, mixed> $data
     * @param string $type
     *
     * @return void
     */
    protected function validateParent(Validator $v, array $data, string $type): void
    {
        if (empty($data['parent_id'])) {
            return;
        }

        $parent = Role::query()->find($data['parent_id']);

        if ($parent === null) {
            return;
        }

        if ($parent->type !== $type) {
            $v->errors()->add('parent_id', trans('rolix::base.validation.role.parent_type_mismatch'));
        }
    }

    /**
     * Validate allow/deny against registered permissions for the role type.
     *
     * @param Validator $v
     * @param array<string, mixed> $data
     * @param string $type
     *
     * @return void
     */
    protected function validatePermissions(Validator $v, array $data, string $type): void
    {
        if (! empty($data['is_super'])) {
            return;
        }

        $model = RoleTypeRegistry::getModel($type);
        $known = Permission::getFlatPermissions(null, $model);

        if ($known === []) {
            return;
        }

        foreach (['allow', 'deny'] as $field) {
            foreach ($data[$field] ?? [] as $permission) {
                if (! in_array($permission, $known, true)) {
                    $v->errors()->add($field, trans('rolix::base.validation.role.permission_not_registered', [
                        'permission' => $permission,
                    ]));
                }
            }
        }
    }

    /**
     * Validate that each rule driver is a RuleEvaluatorContract.
     *
     * @param Validator $v
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function validateRuleDrivers(Validator $v, array $data): void
    {
        foreach ($data['rules'] ?? [] as $index => $rule) {
            $driver = $rule['driver'] ?? null;

            if (! is_string($driver) || ! class_exists($driver)) {
                $v->errors()->add("rules.$index.driver", trans('rolix::base.validation.role.rule_driver_invalid'));

                continue;
            }

            if (! is_subclass_of($driver, RuleEvaluatorContract::class)) {
                $v->errors()->add("rules.$index.driver", trans('rolix::base.validation.role.rule_driver_invalid'));
            }
        }
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
