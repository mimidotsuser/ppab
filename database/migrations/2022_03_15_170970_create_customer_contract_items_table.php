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
        Schema::create('customer_contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->constrained('product_items');
            $table->foreignId('customer_contract_id')
                ->constrained('customer_contracts');
            $table->foreignId('created_by_id')->constrained('users')
                ->restrictOnDelete();;
            $table->foreignId('updated_by_id')->constrained('users')
                ->restrictOnDelete();;
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
        Schema::dropIfExists('customer_contract_items');
    }
};
