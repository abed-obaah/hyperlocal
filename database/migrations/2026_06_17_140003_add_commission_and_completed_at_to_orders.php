<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Platform commission snapshot taken at order creation.
            $table->decimal('commission', 10, 2)->default(0)->after('total');
            // Set when an admin marks a delivered order "complete" (rider paid out).
            $table->timestamp('completed_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['commission', 'completed_at']);
        });
    }
};
