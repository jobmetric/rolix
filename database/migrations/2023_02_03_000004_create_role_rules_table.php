<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the role_rules table.
     *
     * This table stores conditional evaluation rules linked to roles,
     * enabling dynamic activation/inactivation of roles based on configurable drivers.
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.role_rule'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained(config('rolix.tables.role'))
                ->cascadeOnDelete();
            /**
             * The role to which this rule applies.
             * If the rule fails, the role is considered conditionally inactive.
             */

            $table->string('driver');
            /**
             * The evaluator (strategy) class name or alias used to evaluate the rule.
             *
             * Example drivers:
             * - TimeEvaluator
             * - UserStatusEvaluator
             * - WeekdayEvaluator
             * - IpRangeEvaluator
             * - LocationEvaluator
             * - CustomExpressionEvaluator
             * - EnvEvaluator
             * - RoleCountEvaluator
             * - QuotaEvaluator
             *
             * Each driver implements a common interface/contract and contains its own logic.
             */

            $table->json('payload')->nullable();
            /**
             * Optional JSON configuration passed to the driver.
             * Each driver uses this payload to customize its evaluation logic.
             *
             * Example for TimeEvaluator:
             * { "from": "08:00", "to": "18:00", "timezone": "UTC" }
             */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rolix.tables.role_rule'));
    }
};
