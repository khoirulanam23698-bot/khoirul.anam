<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 
        'vehicle_id', 
        'parking_slot_id', 
        'parking_area_id',
        'plat_kendaraan',
        'nama_kendaraan', 
        'waktu_masuk', 
        'waktu_keluar', 
        'status',
        'biaya_total',
        'tarif_per_jam',
        'struk_dicetak_at',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'struk_dicetak_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Slot (lama)
    public function slot()
    {
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

    // TAMBAHAN: Relasi ke Kendaraan
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    // TAMBAHAN: Relasi ke Area Parkir
    public function parking_area()
    {
        return $this->belongsTo(ParkingArea::class, 'parking_area_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}