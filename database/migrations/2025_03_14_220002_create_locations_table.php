<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('city')->index(); 
            $table->string('state');
            $table->string('country');
            $table->timestamps();
        });
        
        Schema::create('job_location', function (Blueprint $table) {

            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
        
            $table->primary(['job_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_location');
        Schema::dropIfExists('locations');
    }
};
