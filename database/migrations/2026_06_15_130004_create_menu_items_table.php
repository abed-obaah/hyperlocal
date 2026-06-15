<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('image')->nullable();
            $table->string('section')->default('Lunch'); // Breakfast|Lunch|Dinner|Drinks|Desserts
            $table->json('ingredients')->nullable();
            $table->unsignedInteger('prep_time_minutes')->default(15);
            $table->unsignedInteger('calories')->nullable();
            $table->boolean('popular')->default(false);
            $table->boolean('is_available')->default(true);
            $table->json('option_groups')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
