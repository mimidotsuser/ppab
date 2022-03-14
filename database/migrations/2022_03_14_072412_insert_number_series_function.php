<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    private $numberSeriesFunctionQuery = <<<EOD
        DELIMITER ;;
            CREATE FUNCTION next_number_series(code_series varchar(100))
                RETURNS varchar(255) NOT DETERMINISTIC MODIFIES SQL DATA
            BEGIN
                SET @last_id = 0;

                UPDATE number_series
                    SET number_series= @last_id := last_id + 1
                    WHERE code = code_series;

                SET @number_series = (
                    SELECT CONCAT(prefix, '-', LPAD(@last_id, 4, '0'))
                    FROM number_series
                    WHERE code = code_series);

                RETURN @number_series;
            END;;
        DELIMITER ;
        EOD;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create number series function
        DB::unprepared($this->numberSeriesFunctionQuery);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION next_number_series');
    }
};
