<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JobMetric\Rolix\Enums\RepresentativeCancelActorEnum;
use JobMetric\Rolix\Enums\RepresentativeCancelReasonEnum;
use JobMetric\Rolix\Enums\RepresentativeRejectReasonEnum;
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
                ->index()
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
                ->index()
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

            $table->text('reason')->nullable()->index();
            /**
             * Optional explanation for why the delegation was made.
             */

            $table->dateTime('started_at')->nullable()->index();
            /**
             * When the delegation starts. If null, activation date may be used instead.
             */

            $table->dateTime('expired_at')->nullable()->index();
            /**
             * When the delegation ends and becomes inactive.
             */

            $table->uuid('activation_token')->nullable()->unique();
            /**
             * Token for verifying and activating the delegation by the recipient.
             */

            $table->dateTime('activation_expires_at')->nullable()->index();
            /**
             * The expiration time of the activation token.
             */

            $table->dateTime('activated_at')->nullable()->index();
            /**
             * When the recipient accepted and activated the delegation.
             */

            $table->string('status')
                ->default(RepresentativeStatusEnum::PENDING())
                ->index();
            /**
             * The current status of the delegation.
             * Possible values:
             *      pending
             *      active
             *      cancel
             *      reject
             *      expire
             *
             * @see RepresentativeStatusEnum
             */

            $table->dateTime('active_at')->nullable()->index();
            /**
             * Timestamp of when the delegation officially became active.
             */

            $table->dateTime('cancel_at')->nullable()->index();
            $table->nullableMorphs('cancel_by');
            /**
             * When and by whom the delegation was canceled.
             */

            $table->string('cancel_actor')->nullable()->index();
            /**
             * The actor who performed the cancellation.
             * Possible values:
             *      user
             *      admin
             *      system
             *      third_party
             *
             * @see RepresentativeCancelActorEnum
             */

            $table->string('cancel_reason')->nullable()->index();
            /**
             * The reason for the cancellation.
             * Possible values:
             *      user_request
             *      payment_failed
             *      quota_exceeded
             *      policy_violation
             *      expired
             *      duplicate
             *      replaced
             *
             * @see RepresentativeCancelReasonEnum
             */

            $table->dateTime('reject_at')->nullable()->index();
            $table->nullableMorphs('reject_by');
            /**
             * When and by whom the delegation was rejected.
             */

            $table->string('reject_reason')->nullable()->index();
            /**
             * The reason for the rejection.
             * Possible values:
             *      invalid_data
             *      insufficient_permissions
             *      role_not_found
             *      already_exists
             *      unauthorized
             *      policy_violation
             *      incomplete_documents
             *      fraud_suspected
             *      manual_review_failed
             *      auto_validation_failed
             *      other
             *
             * @see RepresentativeRejectReasonEnum
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
