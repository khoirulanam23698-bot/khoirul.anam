<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\ParkingArea;
use App\Models\ParkingSlot;
use App\Models\ParkingTariff;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetugasController extends Controller
{
    // Tambahan tarif per jam untuk kendaraan yang belum terdaftar (input manual)
    private const BIAYA_MANUAL = 1000;

    // Halaman utama Transaksi Petugas (Form + Tabel)
    public function index()
    {
        $activeBookings = Booking::with(['vehicle', 'parking_area', 'user'])
            ->where('status', 'aktif')
            ->latest()
            ->get();

        $completedBookings = Booking::with(['vehicle', 'parking_area'])
            ->where('status', 'selesai')
            ->whereDate('waktu_keluar', today())
            ->latest('waktu_keluar')
            ->get();

        $vehicles = Vehicle::orderBy('plat_nomor')->get();
        $areas = ParkingArea::all();

        // Tarif per jenis kendaraan (untuk dropdown di form manual)
        $tarifs = ParkingTariff::pluck('tarif_per_jam', 'jenis_kendaraan');
        $biayaManual = self::BIAYA_MANUAL;

        // Kapasitas dan keterisian tiap area (untuk label "Terisi x/y")
        $kapasitas = ParkingSlot::selectRaw('parking_area_id, count(*) as total')
            ->groupBy('parking_area_id')->pluck('total', 'parking_area_id');

        $terisi = Booking::where('status', 'aktif')
            ->selectRaw('parking_area_id, count(*) as total')
            ->groupBy('parking_area_id')->pluck('total', 'parking_area_id');

        return view('petugas.transaksi.index', compact(
            'activeBookings', 'completedBookings', 'vehicles', 'areas',
            'tarifs', 'biayaManual', 'kapasitas', 'terisi'
        ));
    }

    // Menyimpan transaksi masuk
    public function store(Request $request)
    {
        $request->validate([
            'plat_nomor'      => 'required|string|max:20',
            'parking_area_id' => 'required|exists:parking_areas,id',
            'jenis_kendaraan' => 'nullable|string|max:50',
            'nama_kendaraan'  => 'nullable|string|max:100',
            'warna'           => 'nullable|string|max:50',
            'pemilik'         => 'nullable|string|max:100',
        ]);

        // Tolak kalau area sudah penuh
        $total   = ParkingSlot::where('parking_area_id', $request->parking_area_id)->count();
        $dipakai = Booking::where('status', 'aktif')->where('parking_area_id', $request->parking_area_id)->count();

        if ($total > 0 && $dipakai >= $total) {
            return redirect()->back()->withInput()->with('error', 'Area parkir ini sudah penuh.');
        }

        // Rapikan plat: huruf besar, spasi ganda jadi satu
        $plat = strtoupper(trim(preg_replace('/\s+/', ' ', $request->plat_nomor)));

        $vehicle = Vehicle::where('plat_nomor', $plat)->first();
        $manual  = false;

        if (!$vehicle) {
            // Plat belum terdaftar: wajib pilih jenis kendaraan, lalu buat data kendaraannya
            if (!$request->filled('jenis_kendaraan')) {
                return redirect()->back()->withInput()
                    ->with('error', 'Plat belum terdaftar. Pilih jenis kendaraan dulu.');
            }

            $vehicle = Vehicle::create([
                'user_id'         => Auth::id(),
                'plat_nomor'      => $plat,
                'nama_kendaraan'  => $request->nama_kendaraan ?: '-',
                'color'           => $request->warna,
                'pemilik'         => $request->pemilik,
                'jenis_kendaraan' => $request->jenis_kendaraan,
            ]);

            $manual = true;
        }

        $tarif_dasar = $this->ambilTarif($vehicle->jenis_kendaraan);

        if ($tarif_dasar === null) {
            return redirect()->back()->withInput()
                ->with('error', "Tarif untuk {$vehicle->jenis_kendaraan} belum dibuat di menu admin.");
        }

        $tarif_per_jam = $tarif_dasar + ($manual ? self::BIAYA_MANUAL : 0);

        Booking::create([
            'user_id'         => Auth::id(),
            'vehicle_id'      => $vehicle->id,
            'parking_area_id' => $request->parking_area_id,
            'parking_slot_id' => null,
            'plat_kendaraan'  => $vehicle->plat_nomor,
            'nama_kendaraan'  => $vehicle->nama_kendaraan,
            'tarif_per_jam'   => $tarif_per_jam,
            'waktu_masuk'     => now(),
            'status'          => 'aktif',
        ]);

        $pesan = 'Kendaraan berhasil masuk area parkir!';
        if ($manual) {
            $pesan .= ' (Plat baru, tarif manual +Rp ' . number_format(self::BIAYA_MANUAL) . '/jam)';
        }

        return redirect()->route('petugas.transaksi.index')->with('success', $pesan);
    }

    // Kendaraan keluar & hitung biaya
    public function checkout(Request $request, Booking $booking)
    {
        $waktu_masuk  = Carbon::parse($booking->waktu_masuk);
        $waktu_keluar = Carbon::now();
        $durasi_jam   = max(1, (int) ceil($waktu_masuk->floatDiffInHours($waktu_keluar)));

        // Tarif sudah tersimpan saat masuk (termasuk tambahan manual, kalau ada)
        $tarif_per_jam = $booking->tarif_per_jam;

        if (!$tarif_per_jam) {
            $jenis = $booking->vehicle->jenis_kendaraan ?? 'Motor';
            $tarif_per_jam = $this->ambilTarif($jenis);

            if ($tarif_per_jam === null) {
                return redirect()->back()->with('error', "Tarif untuk {$jenis} belum dibuat di menu admin.");
            }
        }

        $biaya_total = $durasi_jam * $tarif_per_jam;

        $booking->update([
            'waktu_keluar'  => $waktu_keluar,
            'status'        => 'selesai',
            'tarif_per_jam' => $tarif_per_jam,
            'biaya_total'   => $biaya_total,
        ]);

        Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'jumlah' => $biaya_total,
                'status' => 'lunas',
                'metode' => 'tunai',
            ]
        );

        ActivityLog::create([
            'user'     => Auth::user()->name,
            'activity' => 'Checkout Kendaraan',
            'details'  => "Plat {$booking->plat_kendaraan} keluar. Biaya: Rp " . number_format($biaya_total) . " ({$durasi_jam} jam)",
        ]);

        return redirect()->route('petugas.transaksi.index')
            ->with('success', "Kendaraan keluar. Biaya: Rp " . number_format($biaya_total) . ". Klik tombol Struk untuk mencetak.");
    }

    /**
     * Ambil tarif per jam dari tabel tarif buatan admin.
     * SESUAIKAN nama model/kolom di sini kalau berbeda.
     */
    private function ambilTarif(?string $jenis_kendaraan): ?int
    {
        $tariff = ParkingTariff::where('jenis_kendaraan', $jenis_kendaraan)->first();

        return $tariff ? (int) $tariff->tarif_per_jam : null;
    }
}