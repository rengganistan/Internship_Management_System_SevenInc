@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.certificate.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Sertifikat</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Sertifikat Peserta Non-Magang</h1>
            <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Untuk peserta di luar program magang. Divisi otomatis: <strong>EXT</strong>.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate.external.store') }}">
    @csrf
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Informasi Umum ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Informasi Umum</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Perusahaan Penerbit <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="company" value="{{ old('company', 'Seven Inc') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="city" value="{{ old('city', 'Yogyakarta') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-[#D32F2F]">*</span></label>
                    <select name="brand" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih Brand</option>
                        @foreach($brands as $code => $label)
                        <option value="{{ $code }}" {{ old('brand') === $code ? 'selected' : '' }}>{{ $label }} ({{ $code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tgl Mulai <span class="text-[#D32F2F]">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tgl Selesai <span class="text-[#D32F2F]">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', now()->toDateString()) }}" required
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 2: Aset Visual ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Background <span class="text-[#D32F2F]">*</span></label>
                    <select name="background_image" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file</option>
                        @foreach($backgroundFiles as $f)
                        <option value="{{ $f }}" {{ old('background_image') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali bg_</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 1 <span class="text-[#D32F2F]">*</span></label>
                    <select name="logo1" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ old('logo1') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali logo_</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select name="logo2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa logo 2 -</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ old('logo2') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 1 <span class="text-[#D32F2F]">*</span></label>
                    <select name="signature_image1" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ old('signature_image1') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali ttd_</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select name="signature_image2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa ttd 2 -</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ old('signature_image2') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 3: Penandatangan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Penandatangan</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan 1 <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="name_signatory1" value="{{ old('name_signatory1') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <input type="text" name="name_signatory2" value="{{ old('name_signatory2') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan 1 <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="role1" value="{{ old('role1') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <input type="text" name="role2" value="{{ old('role2') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 4: Daftar Peserta ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Daftar Peserta</p>
                    <p class="mt-0.5 text-[12px] text-[#4B5F5A]">Semua peserta memakai setting yang sama di atas.</p>
                </div>
                <button type="button" id="btnAddRow"
                    class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Peserta
                </button>
            </div>

            <div id="participantRows" class="space-y-2">
                @php $oldRows = old('participants', [['name'=>'']]); @endphp
                @foreach($oldRows as $i => $row)
                <div class="flex items-center gap-2 row-item">
                    <input type="text" name="participants[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}"
                        placeholder="Nama peserta {{ $i + 1 }}" required
                        class="flex-1 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    <button type="button" class="btnDel flex h-9 w-9 shrink-0 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                @endforeach
            </div>

            <template id="tplRow">
                <div class="flex items-center gap-2 row-item">
                    <input type="text" name="__NAME__" placeholder="Nama peserta" required
                        class="flex-1 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    <button type="button" class="btnDel flex h-9 w-9 shrink-0 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.certificate.index') }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Batal
            </a>
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Buat Sertifikat
            </button>
        </div>

    </div>
    </form>
</div>

<script>
(function() {
    const rowsEl = document.getElementById('participantRows');
    const tpl    = document.getElementById('tplRow').innerHTML;
    let idx      = rowsEl.querySelectorAll('.row-item').length;

    document.getElementById('btnAddRow').addEventListener('click', () => {
        const html = tpl.replace('__NAME__', `participants[${idx}][name]`).replace('Nama peserta', `Nama peserta ${idx + 1}`);
        const wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        rowsEl.appendChild(wrap.firstChild);
        idx++;
    });

    rowsEl.addEventListener('click', e => {
        if (e.target.closest('.btnDel')) {
            const item = e.target.closest('.row-item');
            if (rowsEl.querySelectorAll('.row-item').length > 1) item?.remove();
        }
    });
})();
</script>

@endsection
