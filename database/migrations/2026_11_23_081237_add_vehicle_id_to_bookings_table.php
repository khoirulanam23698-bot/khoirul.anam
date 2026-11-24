<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambahkan kolom vehicle_id jika belum ada
            if (!Schema::hasColumn('bookings', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('user_id');
            }
            // Tambahkan tarif_per_jam dan biaya_total jika belum ada
            if (!Schema::hasColumn('bookings', 'tarif_per_jam')) {
                $table->integer('tarif_per_jam')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'biaya_total')) {
                $table->integer('biaya_total')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['vehicle_id', 'tarif_per_jam', 'biaya_total']);
        });
    }
};