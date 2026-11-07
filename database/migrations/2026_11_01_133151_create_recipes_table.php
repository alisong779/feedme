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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
     
            $table->unsignedSmallInteger('prep_time_minutes')
                ->nullable();

            $table->unsignedSmallInteger('cook_time_minutes')
                ->nullable();

            $table->unsignedSmallInteger('servings')
                ->nullable();

            $table->string('difficulty')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
