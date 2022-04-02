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
        Schema::create('receipt_note_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_note_voucher_id')
                ->constrained('receipt_note_vouchers')->cascadeOnDelete();
            $table->foreignId('po_item_id')
                ->constrained('purchase_order_items');
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('delivered_qty');
            $table->unsignedInteger('rejected_qty')->nullable();
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
        Schema::dropIfExists('receipt_note_voucher_items');
    }
};
