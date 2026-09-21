<?php

namespace JobMetric\Rolix\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Rolix\Models\RoleActivityLog;

/**
 * @extends Factory<RoleActivityLog>
 */
class RoleActivityLogFactory extends Factory
{
    protected $model = RoleActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = [
            'assign_role',
            'remove_role',
            'create_role',
            'update_role',
            'delete_role',
            'assign_permission',
            'remove_permission',
            'create_permission',
            'update_permission',
            'delete_permission'
        ];

        return [
            'action' => $this->faker->randomElement($actions),
            'actor_type' => 'system',
            'actor_id' => 0,
            'target_type' => 'system',
            'target_id' => 0,
            'context_type' => null,
            'context_id' => null,
            'subject_type' => 'system',
            'subject_id' => 0,
            'reason' => $this->faker->optional()->sentence(),
            'ip_address' => $this->faker->optional()->ipv4(),
            'user_agent' => $this->faker->optional()->userAgent(),
            'performed_at' => now(),
        ];
    }

    /**
     * set action
     *
     * @param string $action
     *
     * @return static
     */
    public function setAction(string $action): static
    {
        return $this->state(fn(array $attributes) => [
            'action' => $action
        ]);
    }

    /**
     * set actor
     *
     * @param string $type
     * @param int $id
     *
     * @return static
     */
    public function setActor(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'actor_type' => $type,
            'actor_id' => $id
        ]);
    }

    /**
     * set target
     *
     * @param string $type
     * @param int $id
     *
     * @return static
     */
    public function setTarget(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'target_type' => $type,
            'target_id' => $id
        ]);
    }

    /**
     * set context
     *
     * @param string $type
     * @param int $id
     *
     * @return static
     */
    public function setContext(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'context_type' => $type,
            'context_id' => $id
        ]);
    }

    /**
     * set subject
     *
     * @param string $type
     * @param int $id
     *
     * @return static
     */
    public function setSubject(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'subject_type' => $type,
            'subject_id' => $id
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
     * set ip address
     *
     * @param string $ipAddress
     *
     * @return static
     */
    public function setIpAddress(string $ipAddress): static
    {
        return $this->state(fn(array $attributes) => [
            'ip_address' => $ipAddress
        ]);
    }

    /**
     * set user agent
     *
     * @param string $userAgent
     *
     * @return static
     */
    public function setUserAgent(string $userAgent): static
    {
        return $this->state(fn(array $attributes) => [
            'user_agent' => $userAgent
        ]);
    }
}
