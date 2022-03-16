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
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->string('serial_number')->nullable();
            $table->boolean('out_of_order')->default(false);
            $table->foreignId('purchase_order_id')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();;
            $table->foreignId('updated_by_id')->constrained('users')->restrictOnDelete();;
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
        Schema::dropIfExists('product_items');
    }
};
