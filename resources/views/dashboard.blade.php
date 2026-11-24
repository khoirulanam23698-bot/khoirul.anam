<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-white">
            Dashboard
        </h2>
    </x-slot>

    @php
        $user = Auth::user();

        // Ikon garis sederhana, satu warna
        $icons = [
            'slot'    => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M10 16V8h3a2 2 0 010 4h-3"/>',
            'booking' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'money'   => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6.5 12h.01M17.5 12h.01"/>',
            'done'    => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/>',
            'today'   => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>',
        ];

        // Kartu statistik per role (variabel sama seperti sebelumnya)
        if ($user->isAdmin()) {
            $cards = [
                ['Slot Tersedia', $slotTersedia . ' / ' . $totalSlot, 'slot'],
                ['Booking Aktif', $bookingAktif, 'booking'],
                ['Total Pendapatan', 'Rp' . number_format($totalPendapatan, 0, ',', '.'), 'money'],
            ];
        } elseif ($user->isPetugas()) {
            $cards = [
                ['Slot Tersedia', $slotTersedia . ' / ' . $totalSlot, 'slot'],
                ['Booking Aktif', $bookingAktif, 'booking'],
                ['Transaksi Hari Ini', $transaksiHariIni, 'today'],
            ];
        } elseif ($user->isOwner()) {
            $cards = [
                ['Total Pendapatan', 'Rp' . number_format($totalPendapatan, 0, ',', '.'), 'money'],
                ['Booking Selesai', $bookingSelesai, 'done'],
                ['Slot Tersedia', $slotTersedia . ' / ' . $totalSlot, 'slot'],
            ];
        } else {
            $cards = [];
        }
    @endphp

    <div class="py-10 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Banner sambutan -->
            <div class="relative overflow-hidden rounded-2xl border border-[#23252f] bg-[#11131a] p-8 md:p-10">
                <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-sky-400 to-blue-600"></div>
                <div class="absolute -top-24 -right-16 w-72 h-72 rounded-full bg-blue-600/20 blur-3xl"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20">
                            {{ ucfirst($user->role ?? 'User') }}
                        </span>
                        <h3 class="mt-3 text-2xl md:text-3xl font-extrabold text-white">
                            Selamat datang, {{ $user->name }}
                        </h3>
                        <p class="mt-2 text-slate-400">
                            @if($user->isAdmin())
                                Kelola semua booking, slot, dan user dari sini.
                            @elseif($user->isPetugas())
                                Kelola transaksi dan cetak struk parkir dari sini.
                            @elseif($user->isOwner())
                                Pantau rekap transaksi dan pendapatan dari sini.
                            @else
                                Semua booking dan slot parkir bisa kamu kelola dari sini.
                            @endif
                        </p>
                    </div>
                    <div class="hidden md:flex w-16 h-16 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 items-center justify-center text-3xl font-extrabold text-white shadow-lg shadow-sky-500/30">
                        P
                    </div>
                </div>
            </div>

            <!-- Kartu statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @forelse($cards as [$label, $nilai, $ikon])
                    <div class="rounded-2xl bg-[#11131a] border border-[#23252f] p-6 flex items-center gap-4 transition hover:border-sky-500/50">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                {!! $icons[$ikon] !!}
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-400">{{ $label }}</p>
                            <p class="text-3xl font-bold text-white tabular-nums truncate">{{ $nilai }}</p>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-3 rounded-2xl bg-[#11131a] border border-[#23252f] p-6 flex items-center gap-4">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-slate-500/10 border border-slate-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Akun kamu belum memiliki role khusus</p>
                            <p class="text-sm font-semibold text-white">Hubungi admin untuk mengatur akses (Admin/Petugas/Owner).</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Kartu isi -->
            <div class="rounded-2xl bg-[#11131a] border border-[#23252f] p-8">
                @if($user->isOwner())
                    <h3 class="font-bold text-lg text-white mb-2">Rekap Transaksi</h3>
                    <p class="text-sm text-slate-400 mb-5">Lihat rincian pendapatan dan transaksi lengkap.</p>
                    <a href="{{ route('owner.rekap.index') }}"
                       class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-br from-sky-500 to-blue-600 shadow-lg shadow-sky-500/30 hover:-translate-y-0.5 transition">
                        Lihat Rekap Transaksi
                    </a>
                @elseif($user->isPetugas())
                    <h3 class="font-bold text-lg text-white mb-2">Transaksi</h3>
                    <p class="text-sm text-slate-400 mb-5">Kelola booking masuk dan cetak struk parkir.</p>
                    <a href="{{ route('petugas.transaksi.index') }}"
                       class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-br from-sky-500 to-blue-600 shadow-lg shadow-sky-500/30 hover:-translate-y-0.5 transition">
                        Lihat Transaksi
                    </a>
                @else
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        <h3 class="font-bold text-lg text-white">Aktivitas Terbaru</h3>
                    </div>
                    <p class="text-sm text-slate-400">Belum ada aktivitas untuk ditampilkan di sini.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>