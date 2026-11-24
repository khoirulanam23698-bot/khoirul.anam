<x-app-layout>
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Transaksi Parkir Masuk</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                {{ session('error') }}
            </div>
        @endif

        @php
            $platList = $vehicles
                ->pluck('plat_nomor')
                ->map(function ($p) {
                    return strtoupper(preg_replace('/\s+/', ' ', trim($p)));
                })
                ->values();
        @endphp

        <style>[x-cloak]{display:none !important}</style>

        <!-- FORM INPUT MASUK -->
        <div class="bg-white rounded-xl shadow p-6 mb-8 border"
             x-data="{
                plat: @js(old('plat_nomor', '')),
                plates: @js($platList),
                get norm() { return this.plat.toUpperCase().replace(/\s+/g, ' ').trim(); },
                get terdaftar() { return this.norm !== '' && this.plates.includes(this.norm); },
                get manual() { return this.norm !== '' && !this.plates.includes(this.norm); }
             }">

            <form action="{{ route('petugas.transaksi.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Plat Nomor (terdaftar / baru)</label>
                    <input type="text" name="plat_nomor" x-model="plat" list="daftar-plat" autocomplete="off" required
                           placeholder="Ketik plat, contoh: N 1234 AB"
                           class="w-full border-gray-300 rounded-lg shadow-sm uppercase">
                    <datalist id="daftar-plat">
                        @foreach($vehicles as $v)
                            <option value="{{ $v->plat_nomor }}">{{ $v->nama_kendaraan }} - {{ $v->jenis_kendaraan }}</option>
                        @endforeach
                    </datalist>
                    <p x-show="terdaftar" x-cloak class="text-sm text-green-600 mt-1">✓ Plat terdaftar, tarif normal.</p>
                    @error('plat_nomor') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Muncul hanya kalau plat belum terdaftar --}}
<div x-show="manual" x-cloak
     class="border border-amber-500/30 bg-amber-500/10 rounded-xl p-4 space-y-3">
    <p class="text-sm font-semibold text-amber-300">
        ⚠ Plat belum terdaftar - input manual (tarif +Rp {{ number_format($biayaManual) }}/jam)
    </p>

    <div>
        <label class="block text-sm font-medium mb-1 text-slate-200">Jenis Kendaraan *</label>
        <select name="jenis_kendaraan" :required="manual" :disabled="!manual"
                class="w-full border-amber-500/30 focus:border-amber-400 focus:ring-amber-400 rounded-lg shadow-sm">
            <option value="">-- Pilih Jenis --</option>
            @foreach($tarifs as $jenis => $harga)
                <option value="{{ $jenis }}" {{ old('jenis_kendaraan') === $jenis ? 'selected' : '' }}>
                    {{ $jenis }} - Rp {{ number_format($harga + $biayaManual) }}/jam (tarif manual)
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-200">Nama Kendaraan <span class="text-slate-400">(opsional)</span></label>
            <input type="text" name="nama_kendaraan" :disabled="!manual" value="{{ old('nama_kendaraan') }}"
                   class="w-full border-amber-500/30 focus:border-amber-400 focus:ring-amber-400 rounded-lg shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-200">Warna <span class="text-slate-400">(opsional)</span></label>
            <input type="text" name="warna" :disabled="!manual" value="{{ old('warna') }}"
                   class="w-full border-amber-500/30 focus:border-amber-400 focus:ring-amber-400 rounded-lg shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-slate-200">Pemilik <span class="text-slate-400">(opsional)</span></label>
            <input type="text" name="pemilik" :disabled="!manual" value="{{ old('pemilik') }}"
                   class="w-full border-amber-500/30 focus:border-amber-400 focus:ring-amber-400 rounded-lg shadow-sm">
        </div>
    </div>
</div>

                <div>
                    <label class="block text-sm font-medium mb-1">Area Parkir</label>
                    <select name="parking_area_id" required class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="">-- Pilih Area --</option>
                        @foreach($areas as $a)
                            @php
                                $kap   = $kapasitas[$a->id] ?? 0;
                                $pakai = $terisi[$a->id] ?? 0;
                                $sisa  = max(0, $kap - $pakai);
                            @endphp
                            <option value="{{ $a->id }}"
                                {{ (string) old('parking_area_id') === (string) $a->id ? 'selected' : '' }}
                                {{ $kap > 0 && $sisa === 0 ? 'disabled' : '' }}>
                                {{ $a->name }} - Terisi: {{ $pakai }}/{{ $kap }} (Sisa: {{ $sisa }} Slot)
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 rounded-lg">
                    + CATAT MASUK
                </button>
            </form>
        </div>

        <!-- TABEL KENDARAAN DI DALAM AREA -->
        <div class="bg-white rounded-xl shadow border overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
              <h2 class="font-semibold text-gray-800">Kendaraan di Dalam Area</h2>
            </div>
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">Plat Nomor</th>
                        <th class="p-3">Waktu Masuk</th>
                        <th class="p-3">Area</th>
                        <th class="p-3">Tarif / Jam</th>
                        <th class="p-3">Petugas</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeBookings as $b)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-semibold">{{ $b->plat_kendaraan }}</td>
                            <td class="p-3 text-sm">{{ \Carbon\Carbon::parse($b->waktu_masuk)->format('d/m/Y H:i') }}</td>
                            <td class="p-3">{{ $b->parking_area->name ?? '-' }}</td>
                            <td class="p-3">Rp {{ number_format($b->tarif_per_jam ?? 0) }}</td>
                            <td class="p-3 text-sm">{{ $b->user->name ?? '-' }}</td>
                            <td class="p-3">
                                <form action="{{ route('petugas.transaksi.checkout', $b->id) }}" method="POST">
                                    @csrf
                                    <button class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-3 rounded">
                                        KELUAR / BAYAR
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">Belum ada kendaraan di dalam area.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TABEL SELESAI HARI INI -->
        <div class="bg-white rounded-xl shadow border overflow-hidden mt-8">
            <div class="p-4 bg-gray-50 border-b">
              <h2 class="font-semibold text-gray-800">Selesai Hari Ini</h2>
            </div>
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">Plat Nomor</th>
                        <th class="p-3">Waktu Keluar</th>
                        <th class="p-3">Area</th>
                        <th class="p-3">Biaya</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedBookings as $c)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-semibold">{{ $c->plat_kendaraan }}</td>
                            <td class="p-3 text-sm">{{ \Carbon\Carbon::parse($c->waktu_keluar)->format('d/m/Y H:i') }}</td>
                            <td class="p-3">{{ $c->parking_area->name ?? '-' }}</td>
                            <td class="p-3">Rp {{ number_format($c->biaya_total) }}</td>
                            <td class="p-3">
                                <a href="{{ url('/petugas/booking/' . $c->id . '/struk') }}" target="_blank"
                                   class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-2 px-3 rounded">
                                    🖨️ STRUK
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">Belum ada kendaraan yang keluar hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>