<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the role_activity_logs table.
     *
     * This table logs all significant actions related to roles, memberships,
     * and delegations for auditing, troubleshooting, and accountability purposes.
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.role_activity_log'), function (Blueprint $table) {
            $table->id();

            $table->string('action')->index();
            /**
             * The type of action performed.
             * Examples: assign_role, remove_role, delegate_role, revoke_delegate, reject_delegate, etc.
             */

            $table->morphs('actor');
            /**
             * The entity (usually a user or system) who performed the action.
             */

            $table->morphs('target');
            /**
             * The entity that the action was performed on.
             * For example: a user being assigned a role.
             */

            $table->nullableMorphs('context');
            /**
             * The logical or organizational context in which the action occurred.
             * For example: a tenant, a team, or a specific project.
             */

            $table->morphs('subject');
            /**
             * The subject entity representing the affected object.
             * For example: a Role, a Membership, or a Representative.
             */

            $table->text('reason')->nullable()->index();
            /**
             * Optional description or explanation for why the action was taken.
             */

            $table->ipAddress()->nullable()->index();
            /**
             * IP address of the actor at the time of action.
             */

            $table->text('user_agent')->nullable()->index();
            /**
             * User agent string for the actor’s client (browser, app, etc.).
             */

            $table->dateTime('performed_at')->index();
            /**
             * The actual timestamp when the action was performed.
             * Useful for backdated logs or imported audit trails.
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
        Schema::dropIfExists(config('rolix.tables.role_activity_log'));
    }
};
