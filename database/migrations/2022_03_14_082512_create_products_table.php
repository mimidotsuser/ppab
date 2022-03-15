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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->foreignId('variant_of')->nullable();
            $table->foreignId('product_category_id')
                ->constrained('product_categories');
            $table->string('internal_code'); // for human dev use
            $table->string('item_code')->nullable()->fulltext();
            $table->string('manufacturer_part_number')->nullable();
            $table->string('description')->fulltext();
            $table->string('local_description')->nullable()->fulltext();
            $table->string('chinese_description')->nullable();
            $table->unsignedInteger('economic_order_qty')->default(1);
            $table->unsignedInteger('min_level')->default(1);
            $table->unsignedInteger('reorder_level')->default(1);
            $table->unsignedInteger('max_level')->default(1);
            $table->foreignId('created_by_id')->constrained('users')
                ->restrictOnDelete();;
            $table->foreignId('updated_by_id')->constrained('users')
                ->restrictOnDelete();;
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {

            $table->foreign('parent_id')
                ->on('products')
                ->references('id');

            $table->foreign('variant_of')
                ->on('products')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
