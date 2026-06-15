<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete();

            // placed | accepted | rejected | preparing | ready | rider_assigned
            // | picked_up | on_the_way | delivered | cancelled
            $table->string('status')->default('placed');
            $table->string('payment_method')->default('cash'); // cash | card | wallet
            $table->string('payment_status')->default('pending'); // pending | paid | refunded

            $table->json('address'); // snapshot of delivery address
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedInteger('eta_minutes')->default(30);
            $table->string('rejected_reason')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
