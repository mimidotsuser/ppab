<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    function __construct()
    {
        $this->adminRole = [
            'name' => 'Super admin',
            'editable' => false,
            'description' => 'Gives a user access to all system services',
            'updated_at' => Date::now(),
            'created_at' => Date::now()
        ];

    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->insert($this->adminRole);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->where('name', $this->adminRole['name']);
    }
};
