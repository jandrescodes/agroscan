<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();

            $table->string('image_path', 255);
            $table->string('crop', 100);
            $table->string('location', 150)->nullable();

            $table->boolean('has_problem')->default(false);
            $table->string('pest_name', 150)->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();
            $table->text('description')->nullable();
            $table->text('immediate_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();

            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->string('weather_condition', 100)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('risk_level');
            $table->index('crop');
            $table->index('has_problem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
