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
            DROP TRIGGER IF EXISTS set_rfq_sn;
            CREATE TRIGGER set_rfq_sn
                BEFORE INSERT
                ON request_for_quotations
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('RFQ_DOC');
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
        DB::unprepared('DROP TRIGGER IF EXISTS set_rfq_sn');
    }
};
