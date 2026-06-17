<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Set when the admin completes the order and the rider's payout is credited.
            // status also gains a terminal "completed" value (string column, no enum change).
            $table->timestamp('paid_out_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('paid_out_at');
        });
    }
};
