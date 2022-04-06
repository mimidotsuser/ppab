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
        Schema::create('repair_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_repair_id')
                ->constrained('product_item_repairs')->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')->restrictOnDelete();//spare id utilized
            $table->unsignedInteger('old_total')->default(0);
            $table->unsignedInteger('new_total')->default(0);
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
        Schema::dropIfExists('repair_items');
    }
};
