<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            // Owner account that logs into the restaurant dashboard.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('cover_image')->nullable();
            $table->string('logo')->nullable();
            $table->json('cuisines')->nullable();
            $table->json('categories')->nullable(); // array of category slugs
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->decimal('distance_km', 5, 1)->default(0);
            $table->unsignedInteger('eta_minutes')->default(30);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('min_order', 8, 2)->default(0);
            $table->boolean('is_open')->default(true);
            $table->boolean('has_promotion')->default(false);
            $table->string('promotion_text')->nullable();
            $table->unsignedTinyInteger('price_level')->default(2);
            $table->string('address')->nullable();
            $table->json('opening_hours')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
