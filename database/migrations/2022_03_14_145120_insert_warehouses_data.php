<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $warehouses = [
        ['name' => 'Mediant Store', 'location' => 'Nairobi']
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('warehouses')
            ->insertOrIgnore($this->warehouses);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('warehouses')
            ->where('name', Arr::pluck($this->warehouses, 'name'))
            ->delete();
    }
};
