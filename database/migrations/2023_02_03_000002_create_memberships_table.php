<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the memberships table.
     *
     * This table manages membership relations between entities (users, bots, etc.)
     * and memberable entities (tenants, teams, organizations).
     * When memberable is null, the membership applies system-wide.
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

            $table->nullableMorphs('memberable');
            /**
             * The entity to which the person is a member of.
             * For example: Tenant, Team, Organization, Board, etc.
             * When both type and id are null, the membership is system-wide.
             */

            $table->foreignId('role_id')
                ->nullable()
                ->index()
                ->constrained(config('rolix.tables.role'))
                ->nullOnDelete();
            /**
             * The assigned role ID for this membership.
             * If null, it means the person is a member without a defined role.
             */

            $table->string('collection')->nullable()->index();
            /**
             * for another collection file
             * if null, value for base collection
             */

            $table->boolean('is_owner')->default(false);
            /**
             * Indicates if the person is the owner of the memberable entity.
             * Used for special privileges or full access.
             */

            $table->timestamp('expired_at')->nullable()->index();
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

            $table->unique([
                'personable_type',
                'personable_id',
                'memberable_type',
                'memberable_id',
                'role_id',
                'collection'
            ], 'MEMBERSHIP_UNIQUE');
        });

        $this->addMemberablePairConstraint();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $this->dropMemberablePairConstraint();

        Schema::dropIfExists(config('rolix.tables.membership'));
    }

    /**
     * Enforce memberable_type/id pair integrity across supported database drivers.
     *
     * Both columns must be null (system membership) or both filled.
     * Application-layer validation remains the fallback when the driver cannot enforce this.
     *
     * @return void
     */
    private function addMemberablePairConstraint(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $grammar = $connection->getQueryGrammar();
        $wrappedTable = $grammar->wrapTable(config('rolix.tables.membership'));
        $typeCol = $grammar->wrap('memberable_type');
        $idCol = $grammar->wrap('memberable_id');
        $expression = "(({$typeCol} IS NULL AND {$idCol} IS NULL) OR ({$typeCol} IS NOT NULL AND {$idCol} IS NOT NULL))";

        try {
            match ($driver) {
                'sqlite' => $this->addSqliteMemberablePairTriggers($wrappedTable),
                'sqlsrv' => DB::statement(
                    "ALTER TABLE {$wrappedTable} WITH CHECK ADD CONSTRAINT membership_memberable_pair_chk CHECK {$expression}"
                ),
                'mysql', 'mariadb', 'pgsql' => DB::statement(
                    "ALTER TABLE {$wrappedTable} ADD CONSTRAINT membership_memberable_pair_chk CHECK {$expression}"
                ),
                default => DB::statement(
                    "ALTER TABLE {$wrappedTable} ADD CONSTRAINT membership_memberable_pair_chk CHECK {$expression}"
                ),
            };
        } catch (Throwable $e) {
            // Driver/version without CHECK or trigger support: enforce in application layer.
        }
    }

    /**
     * SQLite cannot ALTER TABLE to add CHECK; use INSERT/UPDATE triggers instead.
     *
     * @param string $wrappedTable
     *
     * @return void
     */
    private function addSqliteMemberablePairTriggers(string $wrappedTable): void
    {
        $pairValid = '((NEW."memberable_type" IS NULL AND NEW."memberable_id" IS NULL) OR (NEW."memberable_type" IS NOT NULL AND NEW."memberable_id" IS NOT NULL))';
        $message = 'membership memberable_type and memberable_id must both be null or both be set';

        DB::statement("
            CREATE TRIGGER membership_memberable_pair_insert_chk
            BEFORE INSERT ON {$wrappedTable}
            FOR EACH ROW
            WHEN NOT {$pairValid}
            BEGIN
                SELECT RAISE(ABORT, '{$message}');
            END
        ");

        DB::statement("
            CREATE TRIGGER membership_memberable_pair_update_chk
            BEFORE UPDATE ON {$wrappedTable}
            FOR EACH ROW
            WHEN NOT {$pairValid}
            BEGIN
                SELECT RAISE(ABORT, '{$message}');
            END
        ");
    }

    /**
     * Drop driver-specific memberable pair constraints/triggers.
     *
     * @return void
     */
    private function dropMemberablePairConstraint(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $grammar = $connection->getQueryGrammar();
        $wrappedTable = $grammar->wrapTable(config('rolix.tables.membership'));

        try {
            match ($driver) {
                'sqlite' => $this->dropSqliteMemberablePairTriggers(),
                'sqlsrv' => DB::statement(
                    "ALTER TABLE {$wrappedTable} DROP CONSTRAINT membership_memberable_pair_chk"
                ),
                'mysql', 'mariadb' => DB::statement(
                    "ALTER TABLE {$wrappedTable} DROP CHECK membership_memberable_pair_chk"
                ),
                'pgsql' => DB::statement(
                    "ALTER TABLE {$wrappedTable} DROP CONSTRAINT IF EXISTS membership_memberable_pair_chk"
                ),
                default => DB::statement(
                    "ALTER TABLE {$wrappedTable} DROP CONSTRAINT membership_memberable_pair_chk"
                ),
            };
        } catch (Throwable $e) {
            // Ignore when constraint/trigger was never created.
        }
    }

    /**
     * Drop SQLite memberable pair triggers.
     *
     * @return void
     */
    private function dropSqliteMemberablePairTriggers(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS membership_memberable_pair_insert_chk');
        DB::statement('DROP TRIGGER IF EXISTS membership_memberable_pair_update_chk');
    }
};
