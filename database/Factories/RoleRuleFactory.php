<?php

namespace JobMetric\Rolix\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Models\RoleRule;

/**
 * @extends Factory<RoleRule>
 */
class RoleRuleFactory extends Factory
{
    protected $model = RoleRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $drivers = [
            'TimeEvaluator',
            'UserStatusEvaluator',
            'WeekdayEvaluator',
            'IpRangeEvaluator',
            'LocationEvaluator',
            'CustomExpressionEvaluator',
            'EnvEvaluator',
            'RoleCountEvaluator',
            'QuotaEvaluator',
        ];

        return [
            'role_id' => null,
            'driver' => $this->faker->randomElement($drivers),
            'payload' => [],
        ];
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
     * set driver
     *
     * @param string $driver
     *
     * @return static
     */
    public function setDriver(string $driver): static
    {
        return $this->state(fn(array $attributes) => [
            'driver' => $driver
        ]);
    }

    /**
     * set payload
     *
     * @param array $payload
     *
     * @return static
     */
    public function setPayload(array $payload): static
    {
        return $this->state(fn(array $attributes) => [
            'payload' => $payload
        ]);
    }
}
