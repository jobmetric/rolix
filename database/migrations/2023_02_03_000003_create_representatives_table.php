<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JobMetric\Rolix\Enums\RepresentativeStatusEnum;

return new class extends Migration {
    /**
     * Create the representatives table.
     *
     * This table records delegation of roles from one membership to another entity,
     * supporting delegation contexts, activation workflow, and status tracking.
     */
    public function up(): void
    {
        Schema::create(config('rolix.tables.representative'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_membership_id')
                ->constrained(config('rolix.tables.membership'))
                ->cascadeOnDelete();
            /**
             * The membership that is delegating the role to someone else.
             */

            $table->morphs('to_personable');
            /**
             * The entity (user, bot, etc.) receiving the delegated role.
             */

            $table->foreignId('role_id')
                ->constrained(config('rolix.tables.role'))
                ->cascadeOnDelete();
            /**
             * The specific role being delegated.
             */

            $table->morphs('delegatable');
            /**
             * The context in which the role is being delegated.
             * For example: tenant, team, project, etc.
             */

            $table->text('reason')->nullable();
            /**
             * Optional explanation for why the delegation was made.
             */

            $table->dateTime('started_at')->nullable();
            /**
             * When the delegation starts. If null, activation date may be used instead.
             */

            $table->dateTime('expired_at')->nullable();
            /**
             * When the delegation ends and becomes inactive.
             */

            $table->string('activation_token')->nullable()->unique();
            /**
             * Token for verifying and activating the delegation by the recipient.
             */

            $table->dateTime('activation_expires_at')->nullable();
            /**
             * The expiration time of the activation token.
             */

            $table->dateTime('activated_at')->nullable();
            /**
             * When the recipient accepted and activated the delegation.
             */

            $table->string('status')
                ->default(RepresentativeStatusEnum::PENDING())
                ->index();
            /**
             * The current status of the delegation.
             * Possible values: pending, active, cancel, reject, expire.
             * Uses: RepresentativeStatusEnum
             */

            $table->dateTime('active_at')->nullable();
            /**
             * Timestamp of when the delegation officially became active.
             */

            $table->dateTime('cancel_at')->nullable();
            $table->morphs('cancel_by');
            /**
             * When and by whom the delegation was canceled.
             */

            $table->dateTime('reject_at')->nullable();
            $table->morphs('reject_by');
            /**
             * When and by whom the delegation was rejected.
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
        Schema::dropIfExists(config('rolix.tables.representative'));
    }
};
