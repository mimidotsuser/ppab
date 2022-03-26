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
        Schema::create('material_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_requisition_id')
                ->constrained('material_requisitions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')
                ->restrictOnDelete();
            $table->foreignId('worksheet_id')->nullable()->constrained('worksheets')
                ->restrictOnDelete();
            $table->string('purpose_code');
            $table->string('purpose_title');
            $table->unsignedInteger('requested_qty');
            $table->unsignedInteger('verified_qty');
            $table->unsignedInteger('approved_qty');
            $table->unsignedInteger('issued_qty');
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
        Schema::dropIfExists('material_requisition_items');
    }
};
