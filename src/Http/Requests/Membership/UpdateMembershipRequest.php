<?php

namespace JobMetric\Rolix\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for updating a membership.
 */
class UpdateMembershipRequest extends FormRequest
{
    /**
     * Optional context (e.g. membership id) when used via dto().
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
     * Rules usable by dto() with optional membership id context.
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        return [
            'role_id'    => 'sometimes|nullable|integer|exists:' . config('rolix.tables.role') . ',id',
            'collection' => 'sometimes|nullable|string',
            'is_owner'   => 'sometimes|boolean',
            'expired_at' => 'sometimes|nullable|date',
            'allow'      => 'sometimes|array',
            'allow.*'    => 'string',
            'deny'       => 'sometimes|array',
            'deny.*'     => 'string',
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
            'role_id'    => trans('rolix::base.fields.role_id'),
            'collection' => trans('rolix::base.fields.collection'),
            'is_owner'   => trans('rolix::base.fields.is_owner'),
            'expired_at' => trans('rolix::base.fields.expired_at'),
            'allow'      => trans('rolix::base.fields.allow'),
            'deny'       => trans('rolix::base.fields.deny'),
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
