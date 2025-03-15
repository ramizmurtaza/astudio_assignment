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
        Schema::create('attributes', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->string('name')->index(); 
            $table->enum('type', ['text', 'number', 'boolean', 'date', 'select']);
            $table->json('options')->nullable(); 
            $table->timestamps();
        });
        
        Schema::create('job_attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
        
            $table->index(['job_id', 'attribute_id']);
        });
        
    }

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_attribute_values');
        Schema::dropIfExists('attributes');
    }
};
