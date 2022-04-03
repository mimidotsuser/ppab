<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $trigger = <<<EOL
            DROP TRIGGER IF EXISTS set_grn_sn;
            CREATE TRIGGER set_grn_sn
                BEFORE INSERT
                ON goods_receipt_notes
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('GOODS_RECEIPT_NOTE');
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
        DB::unprepared('DROP TRIGGER IF EXISTS set_grn_sn');
    }
};
