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
        Schema::create('product_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->constrained('product_items');

            $table->foreignId('location_id'); //id of customer or warehouse
            $table->string('location_type'); //class name to parent

            $table->foreignId('customer_contract_id')->nullable()
                ->constrained('customer_contracts');

            $table->foreignId('product_warrant_id')->nullable()
                ->constrained('product_warrants');

            $table->foreignId('entry_remark_id')->nullable()
                ->constrained('entry_remarks');

            $table->foreignId('product_repair_id')->nullable()
                ->constrained('product_repairs');

            $table->string('log_category_code');
            $table->string('log_category_title');

            $table->foreignId('eventable_id')->nullable(); //process creating the log
            $table->string('eventable_type')->nullable();

            $table->foreignId('created_by_id')->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('updated_by_id')->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_tracking_logs');
    }
};
