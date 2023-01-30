<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
           $table->id();
          //  $table->bigInteger('id',20)->primary();
            $table->string('user_name')->unique();
            $table->string('name');
            $table->string('ar_name');
            $table->integer('level');
            $table->integer('gpa');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('verification_key',6)->nullable();
            $table->string('phone',13)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->string('access_token',64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
