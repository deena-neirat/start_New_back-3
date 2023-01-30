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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();  // رقم الهوية
            $table->string('name');
            $table->string('ar_name');
            $table->date('date_of_birth');
            $table->enum('gender',['male','female']);
            $table->enum('address',['Jenin','Nablus','Ramallah','Jabaa','Khalil','Tubas']);
            $table->string('password');
            $table->string('phone',13);
            $table->string('image')->nullable();
            $table->string('verification_key',6);
            $table->enum('verified',['yes','no']);
            $table->foreignId('initial_id')->nullable()->constrained()->onDelete('set null ')->onUpdate('set null');///مفرد تابع\ للفحص الاولي
            $table->integer('bookings_num')->default(0);
            $table->string('access_token')->nullable();
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
        Schema::dropIfExists('patients');
    }
};
