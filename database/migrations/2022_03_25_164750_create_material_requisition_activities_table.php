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
        Schema::create('material_requisition_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_requisition_id')
                ->constrained('material_requisitions')->cascadeOnDelete();
            $table->string('stage');
            $table->string('outcome');
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
        Schema::dropIfExists('material_requisition_activities');
    }
};
