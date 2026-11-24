<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\ParkingSlotController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\PetugasController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $data = [];

    $totalSlot   = \App\Models\ParkingSlot::count();
    $bookingAktif = \App\Models\Booking::where('status', 'aktif')->count();
    $slotTersedia = max(0, $totalSlot - $bookingAktif);

    if ($user->isAdmin()) {
        $data['totalSlot'] = $totalSlot;
        $data['slotTersedia'] = $slotTersedia;
        $data['bookingAktif'] = $bookingAktif;
        $data['totalPendapatan'] = \App\Models\Payment::where('status', 'lunas')->sum('jumlah');
    } elseif ($user->isPetugas()) {
        $data['totalSlot'] = $totalSlot;
        $data['slotTersedia'] = $slotTersedia;
        $data['bookingAktif'] = $bookingAktif;
        $data['transaksiHariIni'] = \App\Models\Booking::whereDate('waktu_masuk', today())->count();
    } elseif ($user->isOwner()) {
        $data['totalPendapatan'] = \App\Models\Payment::where('status', 'lunas')->sum('jumlah');
        $data['bookingSelesai'] = \App\Models\Booking::where('status', 'selesai')->count();
        $data['totalSlot'] = $totalSlot;
        $data['slotTersedia'] = $slotTersedia;
    }

    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
Route::middleware('auth')->group(function () {
     Route::resource('vehicles', VehicleController::class)->except(['show']);
});


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/slots', [ParkingSlotController::class, 'index'])->name('admin.slots.index');
    Route::post('/slots', [ParkingSlotController::class, 'store'])->name('admin.slots.store');
    Route::delete('/slots/{slot}', [ParkingSlotController::class, 'destroy'])->name('admin.slots.destroy');
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity_logs');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/bookings/{booking}/reset-struk', [AdminBookingController::class, 'resetStruk'])->name('admin.bookings.reset-struk');

   
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show'])->names('admin.users');

    Route::resource('areas', \App\Http\Controllers\Admin\ParkingAreaController::class)->except(['show'])->names('admin.areas');
    Route::resource('tariffs', \App\Http\Controllers\Admin\ParkingTariffController::class)->except(['show'])->names('admin.tariffs');


});

Route::middleware(['auth', 'petugas'])->prefix('petugas')->group(function () {
    // Halaman utama transaksi (Form + Tabel)
    Route::get('/transaksi', [PetugasController::class, 'index'])->name('petugas.transaksi.index');
    
    // Proses simpan kendaraan masuk
    Route::post('/transaksi', [PetugasController::class, 'store'])->name('petugas.transaksi.store');
    
    // Proses kendaraan keluar (checkout)
    Route::post('/transaksi/{booking}/checkout', [PetugasController::class, 'checkout'])->name('petugas.transaksi.checkout');
    
    // Cetak struk
    Route::get('/booking/{booking}/struk', [BookingController::class, 'cetakStruk'])->name('petugas.struk.cetak');
});

Route::middleware(['auth', 'owner'])->prefix('owner')->group(function () {
    Route::get('/rekap', [AdminBookingController::class, 'rekap'])->name('owner.rekap.index');
});