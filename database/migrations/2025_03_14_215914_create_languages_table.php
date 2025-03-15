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
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->timestamps();
        });
        
        Schema::create('job_language', function (Blueprint $table) {
            $table->bigInteger('job_id')->unsigned();
            $table->bigInteger('language_id')->unsigned();
        
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        
            $table->primary(['job_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_language');
        Schema::dropIfExists('languages');
    }
};
