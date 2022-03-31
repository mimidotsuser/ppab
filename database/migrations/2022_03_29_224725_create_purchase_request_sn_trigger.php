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
            DROP TRIGGER IF EXISTS set_purchase_requests_sn;
            CREATE TRIGGER set_purchase_requests_sn
                BEFORE INSERT
                ON purchase_requests
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('PR_DOC');
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
        DB::unprepared('DROP TRIGGER IF EXISTS set_purchase_requests_sn');
    }
};
