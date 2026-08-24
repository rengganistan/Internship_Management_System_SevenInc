@extends('layouts.dashboard')

@section('content')

{{-- Modal konfirmasi hapus --}}
<div id="deleteModal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-[#D32F2F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus member card?</h3>
                <p class="text-[12.5px] text-[#4B5F5A] leading-relaxed">
                    Data <strong id="deleteCardName"></strong> akan dihapus permanen.
                </p>
            </div>
            <div class="flex justify-center gap-3 border-t border-[#DCE7E1] px-5 py-4">
                <button type="button" onclick="closeDeleteModal()"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]">
                    Batal
                </button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="rounded-[9px] bg-[#D32F2F] px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Member Card</h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">Kelola data member card pemagang aktif dan selesai.</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{!! session('success') !!}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 flex items-start gap-3 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
        <svg class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{!! session('error') !!}</span>
    </div>
    @endif

    {{-- ===== PANEL BULK GENERATE ===== --}}
    <div class="mb-5 rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
        <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Generate Membercard</p>
        <p class="mb-4 text-[13px] text-[#4B5F5A]">
            Generate membercard untuk pemagang yang sudah <strong>Selesai</strong> magang. Membercard yang sudah di-generate akan otomatis tersedia di halaman pemagang.
        </p>
        <form action="{{ route('admin.membercards.generate.bulk') }}" method="POST"
              class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[160px] max-w-xs">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A] mb-1">
                    Filter Brand (opsional)
                </label>
                <select name="brand"
                    class="w-full rounded-[9px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-sm text-[#1B3A34] focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
                    <option value="">— Semua Brand —</option>
                    @php
                        $allBrands = [
                            'magangjogja.com'  => 'Magangjogja.com',
                            'areakerja.com'    => 'Areakerja.com',
                            'republikweb.net'  => 'Republikweb.net',
                            'titipsini.com'    => 'Titipsini.com',
                            'ambilpaket.com'   => 'Ambilpaket.com',
                            'bikinkepo.com'    => 'Bikinkepo.com',
                            'bimbelcerdas.com' => 'Bimbelcerdas.com',
                            'latihankerja.com' => 'Latihankerja.com',
                            'lowkerjateng.com' => 'Lowkerjateng.com',
                            'lowkerjogja.com'  => 'Lowkerjogja.com',
                            'pijatjogja.com'   => 'Pijatjogja.com',
                            'sayabantu.com'    => 'Sayabantu.com',
                            'titikvisual.com'  => 'Titikvisual.com',
                            'tuantanah.com'    => 'Tuantanah.com',
                            'tukanglas.org'    => 'Tukanglas.org',
                            'adakamarid'       => 'Adakamar.id',
                            'seven inc'        => 'Seven Inc',
                            'Ambilpaket'       => 'Ambilpaket',
                            'Seven Inc'        => 'Seven Inc (kapital)',
                        ];
                    @endphp
                    @foreach($allBrands as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
                Generate Sekarang
            </button>
            <a href="{{ route('admin.membercards.generate.bulk') }}"
               onclick="this.closest('form').submit(); return false;"
               class="hidden"></a>
        </form>
    </div>

    {{-- ===== FILTER + TABEL ===== --}}
    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">

        {{-- Filter bar --}}
        <div class="flex flex-wrap items-center gap-3 border-b border-[#DCE7E1] px-5 py-3 bg-[#F4F8F6]">
            <form method="GET" action="{{ route('admin.membercards.index') }}"
                  class="flex flex-wrap items-center gap-2 flex-1">
                <select name="brand" onchange="this.form.submit()"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-sm text-[#1B3A34] focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
                    <option value="">Semua Brand</option>
                    @foreach($availableBrands as $b)
                        <option value="{{ $b }}" @selected($brandFilter === $b)>{{ $b }}</option>
                    @endforeach
                </select>
                @if($brandFilter)
                    <a href="{{ route('admin.membercards.index') }}"
                       class="text-xs text-[#4B5F5A] hover:text-red-500 underline">Reset filter</a>
                @endif
            </form>
            <span class="text-xs text-[#4B5F5A]">{{ $downloads->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">No.</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Kode</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Angkatan</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Instansi</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Brand</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Status Unduh</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($downloads as $dl)
                    <tr class="transition hover:bg-[#F4F8F6]">
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $loop->iteration }}</td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#1B3A34]">{{ $dl->name }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <code class="rounded-[6px] bg-[#F4F8F6] px-2 py-1 text-[11px] text-[#1B3A34] border border-[#DCE7E1]">
                                {{ $dl->code ?? '-' }}
                            </code>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $dl->angkatan ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $dl->instansi ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @if($dl->brand)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 border border-amber-200">
                                {{ $dl->brand }}
                            </span>
                            @else
                            <span class="text-[13px] text-[#4B5F5A]">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($dl->has_downloaded)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[11px] font-semibold text-[#388E3C] border border-[#A5D6A7]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#388E3C]"></span>Sudah Diunduh
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F4F8F6] px-2.5 py-1 text-[11px] font-semibold text-[#4B5F5A] border border-[#DCE7E1]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#4B5F5A]"></span>Belum Diunduh
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($dl->code)
                                {{-- Generate per baris --}}
                                <form action="{{ route('admin.membercards.generate.one', $dl->code) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        title="Generate Membercard"
                                        onclick="return confirm('Generate ulang membercard untuk {{ addslashes($dl->name) }}?')"
                                        class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#2D8659] transition hover:border-[#2D8659] hover:bg-[#E8F5E9]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
                                    </button>
                                </form>

                                <a href="{{ route('admin.membercards.show', $dl->code) }}" title="Detail"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('admin.membercards.edit', $dl->code) }}" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                                <button type="button" title="Hapus"
                                    onclick="openDeleteModal('{{ route('admin.membercards.destroy', $dl->code) }}', '{{ addslashes($dl->name) }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                                @else
                                <span class="text-[12px] text-[#4B5F5A]">Kode belum ada</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Belum ada data member card.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info brand --}}
    <p class="mt-3 text-[11px] text-[#4B5F5A]">
        * Tombol <svg class="inline h-3 w-3 text-[#2D8659]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg> untuk generate ulang membercard (hanya berhasil jika status pemagang sudah <strong>Selesai</strong>).
    </p>
</div>

<script>
function openDeleteModal(action, name) {
    document.getElementById('deleteCardName').textContent = name;
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

@endsection
