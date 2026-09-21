<?php

namespace JobMetric\Rolix\Factories;

use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Models\Membership;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personable_type' => null,
            'personable_id' => null,
            'memberable_type' => null,
            'memberable_id' => null,
            'role_id' => null,
            'collection' => $this->faker->word,
            'is_owner' => false,
            'expired_at' => null,
            'allow' => [],
            'deny' => [],
        ];
    }

    /**
     * set personable
     *
     * @param string $personable_type
     * @param int $personable_id
     *
     * @return static
     */
    public function setPersonable(string $personable_type, int $personable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'personable_type' => $personable_type,
            'personable_id' => $personable_id
        ]);
    }

    /**
     * set memberable
     *
     * @param string $memberable_type
     * @param int $memberable_id
     *
     * @return static
     */
    public function setMemberable(string $memberable_type, int $memberable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'memberable_type' => $memberable_type,
            'memberable_id' => $memberable_id
        ]);
    }

    /**
     * System-wide membership (memberable is null).
     *
     * @return static
     */
    public function system(): static
    {
        return $this->state(fn(array $attributes) => [
            'memberable_type' => null,
            'memberable_id' => null,
        ]);
    }

    /**
     * set role_id
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
     * set collection
     *
     * @param string $collection
     *
     * @return static
     */
    public function setCollection(string $collection): static
    {
        return $this->state(fn(array $attributes) => [
            'collection' => $collection
        ]);
    }

    /**
     * set is_owner
     *
     * @param bool $is_owner
     *
     * @return static
     */
    public function setIsOwner(bool $is_owner): static
    {
        return $this->state(fn(array $attributes) => [
            'is_owner' => $is_owner
        ]);
    }

    /**
     * set expired_at
     *
     * @param DateTime|null $expired_at
     *
     * @return static
     */
    public function setExpiredAt(?DateTime $expired_at): static
    {
        return $this->state(fn(array $attributes) => [
            'expired_at' => $expired_at
        ]);
    }

    /**
     * set allow
     *
     * @param array $allow
     *
     * @return static
     */
    public function setAllow(array $allow): static
    {
        return $this->state(fn(array $attributes) => [
            'allow' => $allow
        ]);
    }

    /**
     * set deny
     *
     * @param array $deny
     *
     * @return static
     */
    public function setDeny(array $deny): static
    {
        return $this->state(fn(array $attributes) => [
            'deny' => $deny
        ]);
    }
}
