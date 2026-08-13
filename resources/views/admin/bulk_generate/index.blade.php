@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Generate Dokumen Massal</h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">Generate dokumen untuk seluruh pemagang berdasarkan Brand. Hanya pemagang dengan status <strong>Selesai</strong> yang dapat diproses.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="mb-4 rounded-[10px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
        {{ session('warning') }}
        @if(session('gen_errors'))
        <ul class="mt-2 list-disc list-inside font-normal text-[12.5px] space-y-0.5">
            @foreach(session('gen_errors') as $err)<li>{{ $err }}</li>@endforeach
        </ul>
        @endif
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-[#D32F2F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ===== PANEL KIRI: FILTER ===== --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Form Filter --}}
            <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-[14px] font-bold text-[#1B3A34]">Filter</h2>
                <form method="GET" action="{{ route('admin.bulk-generate.index') }}" class="space-y-4" id="filterForm">

                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jenis Dokumen</label>
                        <select name="doc_type" onchange="this.form.submit()"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            <option value="skl"       {{ $docType === 'skl'       ? 'selected' : '' }}>SKL (Surat Keterangan Lulus)</option>
                            <option value="loa"       {{ $docType === 'loa'       ? 'selected' : '' }}>LOA (Letter of Acceptance)</option>
                            <option value="sertifikat"{{ $docType === 'sertifikat'? 'selected' : '' }}>Sertifikat Magang</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-red-500">*</span></label>
                        <select name="brand" onchange="this.form.submit()"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            <option value="">-- Semua Brand --</option>
                            @foreach($brands as $key => $label)
                            <option value="{{ $key }}" {{ $brand === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Angkatan (Tahun)</label>
                        <select name="angkatan" onchange="this.form.submit()"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            <option value="">-- Semua Angkatan --</option>
                            @foreach($angkatanList as $year)
                            <option value="{{ $year }}" {{ $angkatan == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>

            {{-- Summary Card --}}
            <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-[14px] font-bold text-[#1B3A34]">Ringkasan</h2>
                <div class="space-y-2 text-[13px]">
                    <div class="flex justify-between">
                        <span class="text-[#4B5F5A]">Total ditemukan</span>
                        <span class="font-bold text-[#1B3A34]">{{ $interns->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#4B5F5A]">Siap di-generate</span>
                        <span class="font-bold text-[#2D8659]">{{ $withBrand->count() }}</span>
                    </div>
                    @if($withoutBrand->count() > 0)
                    <div class="flex justify-between">
                        <span class="text-[#4B5F5A]">Belum ada brand</span>
                        <span class="font-bold text-amber-600">{{ $withoutBrand->count() }}</span>
                    </div>
                    @endif
                </div>
                @if($withoutBrand->count() > 0)
                <div class="mt-3 rounded-[8px] bg-amber-50 border border-amber-200 px-3 py-2.5 text-[11.5px] text-amber-700">
                    {{ $withoutBrand->count() }} pemagang belum memiliki brand. Mereka tidak akan ikut diproses.
                </div>
                @endif
            </div>

        </div>

        {{-- ===== PANEL KANAN: DAFTAR PEMAGANG ===== --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">

                {{-- Header tabel --}}
                <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
                    <div>
                        <h2 class="text-[14px] font-bold text-[#1B3A34]">
                            Daftar Pemagang
                            @if($brand) — <span class="text-[#2D8659]">{{ \App\Helpers\BrandHelper::label($brand) }}</span>@endif
                        </h2>
                        <p class="text-[12px] text-[#4B5F5A] mt-0.5">Centang pemagang yang ingin di-generate dokumennya.</p>
                    </div>
                    @if($withBrand->isNotEmpty())
                    <button type="button" onclick="toggleAll()"
                        class="text-[12.5px] font-semibold text-[#2D8659] hover:text-[#1F5F3F] transition">
                        Pilih Semua
                    </button>
                    @endif
                </div>

                {{-- Form generate --}}
                <form method="POST" action="{{ route('admin.bulk-generate.generate') }}" id="generateForm">
                    @csrf
                    <input type="hidden" name="brand"    value="{{ $brand }}">
                    <input type="hidden" name="doc_type" value="{{ $docType }}">
                    <input type="hidden" name="angkatan" value="{{ $angkatan }}">

                    @if($interns->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                        @if(!$brand)
                            Pilih Brand terlebih dahulu untuk melihat daftar pemagang.
                        @else
                            Tidak ada pemagang dengan brand <strong>{{ \App\Helpers\BrandHelper::label($brand) }}</strong> yang sudah selesai.
                        @endif
                    </div>
                    @else
                    <div class="divide-y divide-[#DCE7E1]">
                        @foreach($interns as $intern)
                        @php $hasBrand = !empty($intern->brand); @endphp
                        <div class="flex items-center gap-4 px-5 py-3.5 {{ !$hasBrand ? 'opacity-50' : '' }}">
                            <input type="checkbox" name="ids[]" value="{{ $intern->id }}"
                                {{ $hasBrand ? '' : 'disabled' }}
                                class="intern-checkbox w-4 h-4 accent-[#2D8659] cursor-pointer"
                                {{ $hasBrand ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13.5px] font-semibold text-[#1B3A34]">{{ $intern->fullname }}</p>
                                <p class="text-[11.5px] text-[#4B5F5A]">{{ $intern->internship_interest ?? '-' }} · {{ $intern->institution_name ?? '-' }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($hasBrand)
                                <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                    {{ \App\Helpers\BrandHelper::label($intern->brand) }}
                                </span>
                                @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-400 border border-gray-200">
                                    Belum ada brand
                                </span>
                                @endif
                                <span class="text-[11px] text-[#4B5F5A]">
                                    {{ $intern->start_date ? \Carbon\Carbon::parse($intern->start_date)->format('M Y') : '-' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tombol generate --}}
                    @if($withBrand->isNotEmpty() && $brand)
                    <div class="border-t border-[#DCE7E1] px-5 py-4 flex items-center justify-between gap-4">
                        <p class="text-[12.5px] text-[#4B5F5A]">
                            <span id="selected-count">{{ $withBrand->count() }}</span> pemagang dipilih
                        </p>
                        <button type="submit"
                            onclick="return confirmGenerate()"
                            class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-50"
                            id="btnGenerate">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Generate {{ strtoupper($docType) }} Semua
                        </button>
                    </div>
                    @elseif(!$brand)
                    <div class="border-t border-[#DCE7E1] px-5 py-4 text-center text-[12.5px] text-[#4B5F5A]">
                        Pilih brand terlebih dahulu sebelum generate.
                    </div>
                    @endif
                    @endif
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function toggleAll() {
    const checkboxes = document.querySelectorAll('.intern-checkbox:not(:disabled)');
    const allChecked = [...checkboxes].every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    updateCount();
}

function updateCount() {
    const n = document.querySelectorAll('.intern-checkbox:checked').length;
    const el = document.getElementById('selected-count');
    if (el) el.textContent = n;
    const btn = document.getElementById('btnGenerate');
    if (btn) btn.disabled = n === 0;
}

document.querySelectorAll('.intern-checkbox').forEach(c => {
    c.addEventListener('change', updateCount);
});

function confirmGenerate() {
    const n = document.querySelectorAll('.intern-checkbox:checked').length;
    if (n === 0) { alert('Pilih minimal satu pemagang.'); return false; }
    const docType = document.querySelector('[name="doc_type"]')?.value?.toUpperCase();
    const brand   = document.querySelector('.text-\\[\\#2D8659\\]')?.textContent?.trim() || 'terpilih';
    return confirm(`Generate ${docType} untuk ${n} pemagang brand ${brand}?\n\nProses ini tidak dapat dibatalkan.`);
}
</script>
@endsection
