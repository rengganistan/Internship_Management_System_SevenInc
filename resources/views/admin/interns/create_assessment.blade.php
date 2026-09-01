@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('interns.assessment.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Surat Penilaian</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Tambah Penilaian Magang</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-[#D32F2F]">
        {!! session('error') !!}
    </div>
    @endif

    {{-- ===========================
         MODE TOGGLE: Single vs Bulk
    =========================== --}}
    <div class="mb-5 flex items-center gap-3">
        <button type="button" id="btnModeSingle" onclick="setMode('single')"
            class="mode-btn rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition">
            Penilaian Satu Pemagang
        </button>
        <button type="button" id="btnModeBulk" onclick="setMode('bulk')"
            class="mode-btn rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            Penilaian Semua Pemagang (Bulk)
        </button>
    </div>

    {{-- ===========================
         STEP 1 — PILIH BRAND
    =========================== --}}
    <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm mb-5">
        <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 1 — Pilih Brand</p>
        <div class="flex items-center gap-3 flex-wrap">
            <select id="brandSelect"
                class="rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition min-w-[220px]">
                <option value="">-- Pilih Brand --</option>
                @foreach($brands as $brand)
                <option value="{{ $brand }}" {{ ($selectedBrand ?? '') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                @endforeach
            </select>
            <button type="button" id="btnLoadInterns" onclick="loadInternsByBrand()"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Tampilkan Pemagang
            </button>
            <span id="loadingInterns" class="hidden text-[13px] text-[#4B5F5A]">Memuat...</span>
        </div>
    </div>

    {{-- ===========================
         STEP 2 — PILIH PEMAGANG
    =========================== --}}
    <div id="internListSection" class="hidden rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm mb-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 2 — Pilih Pemagang</p>
            <div id="bulkSelectAll" class="hidden">
                <label class="flex items-center gap-2 text-[13px] font-semibold text-[#1B3A34] cursor-pointer">
                    <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)"
                        class="h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659]">
                    Pilih Semua
                </label>
            </div>
        </div>
        <div id="internListContainer" class="space-y-2 max-h-80 overflow-y-auto pr-1">
            {{-- Isi oleh JS --}}
        </div>
        <div id="bulkProceedSection" class="hidden mt-4 flex justify-end">
            <button type="button" onclick="proceedBulk()"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                Lanjutkan dengan Pemagang Terpilih →
            </button>
        </div>
    </div>

    {{-- ===========================
         FORM SINGLE (1 pemagang)
    =========================== --}}
    <form id="formSingle" action="{{ route('interns.assessment.store') }}" method="POST" enctype="multipart/form-data" class="{{ $selectedIntern ? '' : 'hidden' }}">
    @csrf
    <input type="hidden" name="brand" id="singleBrand" value="{{ $selectedBrand ?? '' }}">
    <div class="space-y-5">

        {{-- Data Pemagang --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Pemagang</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- Nama (readonly setelah dipilih) --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Pemagang</label>
                    <div class="flex items-center gap-2 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5">
                        <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        <span id="singleInternName" class="text-[13px] text-[#1B3A34] font-semibold">
                            {{ $selectedIntern?->fullname ?? '— Belum dipilih —' }}
                        </span>
                    </div>
                    <input type="hidden" name="fullname" id="singleFullname" value="{{ $selectedIntern?->fullname ?? '' }}">
                    <input type="hidden" name="intern_id" id="singleInternId" value="{{ $selectedIntern?->id ?? '' }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">NIM / NIS</label>
                    <input type="text" name="nim_or_nis" id="singleNim"
                        value="{{ old('nim_or_nis', $selectedIntern?->student_id ?? '') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Program Studi</label>
                    <input type="text" name="study_program" id="singleProdi"
                        value="{{ old('study_program', $selectedIntern?->study_program ?? '') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Divisi / Kompetensi Keahlian</label>
                    <select name="div" id="singleDivision"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $div)
                        <option value="{{ $div }}" {{ ($division ?? '') === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Data Penandatangan --}}
        @include('admin.interns._signatory_fields', [
            'prefix'    => 'single',
            'signatory' => $signatory ?? null,
            'brand'     => $selectedBrand ?? null,
            'logos'     => $logos,
            'signatures'=> $signatures,
        ])

        {{-- Aspek Penilaian --}}
        @include('admin.interns._aspek_fields', [
            'formId'  => 'single',
            'aspects' => $aspects,
        ])

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('interns.assessment.index') }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Batal
            </a>
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Simpan Penilaian
            </button>
        </div>
    </div>
    </form>

    {{-- ===========================
         FORM BULK (banyak pemagang)
    =========================== --}}
    <form id="formBulk" action="{{ route('interns.assessment.store_bulk') }}" method="POST" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="brand" id="bulkBrand" value="">
    <div class="space-y-5">

        {{-- Data Penandatangan (shared untuk semua) --}}
        @include('admin.interns._signatory_fields', [
            'prefix'    => 'bulk',
            'signatory' => null,
            'brand'     => null,
            'logos'     => $logos,
            'signatures'=> $signatures,
        ])

        {{-- Daftar pemagang + aspek masing-masing --}}
        <div id="bulkInternsContainer" class="space-y-5">
            {{-- Isi oleh JS --}}
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('interns.assessment.index') }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Batal
            </a>
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Simpan Semua Penilaian
            </button>
        </div>
    </div>
    </form>

</div>

{{-- ========================================================================
     DATA DARI BLADE
======================================================================== --}}
<script>
const ROUTES = {
    internsByBrand : '{{ route('interns.assessment.interns_by_brand') }}',
    aspekByDivision: '{{ route('ajax.aspek') }}',
    saveSignatory  : '{{ route('interns.assessment.save_signatory') }}',
};
const DIVISIONS = @json($divisions);
const CSRF      = '{{ csrf_token() }}';

// Data dari server (kalau ada selectedIntern)
const preSelectedIntern = @json($selectedIntern ? [
    'id'                  => $selectedIntern->id,
    'fullname'            => $selectedIntern->fullname,
    'student_id'          => $selectedIntern->student_id ?? '',
    'study_program'       => $selectedIntern->study_program ?? '',
    'internship_interest' => $selectedIntern->internship_interest ?? '',
    'brand'               => $selectedIntern->brand ?? '',
] : null);
</script>

<script>
// =========================================================================
// STATE
// =========================================================================
let currentMode      = 'single';
let currentBrand     = '';
let loadedInterns    = [];   // data dari API
let selectedSingle   = null; // intern yg dipilih untuk mode single
let selectedBulkIds  = new Set();

// =========================================================================
// MODE TOGGLE
// =========================================================================
function setMode(mode) {
    currentMode = mode;
    const btnSingle = document.getElementById('btnModeSingle');
    const btnBulk   = document.getElementById('btnModeBulk');

    if (mode === 'single') {
        btnSingle.className = 'mode-btn rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition';
        btnBulk.className   = 'mode-btn rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]';
    } else {
        btnBulk.className   = 'mode-btn rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition';
        btnSingle.className = 'mode-btn rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]';
    }

    // Sembunyikan kedua form dulu
    document.getElementById('formSingle').classList.add('hidden');
    document.getElementById('formBulk').classList.add('hidden');

    // Refresh tampilan daftar pemagang
    if (loadedInterns.length > 0) renderInternList();
}

// =========================================================================
// LOAD INTERNS BY BRAND
// =========================================================================
async function loadInternsByBrand() {
    const brand = document.getElementById('brandSelect').value;
    if (!brand) { alert('Pilih brand terlebih dahulu.'); return; }

    currentBrand = brand;
    document.getElementById('loadingInterns').classList.remove('hidden');
    document.getElementById('btnLoadInterns').disabled = true;

    try {
        const res  = await fetch(ROUTES.internsByBrand + '?brand=' + encodeURIComponent(brand));
        const json = await res.json();
        loadedInterns = json.interns || [];

        // Terapkan setting penandatangan tersimpan ke form
        if (json.signatory) applySignatory(json.signatory);

        // Auto-isi nama perusahaan dari brand
        document.getElementById('singleCompanyName').value = brand;
        document.getElementById('bulkCompanyName').value   = brand;
        document.getElementById('singleBrand').value       = brand;
        document.getElementById('bulkBrand').value         = brand;

        renderInternList();
        document.getElementById('internListSection').classList.remove('hidden');
    } catch(e) {
        alert('Gagal memuat data pemagang. Coba lagi.');
        console.error(e);
    } finally {
        document.getElementById('loadingInterns').classList.add('hidden');
        document.getElementById('btnLoadInterns').disabled = false;
    }
}

// =========================================================================
// RENDER INTERN LIST
// =========================================================================
function renderInternList() {
    const container = document.getElementById('internListContainer');
    const bulkCheck = document.getElementById('bulkSelectAll');
    const bulkProceed = document.getElementById('bulkProceedSection');

    if (loadedInterns.length === 0) {
        container.innerHTML = '<p class="text-[13px] text-[#4B5F5A] py-4 text-center">Tidak ada pemagang selesai untuk brand ini.</p>';
        bulkCheck.classList.add('hidden');
        bulkProceed.classList.add('hidden');
        return;
    }

    if (currentMode === 'bulk') {
        bulkCheck.classList.remove('hidden');
        bulkProceed.classList.remove('hidden');
    } else {
        bulkCheck.classList.add('hidden');
        bulkProceed.classList.add('hidden');
    }

    container.innerHTML = loadedInterns.map((intern, idx) => {
        const hasAssess = intern.has_assessment;
        const badge = hasAssess
            ? '<span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200">Sudah Ada</span>'
            : '<span class="inline-flex items-center rounded-full bg-[#E8F5E9] px-2 py-0.5 text-[10px] font-semibold text-[#1F5F3F] border border-[#A5D6A7]">Belum Ada</span>';

        if (currentMode === 'single') {
            return `
            <div class="flex items-center justify-between rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-3 hover:border-[#2D8659] hover:bg-white transition cursor-pointer"
                 onclick="selectSingleIntern(${idx})">
                <div>
                    <p class="text-[13px] font-semibold text-[#1B3A34]">${intern.fullname}</p>
                    <p class="text-[11px] text-[#4B5F5A]">${intern.study_program || '—'} &nbsp;·&nbsp; NIM: ${intern.student_id || '—'}</p>
                </div>
                <div class="flex items-center gap-2">
                    ${badge}
                    <svg class="h-4 w-4 text-[#2D8659]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </div>
            </div>`;
        } else {
            return `
            <div class="flex items-center gap-3 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-3">
                <input type="checkbox" id="bulkCheck_${intern.id}" value="${intern.id}"
                    class="h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659]"
                    ${selectedBulkIds.has(intern.id) ? 'checked' : ''}
                    onchange="toggleBulkCheck(${intern.id}, this.checked)">
                <label for="bulkCheck_${intern.id}" class="flex-1 cursor-pointer">
                    <p class="text-[13px] font-semibold text-[#1B3A34]">${intern.fullname}</p>
                    <p class="text-[11px] text-[#4B5F5A]">${intern.study_program || '—'} &nbsp;·&nbsp; NIM: ${intern.student_id || '—'}</p>
                </label>
                ${badge}
            </div>`;
        }
    }).join('');
}

// =========================================================================
// MODE SINGLE — pilih satu pemagang
// =========================================================================
function selectSingleIntern(idx) {
    const intern = loadedInterns[idx];
    selectedSingle = intern;

    // Fill hidden fields & tampilan nama
    document.getElementById('singleInternId').value   = intern.id;
    document.getElementById('singleFullname').value   = intern.fullname;
    document.getElementById('singleInternName').textContent = intern.fullname;
    document.getElementById('singleNim').value        = intern.student_id || '';
    document.getElementById('singleProdi').value      = intern.study_program || '';
    document.getElementById('singleBrand').value      = currentBrand;

    // Auto-isi divisi dari internship_interest
    const divSelect = document.getElementById('singleDivision');
    const mapped    = mapInterestToDivision(intern.internship_interest || '');
    if (mapped) {
        divSelect.value = mapped;
        // Load aspek default untuk divisi ini
        loadAspekForDivision('single', mapped);
    }

    // Tampilkan form single, sembunyikan list
    document.getElementById('formSingle').classList.remove('hidden');
    document.getElementById('internListSection').classList.add('hidden');

    // Scroll ke form
    document.getElementById('formSingle').scrollIntoView({ behavior: 'smooth' });
}

// =========================================================================
// MODE BULK — pilih semua / beberapa
// =========================================================================
function toggleCheckAll(chk) {
    loadedInterns.forEach(i => {
        selectedBulkIds[chk.checked ? 'add' : 'delete'](i.id);
        const el = document.getElementById('bulkCheck_' + i.id);
        if (el) el.checked = chk.checked;
    });
}

function toggleBulkCheck(id, checked) {
    selectedBulkIds[checked ? 'add' : 'delete'](id);
    // Sinkron checkAll
    const allChecked = loadedInterns.every(i => selectedBulkIds.has(i.id));
    document.getElementById('checkAll').checked = allChecked;
}

function proceedBulk() {
    if (selectedBulkIds.size === 0) { alert('Pilih minimal satu pemagang.'); return; }

    const selected = loadedInterns.filter(i => selectedBulkIds.has(i.id));
    buildBulkForm(selected);

    document.getElementById('formBulk').classList.remove('hidden');
    document.getElementById('internListSection').classList.add('hidden');
    document.getElementById('formBulk').scrollIntoView({ behavior: 'smooth' });
}

// =========================================================================
// BUILD BULK FORM
// =========================================================================
async function buildBulkForm(interns) {
    const container = document.getElementById('bulkInternsContainer');
    container.innerHTML = '<p class="text-[13px] text-[#4B5F5A]">Memuat formulir...</p>';

    // Kumpulkan semua aspek per divisi secara paralel
    const divisionAspeks = {};
    const uniqueDivisions = [...new Set(interns.map(i => mapInterestToDivision(i.internship_interest || '') || 'Content Writer'))];

    await Promise.all(uniqueDivisions.map(async div => {
        try {
            const res = await fetch(ROUTES.aspekByDivision + '?division=' + encodeURIComponent(div));
            const json = await res.json();
            divisionAspeks[div] = json.aspek || [];
        } catch(e) {
            divisionAspeks[div] = [{ aspek: 'Kedisiplinan', nilai: 95 }, { aspek: 'Kerjasama', nilai: 95 }, { aspek: 'Kehadiran', nilai: 95 }];
        }
    }));

    container.innerHTML = interns.map((intern, idx) => {
        const div     = mapInterestToDivision(intern.internship_interest || '') || 'Content Writer';
        const aspeks  = divisionAspeks[div] || [];
        const divOpts = DIVISIONS.map(d => `<option value="${d}" ${d === div ? 'selected' : ''}>${d}</option>`).join('');

        const aspekRows = aspeks.map((item, i) => `
            <tr class="hover:bg-[#F4F8F6]">
                <td class="px-4 py-2 text-center text-[13px] text-[#4B5F5A]">${i + 1}</td>
                <td class="px-3 py-2"><input type="text" name="interns[${idx}][aspek][]" value="${escHtml(item.aspek)}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
                <td class="px-3 py-2"><input type="number" name="interns[${idx}][nilai][]" value="${item.nilai}" min="0" max="100"
                    oninput="updateBulkAvg(${idx})"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
                <td class="px-3 py-2 text-center">
                    <button type="button" onclick="deleteBulkRow(this, ${idx})"
                        class="flex h-7 w-7 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] hover:bg-red-100 mx-auto">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </td>
            </tr>`).join('');

        return `
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 bg-[#1B3A34] px-5 py-3">
                <svg class="h-4 w-4 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                <span class="text-[13px] font-bold text-white">${escHtml(intern.fullname)}</span>
                <span class="text-[11px] text-white/70">— ${escHtml(intern.study_program || '—')}</span>
            </div>
            <div class="p-5">
                <input type="hidden" name="interns[${idx}][intern_id]" value="${intern.id}">
                <input type="hidden" name="interns[${idx}][fullname]" value="${escHtml(intern.fullname)}">
                <input type="hidden" name="interns[${idx}][nim_or_nis]" value="${escHtml(intern.student_id || '')}">
                <input type="hidden" name="interns[${idx}][study_program]" value="${escHtml(intern.study_program || '')}">

                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Divisi / Kompetensi</label>
                        <select name="interns[${idx}][div]" onchange="reloadBulkAspek(this, ${idx})"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                            ${divOpts}
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="bulkTable_${idx}">
                        <thead>
                            <tr>
                                <th class="bg-[#1B3A34] px-4 py-2 text-[10px] font-bold uppercase text-white w-10 text-center">No</th>
                                <th class="bg-[#1B3A34] px-4 py-2 text-[10px] font-bold uppercase text-white">Aspek</th>
                                <th class="bg-[#1B3A34] px-4 py-2 text-[10px] font-bold uppercase text-white w-24 text-center">Nilai</th>
                                <th class="bg-[#1B3A34] px-4 py-2 text-[10px] font-bold uppercase text-white w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#DCE7E1]" id="bulkTbody_${idx}">
                            ${aspekRows}
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-[#DCE7E1] bg-[#F4F8F6]">
                                <td colspan="2" class="px-4 py-2 text-right text-[13px] font-bold text-[#1B3A34]">Rata-rata</td>
                                <td class="px-4 py-2 text-center text-[14px] font-bold text-[#2D8659]" id="bulkAvg_${idx}">—</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-3 flex justify-between items-center">
                    <button type="button" onclick="addBulkRow(${idx})"
                        class="flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12px] font-semibold text-[#1B3A34] hover:border-[#2D8659] hover:text-[#1F5F3F]">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Aspek
                    </button>
                    <p class="text-[10px] text-[#4B5F5A]">81–100: Amat Baik &nbsp;|&nbsp; 65–80: Baik &nbsp;|&nbsp; 50–64: Cukup &nbsp;|&nbsp; &lt;50: Kurang</p>
                </div>
            </div>
        </div>`;
    }).join('');

    // Hitung rata-rata awal
    interns.forEach((_, idx) => updateBulkAvg(idx));
}

// =========================================================================
// BULK TABLE HELPERS
// =========================================================================
function updateBulkAvg(idx) {
    const tbody = document.getElementById('bulkTbody_' + idx);
    if (!tbody) return;
    const inputs = tbody.querySelectorAll('input[type="number"]');
    let total = 0, count = 0;
    inputs.forEach(inp => { const v = parseFloat(inp.value); if (!isNaN(v)) { total += v; count++; } });
    const avgEl = document.getElementById('bulkAvg_' + idx);
    if (avgEl) avgEl.textContent = count ? (total / count).toFixed(2) : '—';
}

function addBulkRow(idx) {
    const tbody = document.getElementById('bulkTbody_' + idx);
    const rowCount = tbody.querySelectorAll('tr').length;
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-[#F4F8F6]';
    tr.innerHTML = `
        <td class="px-4 py-2 text-center text-[13px] text-[#4B5F5A]">${rowCount + 1}</td>
        <td class="px-3 py-2"><input type="text" name="interns[${idx}][aspek][]" value="Aspek Baru"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
        <td class="px-3 py-2"><input type="number" name="interns[${idx}][nilai][]" value="0" min="0" max="100"
            oninput="updateBulkAvg(${idx})"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
        <td class="px-3 py-2 text-center">
            <button type="button" onclick="deleteBulkRow(this, ${idx})"
                class="flex h-7 w-7 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] hover:bg-red-100 mx-auto">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </td>`;
    tbody.appendChild(tr);
    reindexBulkRows(idx);
    updateBulkAvg(idx);
}

function deleteBulkRow(btn, idx) {
    btn.closest('tr').remove();
    reindexBulkRows(idx);
    updateBulkAvg(idx);
}

function reindexBulkRows(idx) {
    const tbody = document.getElementById('bulkTbody_' + idx);
    if (!tbody) return;
    tbody.querySelectorAll('tr').forEach((r, i) => r.cells[0].textContent = i + 1);
}

async function reloadBulkAspek(select, idx) {
    const div = select.value;
    try {
        const res  = await fetch(ROUTES.aspekByDivision + '?division=' + encodeURIComponent(div));
        const json = await res.json();
        const tbody = document.getElementById('bulkTbody_' + idx);
        tbody.innerHTML = (json.aspek || []).map((item, i) => `
            <tr class="hover:bg-[#F4F8F6]">
                <td class="px-4 py-2 text-center text-[13px] text-[#4B5F5A]">${i + 1}</td>
                <td class="px-3 py-2"><input type="text" name="interns[${idx}][aspek][]" value="${escHtml(item.aspek)}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
                <td class="px-3 py-2"><input type="number" name="interns[${idx}][nilai][]" value="${item.nilai}" min="0" max="100"
                    oninput="updateBulkAvg(${idx})"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]"></td>
                <td class="px-3 py-2 text-center">
                    <button type="button" onclick="deleteBulkRow(this, ${idx})"
                        class="flex h-7 w-7 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] hover:bg-red-100 mx-auto">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </td>
            </tr>`).join('');
        updateBulkAvg(idx);
    } catch(e) { console.error(e); }
}

// =========================================================================
// SINGLE FORM — aspek & rata-rata
// =========================================================================
document.getElementById('singleDivision').addEventListener('change', function() {
    loadAspekForDivision('single', this.value);
});

async function loadAspekForDivision(prefix, div) {
    if (!div) return;
    try {
        const res  = await fetch(ROUTES.aspekByDivision + '?division=' + encodeURIComponent(div));
        const json = await res.json();
        const tbody = document.getElementById('singleAspekTbody');
        tbody.innerHTML = (json.aspek || []).map((item, i) => `
            <tr class="hover:bg-[#F4F8F6]">
                <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]">${i + 1}</td>
                <td class="px-3 py-2"><input type="text" name="aspek[]" value="${escHtml(item.aspek)}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
                <td class="px-3 py-2"><input type="number" name="nilai[]" value="${item.nilai}" min="0" max="100"
                    oninput="updateSingleAvg()"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
                <td class="px-3 py-2 text-center">
                    <button type="button" onclick="deleteSingleRow(this)"
                        class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </td>
            </tr>`).join('');
        updateSingleAvg();
    } catch(e) { console.error(e); }
}

function updateSingleAvg() {
    const inputs = document.querySelectorAll('#singleAspekTbody input[name="nilai[]"]');
    let total = 0, count = 0;
    inputs.forEach(i => { const v = parseFloat(i.value); if (!isNaN(v)) { total += v; count++; } });
    document.getElementById('singleAvg').textContent = count ? (total / count).toFixed(2) : '0';
}

function addSingleRow() {
    const tbody = document.getElementById('singleAspekTbody');
    const count = tbody.querySelectorAll('tr').length;
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-[#F4F8F6]';
    tr.innerHTML = `
        <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]">${count + 1}</td>
        <td class="px-3 py-2"><input type="text" name="aspek[]" value="Aspek Baru"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
        <td class="px-3 py-2"><input type="number" name="nilai[]" value="0" min="0" max="100" oninput="updateSingleAvg()"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
        <td class="px-3 py-2 text-center">
            <button type="button" onclick="deleteSingleRow(this)"
                class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </td>`;
    tbody.appendChild(tr);
    reindexSingleRows();
    updateSingleAvg();
}

function deleteSingleRow(btn) {
    btn.closest('tr').remove();
    reindexSingleRows();
    updateSingleAvg();
}

function reindexSingleRows() {
    document.querySelectorAll('#singleAspekTbody tr').forEach((r, i) => r.cells[0].textContent = i + 1);
}

// =========================================================================
// APPLY SIGNATORY FROM SAVED SETTING
// =========================================================================
function applySignatory(sig) {
    ['single', 'bulk'].forEach(prefix => {
        const companyName = document.getElementById(prefix + 'CompanyName');
        const companyAddr = document.getElementById(prefix + 'CompanyAddress');
        const sigName     = document.getElementById(prefix + 'SignatureName');
        const sigPos      = document.getElementById(prefix + 'SignaturePosition');

        if (companyName && sig.company_name)   companyName.value = sig.company_name;
        if (companyAddr && sig.company_address) companyAddr.value = sig.company_address;
        if (sigName     && sig.signature_name)  sigName.value     = sig.signature_name;
        if (sigPos      && sig.signature_position) sigPos.value   = sig.signature_position;

        // Logo & TTD — update selector jika path cocok
        if (sig.company_logo_path) {
            const logoSel = document.getElementById(prefix + 'LogoSelect');
            if (logoSel) logoSel.value = sig.company_logo_path;
        }
        if (sig.signature_image_path) {
            const sigSel = document.getElementById(prefix + 'SigSelect');
            if (sigSel) sigSel.value = sig.signature_image_path;
        }
    });
}

// =========================================================================
// MAP interest → division label
// =========================================================================
function mapInterestToDivision(raw) {
    const map = {
        'project-manager': 'Project Manager',
        'administration': 'Administration',
        'administrasi': 'Administration',
        'hr': 'Human Resources (HR)',
        'human resources (hr)': 'Human Resources (HR)',
        'uiux': 'UI/UX',
        'ui/ux': 'UI/UX',
        'programmer': 'Programmer (Front End / Backend)',
        'programmer (front end / backend)': 'Programmer (Front End / Backend)',
        'photographer': 'Photographer',
        'fotografer': 'Photographer',
        'videographer': 'Videographer',
        'videografer': 'Videographer',
        'graphic-designer': 'Graphic Designer',
        'desainer grafis': 'Graphic Designer',
        'social-media-specialist': 'Social Media Specialist',
        'content-writer': 'Content Writer',
        'content-planner': 'Content Planner',
        'marketing-and-sales': 'Sales & Marketing',
        'public-relation': 'Public Relations (Marcomm)',
        'public relations (marcomm)': 'Public Relations (Marcomm)',
        'digital-marketing': 'Digital Marketing',
        'tiktok-creator': 'TikTok Creator',
        'welding': 'Welding',
        'pengelasan': 'Welding',
        'customer-service': 'Customer Service',
    };
    const key = (raw || '').toLowerCase().trim();
    return map[key] || (DIVISIONS.includes(raw) ? raw : null);
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// =========================================================================
// INIT — kalau ada preSelectedIntern dari URL
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    updateSingleAvg();
    reindexSingleRows();

    if (preSelectedIntern) {
        selectedSingle  = preSelectedIntern;
        currentBrand    = preSelectedIntern.brand || '';
        const sel       = document.getElementById('brandSelect');
        if (sel && currentBrand) sel.value = currentBrand;
    }
});

// =========================================================================
// SIGNATORY AJAX SAVE & TOAST
// =========================================================================
async function saveSignatoryNow(prefix) {
    // Ambil brand dari hidden input di form yang sesuai
    const brandEl = document.getElementById(prefix + 'Brand')
        ?? document.getElementById('brandSelect');
    const brand = brandEl?.value || currentBrand;
    if (!brand) { showToast('❌ Pilih brand terlebih dahulu.', 'error'); return; }

    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('brand',              brand);
    fd.append('company_name',       document.getElementById(prefix + 'CompanyName')?.value || '');
    fd.append('company_address',    document.getElementById(prefix + 'CompanyAddress')?.value || '');
    fd.append('signature_name',     document.getElementById(prefix + 'SignatureName')?.value || '');
    fd.append('signature_position', document.getElementById(prefix + 'SignaturePosition')?.value || '');

    // Logo & TTD upload jika ada
    const formEl   = document.getElementById('form' + prefix.charAt(0).toUpperCase() + prefix.slice(1));
    const logoInput = formEl?.querySelector('input[name="company_logo"]');
    const sigInput  = formEl?.querySelector('input[name="signature_image"]');
    if (logoInput?.files[0]) fd.append('company_logo', logoInput.files[0]);
    if (sigInput?.files[0])  fd.append('signature_image', sigInput.files[0]);

    const logoSel = document.getElementById(prefix + 'LogoSelect');
    const sigSel  = document.getElementById(prefix + 'SigSelect');
    if (logoSel?.value) fd.append('company_logo_select', logoSel.value);
    if (sigSel?.value)  fd.append('signature_image_select', sigSel.value);

    try {
        const res  = await fetch(ROUTES.saveSignatory, { method: 'POST', body: fd });
        const json = await res.json();
        showToast(json.success ? '✅ ' + json.message : '❌ ' + (json.message || 'Gagal'), json.success ? 'success' : 'error');
    } catch(e) {
        showToast('❌ Gagal koneksi ke server.', 'error');
        console.error(e);
    }
}

function showToast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = 'fixed bottom-6 right-6 z-[200] rounded-[10px] px-4 py-3 text-[13px] font-semibold shadow-lg transition '
        + (type === 'success' ? 'bg-[#E8F5E9] border border-[#A5D6A7] text-[#1F5F3F]' : 'bg-red-50 border border-red-200 text-[#D32F2F]');
    el.innerHTML = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}
</script>

@endsection
