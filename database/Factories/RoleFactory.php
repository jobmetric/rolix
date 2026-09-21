<?php

namespace JobMetric\Rolix\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Models\Role;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'system',
            'parent_id' => null,
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'allow' => [],
            'deny' => [],
            'is_default' => $this->faker->boolean,
            'ordering' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * set type
     *
     * @param string $type
     *
     * @return static
     */
    public function setType(string $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => $type
        ]);
    }

    /**
     * set parent_id
     *
     * @param int $parent_id
     *
     * @return static
     */
    public function setParent(int $parent_id): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parent_id
        ]);
    }

    /**
     * set name
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name
        ]);
    }

    /**
     * set description
     *
     * @param string $description
     *
     * @return static
     */
    public function setDescription(string $description): static
    {
        return $this->state(fn(array $attributes) => [
            'description' => $description
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

    /**
     * set is_default
     *
     * @param bool $is_default
     *
     * @return static
     */
    public function setIsDefault(bool $is_default): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => $is_default
        ]);
    }

    /**
     * set ordering
     *
     * @param int $ordering
     *
     * @return static
     */
    public function setOrdering(int $ordering): static
    {
        return $this->state(fn(array $attributes) => [
            'ordering' => $ordering
        ]);
    }
}
