<?php

namespace JobMetric\Rolix\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Models\RolePath;

/**
 * @extends Factory<RolePath>
 */
class RolePathFactory extends Factory
{
    protected $model = RolePath::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => null,
            'role_id' => null,
            'path_id' => null,
            'level' => 0,
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
     * set path_id
     *
     * @param int $path_id
     *
     * @return static
     */
    public function setPathId(int $path_id): static
    {
        return $this->state(fn(array $attributes) => [
            'path_id' => $path_id
        ]);
    }

    /**
     * set level
     *
     * @param int $level
     *
     * @return static
     */
    public function setLevel(int $level): static
    {
        return $this->state(fn(array $attributes) => [
            'level' => $level
        ]);
    }
}
