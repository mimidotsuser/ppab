<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    private $categories = [
        ['name' => 'Machine', 'description' => 'Unit item with a definite function'],
        ['name' => 'Spare', 'description' => 'Replacement parts']
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('product_categories')
            ->insertOrIgnore($this->categories);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('product_categories')
            ->whereIn('name', Arr::pluck($this->categories, 'name'))
            ->delete();
    }
};
