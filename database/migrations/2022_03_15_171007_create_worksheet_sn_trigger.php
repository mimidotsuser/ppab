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
            DROP TRIGGER IF EXISTS set_worksheet_sn;
            CREATE TRIGGER set_worksheet_sn
                BEFORE INSERT
                ON worksheets
                FOR EACH ROW
            BEGIN
                SET NEW.sn = next_number_series('WORKSHEET_DOC');
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
        DB::unprepared('DROP TRIGGER IF EXISTS set_worksheet_sn');
    }
};
