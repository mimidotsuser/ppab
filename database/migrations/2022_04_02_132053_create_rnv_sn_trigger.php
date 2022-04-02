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
            DROP TRIGGER IF EXISTS set_rnv_sn;
            CREATE TRIGGER set_rnv_sn
                BEFORE INSERT
                ON receipt_note_vouchers
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('RECEIPT_NOTE_VOUCHER');
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
        DB::unprepared('DROP TRIGGER IF EXISTS set_rnv_sn');
    }
};
