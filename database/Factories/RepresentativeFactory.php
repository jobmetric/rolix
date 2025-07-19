<?php

namespace JobMetric\Rolix\Factories;

use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Enums\RepresentativeCancelActorEnum;
use JobMetric\Rolix\Enums\RepresentativeCancelReasonEnum;
use JobMetric\Rolix\Enums\RepresentativeRejectReasonEnum;
use JobMetric\Rolix\Enums\RepresentativeStatusEnum;
use JobMetric\Rolix\Models\Representative;

/**
 * @extends Factory<Representative>
 */
class RepresentativeFactory extends Factory
{
    protected $model = Representative::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $started_at = $this->faker->optional()->dateTimeBetween('now', '+1 month');
        $expired_at = $started_at ? $this->faker->dateTimeBetween('+1 month', '+2 years', $started_at) : null;

        $status = $this->faker->randomElement(RepresentativeStatusEnum::values());

        $activation_token = $this->faker->optional()->uuid;
        $activation_expires_at = $activation_token ? $this->faker->dateTimeBetween('now', '+7 days') : null;
        $activated_at = null;
        $active_at = null;
        $cancel_at = null;
        $reject_at = null;

        switch ($status) {
            case RepresentativeStatusEnum::PENDING():
                break;
            case RepresentativeStatusEnum::ACTIVE():
                $activated_at = $activation_token ? $this->faker->dateTimeBetween('now', '+7 days') : null;
                $active_at = $activation_token ? $activated_at : $this->faker->dateTimeBetween('now', '+7 days');
                break;
            case RepresentativeStatusEnum::CANCEL():
                $cancel_at = $this->faker->dateTimeBetween('-3 months');
                break;
            case RepresentativeStatusEnum::REJECT():
                $reject_at = $this->faker->dateTimeBetween('-3 months');
                break;
            case RepresentativeStatusEnum::EXPIRE():
                $started_at = $this->faker->optional()->dateTimeBetween('-2 months', '-1 month');
                $expired_at = $this->faker->dateTimeBetween('-1 month');
                break;
        }

