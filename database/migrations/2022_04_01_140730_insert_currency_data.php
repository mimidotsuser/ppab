<?php

use App\Utils\CurrencyUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currencies = array_map(fn($row) => ['code' => $row[0], 'name' => $row[1]],
            CurrencyUtils::currencies());

        DB::table('currencies')
            ->insertOrIgnore($currencies);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('currencies')
            ->whereIn('code', Arr::pluck(CurrencyUtils::currencies(), 0))
            ->delete();
    }
};
