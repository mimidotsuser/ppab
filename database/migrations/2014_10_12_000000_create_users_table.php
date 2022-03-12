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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default(\App\Utils\UserUtils::PendingActivation);
            $table->foreignId('role_id');
            $table->rememberToken();
            $table->foreignId('created_by_id')->nullable();
            $table->foreignId('updated_by_id')->nullable();
            $table->timestamps();

        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('created_by_id')
                ->on('users')
                ->references('id')
                ->restrictOnDelete();

            $table->foreign('updated_by_id')
                ->on('users')
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
        Schema::dropIfExists('users');
    }
};
