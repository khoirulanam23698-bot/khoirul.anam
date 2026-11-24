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
        Schema::table('bookings', function (Blueprint $table) {
            // Ubah kolom parking_slot_id agar boleh kosong (nullable)
            $table->unsignedBigInteger('parking_slot_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kembalikan ke kondisi semula (tidak boleh kosong)
            $table->unsignedBigInteger('parking_slot_id')->nullable(false)->change();
        });
    }
};