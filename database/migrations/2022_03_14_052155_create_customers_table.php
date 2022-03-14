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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->string('name')->fulltext();
            $table->string('branch')->nullable()->fulltext();
            $table->string('region')->nullable()->fulltext();
            $table->string('location')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();;
            $table->foreignId('updated_by_id')->constrained('users')->restrictOnDelete();;
            $table->timestamps();
            $table->unique(['parent_id', 'name', 'branch']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->on('customers')
                ->references('id')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
