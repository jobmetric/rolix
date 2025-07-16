<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the roles table.
     *
     * This table stores all defined roles in the system,
     * supporting hierarchy, categorization (multi-tenant, teams),
     * and permission control (allow/deny).
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.role'), function (Blueprint $table) {
            $table->id();

            $table->string('type')->nullable();
            /**
             * The context or scope this role belongs to.
             * Examples: 'administrator', 'tenant.1', 'team.2'
             * Used to group and categorize roles within the system.
             */

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained(config('rolix.tables.role'))
                ->nullOnDelete();
            /**
             * Optional parent role reference to support hierarchical role structures.
             * Enables inheritance or grouping of roles under a parent.
             */

            $table->string('name');
            /**
             * Human-readable name for the role.
             * Examples: 'Admin', 'Manager', 'Content Moderator'
             */

            $table->text('description')->nullable();
            /**
             * Optional description providing details about the role’s purpose or scope.
             */

            $table->json('allow')->nullable();
            /**
             * List of permissions explicitly granted to this role.
             * Example: ["user.create", "product.delete"]
             */

            $table->json('deny')->nullable();
            /**
             * List of permissions explicitly denied to this role.
             * Denials always take precedence over allowances.
             */

            $table->boolean('is_default')->default(false);
            /**
             * Indicates whether this is the default role for its type.
             * Only one default role should exist per type/category.
             */

            $table->unsignedInteger('ordering')->default(0);
            /**
             * Determines the display or evaluation order of roles.
             * Lower numbers appear earlier in listings or evaluations.
             */

            $table->timestamps();
        });
    }

    /**
     * Reverse the roles table migration.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rolix.tables.role'));
    }
};
