<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * When a product is created, respective stock balance row is created
     * @var string
     */
    private string $productInsertTrigger = <<< EOD
            DROP TRIGGER IF EXISTS create_stock_balance_row;
            CREATE TRIGGER create_stock_balance_row
                AFTER INSERT
                ON products
                FOR EACH ROW
            BEGIN
                DECLARE main_warehouse INT;
                #it's safe to assume the first warehouse will be the main one
                SET main_warehouse = (SELECT id FROM warehouses LIMIT 1);
                INSERT INTO stock_balances(warehouse_id, product_id, reorder_level, internal_code,
                                           created_by_id, updated_by_id, created_at, updated_at)
                VALUES (main_warehouse, NEW.id, NEW.reorder_level, NEW.internal_code,
                        NEW.created_by_id, NEW.updated_by_id, NEW.created_at, NEW.updated_at);
            END
        EOD;

    /**
     * If product reorder level is updated, update respective stock balances row
     * @var string
     */
    private string $productUpdateTrigger = <<< EOD
        DROP TRIGGER IF EXISTS update_stock_balance_reorder_level;
        CREATE TRIGGER update_stock_balance_reorder_level
            AFTER UPDATE
            ON products
            FOR EACH ROW
        BEGIN
            IF NEW.reorder_level != OLD.reorder_level THEN
                UPDATE stock_balances
                SET reorder_level= NEW.reorder_level,
                    updated_at   = NEW.updated_at,
                    updated_by_id=NEW.updated_by_id
                WHERE product_id = NEW.id;
            END IF;
        END
        EOD;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->productInsertTrigger);
        DB::unprepared($this->productUpdateTrigger);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS create_stock_balance_row');
        DB::unprepared('DROP TRIGGER IF EXISTS update_stock_balance_reorder_level');
    }
};
