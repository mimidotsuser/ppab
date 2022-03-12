<?php

use App\Utils\PermissionUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = array_map(function ($group) {
            $group['created_at'] = Date::now();
            $group['updated_at'] = Date::now();
            return $group; //cannot unpack on v8.0
        }, PermissionUtils::Permissions);
        DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionsNames = array_reduce(PermissionUtils::Permissions, function ($acc, $group) {
            array_push($acc, $group['name']);
            return $acc;
        }, []);

        DB::table('permissions')
            ->orWhere('name', '=', $permissionsNames);
    }
};
