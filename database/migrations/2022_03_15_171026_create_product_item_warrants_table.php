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
        Schema::create('product_item_warrants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->constrained('product_items')
            ->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')
            ->cascadeOnDelete();
            $table->date('warrant_start')->nullable();
            $table->date('warrant_end')->nullable();
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
        Schema::dropIfExists('product_item_warrants');
    }
};
