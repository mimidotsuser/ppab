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
        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->string('sn');
            $table->string('reference');
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')
                ->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')
                ->restrictOnDelete();
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
        Schema::dropIfExists('goods_receipt_notes');
    }
};
