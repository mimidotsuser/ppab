<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private $numberSeries = [];

    public function __construct()
    {
        $this->numberSeries = [
            ['code' => 'MATERIAL_REQUEST', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'MRN_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'SIV_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'PR_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'RFQ_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'INSPECTION_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'GRN_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'RGA_DOC', 'prefix' => config('app.number_series_prefix')],
            ['code' => 'WORKSHEET_DOC', 'prefix' => config('app.number_series_prefix')],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('number_series')->insert($this->numberSeries);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('number_series')
            ->whereIn('code', Arr::pluck($this->numberSeries, 'code'))
            ->delete();
    }
};
