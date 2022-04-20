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
        Schema::create('stock_balance_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')->cascadeOnDelete();
            $table->foreignId('stock_balance_id')
                ->constrained('stock_balances')->cascadeOnDelete();
            $table->unsignedInteger('qty_in_before')->default(0);
            $table->unsignedInteger('qty_in_after')->default(0);
            $table->unsignedInteger('qty_out_before')->default(0);
            $table->unsignedInteger('qty_out_after')->default(0);
            $table->unsignedInteger('restock_qty_before')->default(0);
            $table->unsignedInteger('restock_qty_after')->default(0);
            $table->unsignedInteger('qty_pending_issue_before')->default(0);
            $table->unsignedInteger('qty_pending_issue_after')->default(0);
            $table->foreignId('event_id');
            $table->string('event_type');
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('stock_balance_activities');
    }
};
