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
        Schema::create('number_series', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('prefix');
            $table->unsignedInteger('last_series')->default(0);
            $table->foreignId('created_by_id')
                ->nullable()->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('updated_by_id')
                ->nullable()->constrained('users')
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
        Schema::dropIfExists('number_series');
    }
};
