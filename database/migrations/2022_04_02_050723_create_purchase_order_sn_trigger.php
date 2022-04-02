<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $trigger = <<<EOL
            DROP TRIGGER IF EXISTS set_purchaser_order_sn;
            CREATE TRIGGER set_purchaser_order_sn
                BEFORE INSERT
                ON purchase_orders
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('PURCHASE_ORDER');
            END;
           EOL;

        DB::unprepared($trigger);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS set_purchaser_order_sn');
    }
};
