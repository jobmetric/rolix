<?php

namespace JobMetric\Rolix\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for creating a membership.
 */
class StoreMembershipRequest extends FormRequest
{
    /**
     * Build validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'personable_type' => 'required|string',
            'personable_id'   => 'required|integer',
            'memberable_type' => 'nullable|string',
            'memberable_id'   => 'nullable|integer',
            'role_id'         => 'nullable|integer|exists:' . config('rolix.tables.role') . ',id',
            'collection'      => 'nullable|string',
            'is_owner'        => 'boolean',
            'expired_at'      => 'nullable|date',
            'allow'           => 'sometimes|array',
            'allow.*'         => 'string',
            'deny'            => 'sometimes|array',
            'deny.*'          => 'string',
        ];
    }

    /**
     * Attributes via language keys.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'personable_type' => trans('rolix::base.fields.personable_type'),
            'personable_id'   => trans('rolix::base.fields.personable_id'),
            'memberable_type' => trans('rolix::base.fields.memberable_type'),
            'memberable_id'   => trans('rolix::base.fields.memberable_id'),
            'role_id'         => trans('rolix::base.fields.role_id'),
            'collection'      => trans('rolix::base.fields.collection'),
            'is_owner'        => trans('rolix::base.fields.is_owner'),
            'expired_at'      => trans('rolix::base.fields.expired_at'),
            'allow'           => trans('rolix::base.fields.allow'),
            'deny'            => trans('rolix::base.fields.deny'),
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
