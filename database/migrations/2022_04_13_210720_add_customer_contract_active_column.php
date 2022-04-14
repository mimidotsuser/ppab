<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_contracts', function (Blueprint $table) {
            $table->boolean('active')
                ->after('customer_id')->default(true);
            $table->foreignId('previous_version_id')
                ->after('id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_contracts', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('previous_version_id');
        });

    }
};
