<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the memberships table.
     *
     * This table manages membership relations between entities (users, bots, etc.)
     * and memberable entities (tenants, teams, organizations).
     * It assigns roles to members and supports permission overrides,
     * expiration, ownership flags, and soft deletion.
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.membership'), function (Blueprint $table) {
            $table->id();

            $table->morphs('personable');
            /**
             * The entity being assigned a role.
             * For example: User, Bot, SystemAgent, etc.
             */

            $table->morphs('memberable');
            /**
             * The entity to which the person is a member of.
             * For example: Tenant, Team, Organization, Board, etc.
             */

            $table->foreignId('role_id')
                ->nullable()
                ->constrained(config('rolix.tables.role'))
                ->nullOnDelete();
            /**
             * The assigned role ID for this membership.
             * If null, it means the person is a member without a defined role.
             */

            $table->boolean('is_owner')->default(false);
            /**
             * Indicates if the person is the owner of the memberable entity.
             * Used for special privileges or full access.
             */

            $table->timestamp('expired_at')->nullable();
            /**
             * If set, the membership will be considered expired after this timestamp.
             */

            $table->json('allow')->nullable();
            /**
             * A list of explicitly granted permissions for this membership,
             * overriding the default permissions of the role.
             */

            $table->json('deny')->nullable();
            /**
             * A list of explicitly denied permissions for this membership,
             * also overriding the role permissions.
             */

            $table->softDeletes();
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
        Schema::dropIfExists(config('rolix.tables.membership'));
    }
};
