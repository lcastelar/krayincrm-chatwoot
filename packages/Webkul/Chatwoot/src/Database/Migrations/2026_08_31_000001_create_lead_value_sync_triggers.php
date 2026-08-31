<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('leads') || ! Schema::hasTable('lead_products')) {
            return;
        }

        $this->dropSynchronizationObjects();

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE chatwoot_recalculate_lead_value(IN target_lead_id INT)
            BEGIN
                UPDATE leads
                SET lead_value = COALESCE(
                    (SELECT SUM(amount) FROM lead_products WHERE lead_id = target_lead_id),
                    0
                )
                WHERE id = target_lead_id;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER chatwoot_lead_products_after_insert
            AFTER INSERT ON lead_products
            FOR EACH ROW
            BEGIN
                CALL chatwoot_recalculate_lead_value(NEW.lead_id);
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER chatwoot_lead_products_after_update
            AFTER UPDATE ON lead_products
            FOR EACH ROW
            BEGIN
                CALL chatwoot_recalculate_lead_value(OLD.lead_id);

                IF NEW.lead_id <> OLD.lead_id THEN
                    CALL chatwoot_recalculate_lead_value(NEW.lead_id);
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER chatwoot_lead_products_after_delete
            AFTER DELETE ON lead_products
            FOR EACH ROW
            BEGIN
                CALL chatwoot_recalculate_lead_value(OLD.lead_id);
            END
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->dropSynchronizationObjects();
    }

    private function dropSynchronizationObjects(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS chatwoot_lead_products_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS chatwoot_lead_products_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS chatwoot_lead_products_after_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS chatwoot_recalculate_lead_value');
    }
};
