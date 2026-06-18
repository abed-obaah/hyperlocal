<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rider_status', ['available', 'unavailable', 'busy'])->default('unavailable')->after('is_available');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rider_status');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