        return [
            'from_membership_id' => null,
            'to_personable_type' => null,
            'to_personable_id' => null,
            'role_id' => null,
            'delegatable_type' => null,
            'delegatable_id' => null,
            'reason' => $this->faker->sentence,
            'started_at' => $started_at,
            'expired_at' => $expired_at,
            'activation_token' => $activation_token,
            'activation_expires_at' => $activation_expires_at,
            'activated_at' => $activated_at,
            'status' => $this->faker->randomElement(RepresentativeStatusEnum::values()),
            'active_at' => $active_at,
            'cancel_at' => $cancel_at,
            'cancel_by_type' => null,
            'cancel_by_id' => null,
            'cancel_actor' => $this->faker->optional()->randomElement(RepresentativeCancelActorEnum::values()),
            'cancel_reason' => $this->faker->optional()->randomElement(RepresentativeCancelReasonEnum::values()),
            'reject_at' => $reject_at,
            'reject_by_type' => null,
            'reject_by_id' => null,
            'reject_reason' => $this->faker->optional()->randomElement(RepresentativeRejectReasonEnum::values()),
        ];
    }

    /**
     * set from membership id
     *
     * @param int $from_membership_id
     *
     * @return static
     */
    public function setFromMembershipId(int $from_membership_id): static
    {
        return $this->state(fn(array $attributes) => [
            'from_membership_id' => $from_membership_id
        ]);
    }

    /**
     * set to personable
     *
     * @param string $to_personable_type
     * @param int $to_personable_id
     *
     * @return static
     */
    public function setToPersonable(string $to_personable_type, int $to_personable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'to_personable_type' => $to_personable_type,
            'to_personable_id' => $to_personable_id
        ]);
    }

    /**
     * set role id
     *
     * @param int $role_id
     *
     * @return static
     */
    public function setRoleId(int $role_id): static
    {
        return $this->state(fn(array $attributes) => [
            'role_id' => $role_id
        ]);
    }

    /**
     * set delegatable
     *
     * @param string $delegatable_type
     * @param int $delegatable_id
     *
     * @return static
     */
    public function setDelegatable(string $delegatable_type, int $delegatable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'delegatable_type' => $delegatable_type,
            'delegatable_id' => $delegatable_id
        ]);
    }

    /**
     * set reason
     *
     * @param string $reason
     *
     * @return static
     */
    public function setReason(string $reason): static
    {
        return $this->state(fn(array $attributes) => [
            'reason' => $reason
        ]);
    }

    /**
     * set started at and optionally expired at
     *
     * @param DateTime $started_at
     * @param DateTime|null $expired_at
     *
     * @return static
     */
    public function setStartedAt(DateTime $started_at, ?DateTime $expired_at = null): static
    {
        return $this->state(fn(array $attributes) => [
            'started_at' => $started_at,
            'expired_at' => $expired_at ?? $this->faker->dateTimeBetween($started_at, '+2 years')
        ]);
    }

    /**
     * set activation token and activation expires at
     *
     * @param string|null $activation_token
     * @param DateTime|null $activation_expires_at
     *
     * @return static
     */
    public function setActivationToken(?string $activation_token = null, ?DateTime $activation_expires_at = null): static
    {
        return $this->state(fn(array $attributes) => [
            'activation_token' => $activation_token,
            'activation_expires_at' => $activation_expires_at ?? ($activation_token ? $this->faker->dateTimeBetween('now', '+7 days') : null),
        ]);
    }

    /**
     * set activated at
     *
     * @param DateTime|null $activated_at
     *
     * @return static
     */
    public function setActivatedAt(?DateTime $activated_at = null): static
    {
        return $this->state(function(array $attributes) use ($activated_at) {
            $field_activated_at = $activated_at ?? ($attributes['activation_token'] ? $this->faker->dateTimeBetween('now', '+7 days') : null);

            return [
                'activated_at' => $field_activated_at,
                'active_at' => $field_activated_at ?? null,
            ];
        });
    }

    /**
     * set status
     *
     * @param string $status
     *
     * @return static
     */
    public function setStatus(string $status): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => $status,
            'active_at' => $status === RepresentativeStatusEnum::ACTIVE() ? $this->faker->dateTimeBetween('-3 months', '+7 days') : null,
            'cancel_at' => $status === RepresentativeStatusEnum::CANCEL() ? $this->faker->dateTimeBetween('-3 months') : null,
            'reject_at' => $status === RepresentativeStatusEnum::REJECT() ? $this->faker->dateTimeBetween('-3 months') : null,
        ]);
    }

    /**
     * set active at
     *
     * @param DateTime|null $active_at
     *
     * @return static
     */
    public function setActiveAt(?DateTime $active_at = null): static
    {
        return $this->state(fn(array $attributes) => [
            'active_at' => $active_at ?? ($attributes['activated_at'] ?? $this->faker->dateTimeBetween('-3 months', '+7 days')),
        ]);
    }

    /**
     * set cancel at and cancel by
     *
     * @param DateTime|null $cancel_at
     * @param string|null $cancel_by_type
     * @param int|null $cancel_by_id
     * @param string|null $cancel_actor
     * @param string|null $cancel_reason
     *
     * @return static
     */
    public function setCancelAt(?DateTime $cancel_at = null, ?string $cancel_by_type = null, ?int $cancel_by_id = null, ?string $cancel_actor = null, ?string $cancel_reason = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => RepresentativeStatusEnum::CANCEL(),
            'cancel_at' => $cancel_at,
            'cancel_by_type' => $cancel_by_type,
            'cancel_by_id' => $cancel_by_id,
            'cancel_actor' => $cancel_actor ?? $this->faker->optional()->randomElement(RepresentativeCancelActorEnum::values()),
            'cancel_reason' => $cancel_reason ?? $this->faker->optional()->randomElement(RepresentativeCancelReasonEnum::values()),
        ]);
    }

    /**
     * set reject at and reject by
     *
     * @param DateTime|null $reject_at
     * @param string|null $reject_by_type
     * @param int|null $reject_by_id
     * @param string|null $reject_reason
     *
     * @return static
     */
    public function setRejectAt(?DateTime $reject_at = null, ?string $reject_by_type = null, ?int $reject_by_id = null, ?string $reject_reason = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => RepresentativeStatusEnum::REJECT(),
            'reject_at' => $reject_at,
            'reject_by_type' => $reject_by_type,
            'reject_by_id' => $reject_by_id,
            'reject_reason' => $reject_reason ?? $this->faker->optional()->randomElement(RepresentativeRejectReasonEnum::values()),
        ]);
    }
}
