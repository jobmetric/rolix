<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the role_paths table.
     *
     * This table maintains hierarchical paths between roles,
     * supporting role inheritance and efficient ancestor-descendant queries.
     * Each record represents a direct or indirect path in the role hierarchy.
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.role_path'), function (Blueprint $table) {
            $table->string('type')->index();
            /**
             * The type field distinguishes different role hierarchies.
             * For example, 'tenant' or 'team' to group role paths separately.
             */

            $table->foreignId('role_id')
                ->index()
                ->constrained(config('rolix.tables.role'))
                ->cascadeOnDelete();
            /**
             * The role_id refers to the current role in the hierarchy.
             */

            $table->foreignId('path_id')
                ->index()
                ->constrained(config('rolix.tables.role'))
                ->cascadeOnDelete();
            /**
             * The path_id refers to one of the parent or ancestor roles of the current role.
             */

            $table->unsignedInteger('level')->default(0)->index();
            /**
             * Indicates the distance (depth) between the current role and the ancestor.
             * Root level is 0; immediate parent is 1; grandparent is 2, and so on.
             */

            $table->unique([
                'type',
                'role_id',
                'path_id',
            ], 'ROLE_PATHS_UNIQUE_KEY');
            /**
             * Ensures a role has a unique path entry per type and ancestor.
             */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rolix.tables.role_path'));
    }
};
