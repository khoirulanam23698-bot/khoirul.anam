<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // Tambahkan 'color' di sini agar diizinkan masuk ke database
    protected $fillable = [
    'user_id',
    'plat_nomor',
    'nama_kendaraan',
    'color',
    'pemilik',        
    'jenis_kendaraan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}