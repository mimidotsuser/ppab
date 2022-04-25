<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private $unitsOfMeasure = [
        ['code' => 'PCS', 'title' => 'Pieces', 'unit' => 1],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('unit_of_measures')
            ->insertOrIgnore($this->unitsOfMeasure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->unitsOfMeasure as $uom) {
            DB::table('unit_of_measures')
                ->where('code', $uom['code'])
                ->where('unit', $uom['unit'])
                ->delete();
        }
    }
};
