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
        Schema::create('request_for_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_for_quotation_id')
                ->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('purchase_request_item_id')->nullable()
                ->constrained('purchase_request_items');
            $table->unsignedInteger('qty');
            $table->foreignId('unit_of_measure_id')
                ->constrained('unit_of_measures');
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
        Schema::dropIfExists('request_for_quotation_items');
    }
};
