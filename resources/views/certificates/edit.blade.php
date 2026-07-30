@extends('layouts.dashboard')

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $bgUrl   = $certificate->background_image  ? Storage::url($certificate->background_image)  : '';
    $logo1U  = $certificate->logo1             ? Storage::url($certificate->logo1)             : '';
    $logo2U  = $certificate->logo2             ? Storage::url($certificate->logo2)             : '';
    $ttd1U   = $certificate->signature_image1  ? Storage::url($certificate->signature_image1)  : '';
    $ttd2U   = $certificate->signature_image2  ? Storage::url($certificate->signature_image2)  : '';

    function sel($a, $b){ return (string)$a === (string)$b ? 'selected' : ''; }
@endphp

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.certificate.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Sertifikat</p>
                <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Edit Sertifikat</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.certificate.show', $certificate->id) }}"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview
            </a>
            <a href="{{ route('admin.certificate.pdf', $certificate->id) }}"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate.update', $certificate->id) }}" id="certEditForm">
    @csrf @method('PUT')
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Data Sertifikat ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Sertifikat</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $certificate->name) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Divisi <span class="text-[#D32F2F]">*</span></label>
                    @if(strtoupper($certificate->division) === 'EXT')
                    <div class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#4B5F5A]">
                        Eksternal (EXT)
                    </div>
                    <input type="hidden" name="division" value="EXT">
                    @else
                    <select name="division" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih Divisi</option>
                        @php $curDiv = old('division', $certificate->division); @endphp
                        @if($curDiv && !array_key_exists($curDiv, $divisions))
                        <option value="{{ $curDiv }}" selected>{{ $curDiv }}</option>
                        @endif
                        @foreach($divisions as $code => $label)
                        <option value="{{ $code }}" {{ (string)$curDiv === (string)$code ? 'selected' : '' }}>
                            {{ $label }} ({{ $code }})
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Perusahaan <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="company" value="{{ old('company', $certificate->company) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="city" value="{{ old('city', $certificate->city) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-[#D32F2F]">*</span></label>
                    <select name="brand" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih Brand</option>
                        @foreach($brands as $code => $label)
                        <option value="{{ $code }}" {{ sel(old('brand', $certificate->brand), $code) }}>
                            {{ $label }} ({{ $code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nomor Serial</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $certificate->serial_number) }}" readonly
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#4B5F5A] outline-none cursor-default">
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Nomor serial tidak dapat diubah setelah sertifikat dibuat.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanggal Mulai <span class="text-[#D32F2F]">*</span></label>
                    <input type="date" id="start_date" name="start_date"
                        value="{{ old('start_date', \Carbon\Carbon::parse($certificate->start_date)->format('Y-m-d')) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanggal Selesai <span class="text-[#D32F2F]">*</span></label>
                    <input type="date" id="end_date" name="end_date"
                        value="{{ old('end_date', \Carbon\Carbon::parse($certificate->end_date)->format('Y-m-d')) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 2: Aset Visual ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Background --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Background</label>
                    <select id="sel_bg"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file (bg_)</option>
                        @foreach($backgroundFiles as $f)
                        <option value="{{ $f }}" {{ sel(old('background_image', basename($certificate->background_image)), $f) }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    @if($bgUrl)
                    <img id="prev_bg" src="{{ $bgUrl }}" class="mt-2 max-h-20 w-full rounded-[8px] object-cover border border-[#DCE7E1]" alt="Preview">
                    @else
                    <img id="prev_bg" class="mt-2 max-h-20 w-full rounded-[8px] object-cover border border-[#DCE7E1] hidden" alt="Preview">
                    @endif
                </div>

                {{-- Logo 1 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 1</label>
                    <select id="sel_logo1"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file (logo_)</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ sel(old('logo1', basename($certificate->logo1)), $f) }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    @if($logo1U)
                    <img id="prev_logo1" src="{{ $logo1U }}" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1]" alt="Preview">
                    @else
                    <img id="prev_logo1" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                    @endif
                </div>

                {{-- Logo 2 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select id="sel_logo2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa logo 2 -</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ sel(old('logo2', basename($certificate->logo2)), $f) }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    @if($logo2U)
                    <img id="prev_logo2" src="{{ $logo2U }}" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1]" alt="Preview">
                    @else
                    <img id="prev_logo2" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 3: Penandatangan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Penandatangan</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan 1 <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="name_signatory1" value="{{ old('name_signatory1', $certificate->name_signatory1) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <input type="text" name="name_signatory2" value="{{ old('name_signatory2', $certificate->name_signatory2) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan 1 <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" name="role1" value="{{ old('role1', $certificate->role1) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <input type="text" name="role2" value="{{ old('role2', $certificate->role2) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                {{-- TTD 1 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 1</label>
                    <select id="sel_ttd1"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file (ttd_)</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ sel(old('signature_image1', basename($certificate->signature_image1)), $f) }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    @if($ttd1U)
                    <img id="prev_ttd1" src="{{ $ttd1U }}" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1]" alt="Preview">
                    @else
                    <img id="prev_ttd1" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                    @endif
                </div>

                {{-- TTD 2 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select id="sel_ttd2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa ttd 2 -</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ sel(old('signature_image2', basename($certificate->signature_image2)), $f) }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    @if($ttd2U)
                    <img id="prev_ttd2" src="{{ $ttd2U }}" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1]" alt="Preview">
                    @else
                    <img id="prev_ttd2" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                    @endif
                </div>
            </div>
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
                Simpan Perubahan
            </button>
        </div>

    </div>
    </form>
</div>

<script>
// Preview preview gambar saat dropdown berubah
function bindPreview(selId, imgId, baseDir) {
    const sel = document.getElementById(selId);
    const img = document.getElementById(imgId);
    if (!sel || !img) return;
    sel.addEventListener('change', () => {
        if (!sel.value) { img.src = ''; img.classList.add('hidden'); return; }
        img.src = '/storage/' + baseDir + '/' + sel.value;
        img.classList.remove('hidden');
    });
}
bindPreview('sel_bg',    'prev_bg',    'images/backgrounds');
bindPreview('sel_logo1', 'prev_logo1', 'images/logos');
bindPreview('sel_logo2', 'prev_logo2', 'images/logos');
bindPreview('sel_ttd1',  'prev_ttd1',  'images/signature');
bindPreview('sel_ttd2',  'prev_ttd2',  'images/signature');

// Tanggal min
const startEl = document.getElementById('start_date');
const endEl   = document.getElementById('end_date');
if (startEl && endEl) {
    if (startEl.value) endEl.min = startEl.value;
    startEl.addEventListener('change', () => {
        endEl.min = startEl.value;
        if (endEl.value && new Date(endEl.value) < new Date(startEl.value)) endEl.value = startEl.value;
    });
}

// Saat submit: kirim nilai dropdown gambar sebagai hidden input
// (karena controller update() masih expect file upload, tapi kita override ke pilihan yang sudah ada)
document.getElementById('certEditForm')?.addEventListener('submit', function() {
    const maps = [
        ['sel_bg',    'background_image'],
        ['sel_logo1', 'logo1'],
        ['sel_logo2', 'logo2'],
        ['sel_ttd1',  'signature_image1'],
        ['sel_ttd2',  'signature_image2'],
    ];
    maps.forEach(([selId, fieldName]) => {
        const sel = document.getElementById(selId);
        if (!sel) return;
        // Lepas name dari select agar tidak terkirim duplikat
        sel.removeAttribute('name');
        // Buat hidden input dengan path lengkap hanya jika ada nilai
        if (sel.value) {
            const base = { background_image:'images/backgrounds', logo1:'images/logos', logo2:'images/logos', signature_image1:'images/signature', signature_image2:'images/signature' };
            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = fieldName;
            hidden.value = base[fieldName] + '/' + sel.value;
            this.appendChild(hidden);
        }
    });
});
</script>

@endsection
