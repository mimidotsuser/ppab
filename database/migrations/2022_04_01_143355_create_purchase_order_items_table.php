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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->nullable()
                ->constrained('request_for_quotation_items');
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price')->default(0);
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
        Schema::dropIfExists('purchase_order_items');
    }
};
