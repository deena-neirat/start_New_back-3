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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registeration_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('disease_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->enum('tooth',['upper left','upper right','lower left','lower right']);
            $table->enum('tooth_id',['1','2','3','4','5','6','7','8']);
            $table->enum('status',['completed','not completed','canceled'])->nullable();
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
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
        Schema::dropIfExists('treatments');
    }
};
