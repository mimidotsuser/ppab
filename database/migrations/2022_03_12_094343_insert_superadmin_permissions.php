<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {


    static function rolePermissions(): \Illuminate\Support\Collection
    {
        $roleId = DB::table('roles')
            ->where('name', 'super admin')
            ->first(['id']);

        $permissionsIds = DB::table('permissions')->get(['id']);

        return $permissionsIds->map(function ($permissionId) use ($roleId) {
            return [
                'permission_id' => $permissionId->id,
                'role_id' => $roleId->id,
                'updated_at' => Date::now(),
                'created_at' => Date::now()
            ];
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('role_permissions')
            ->insertOrIgnore(self::rolePermissions()->toArray());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        $rolePermissions = self::rolePermissions();

        DB::table('role_permissions')
            ->whereIn('permission_id', $rolePermissions->pluck('permission_id')->toArray())
            ->where('role_id', $rolePermissions->first()['role_id'])
            ->delete();
    }
};
