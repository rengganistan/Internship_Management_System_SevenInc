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
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Buat Sertifikat Selesai Magang</h1>
            <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Pilih brand → pilih pemagang → isi aset visual & penandatangan → buat sekaligus.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 flex items-center gap-2 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate.store') }}" id="certBulkForm">
    @csrf
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Pilih Brand ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 1 — Pilih Brand</p>
            <p class="mb-4 text-[12.5px] text-[#4B5F5A]">Pemagang yang sudah <strong>selesai magang</strong> dari brand ini akan muncul di bawah.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-[#D32F2F]">*</span></label>
                    <select id="brandSelect" name="brand" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih Brand --</option>
                        @foreach($brands as $code => $label)
                        <option value="{{ $code }}" {{ old('brand') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Perusahaan <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" id="companyInput" name="company" value="{{ old('company', 'Seven Inc') }}" required
                        placeholder="Nama perusahaan yang tertera di sertifikat"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" id="cityInput" name="city" value="{{ old('city', 'Yogyakarta') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 2: Daftar Pemagang Selesai ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 2 — Pilih Pemagang</p>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Hanya pemagang berstatus <strong>Selesai</strong> dari brand yang dipilih yang ditampilkan.</p>
                </div>
                <div class="flex items-center gap-2" id="selectAllWrap" style="display:none!important">
                    <label class="flex cursor-pointer items-center gap-2 text-[13px] font-semibold text-[#1B3A34]">
                        <input type="checkbox" id="selectAllCheck"
                            class="h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659]">
                        Pilih Semua
                    </label>
                    <span id="selectedCount" class="rounded-full bg-[#2D8659] px-2.5 py-0.5 text-[11px] font-bold text-white">0</span>
                </div>
            </div>

            {{-- Loading state --}}
            <div id="internsLoading" class="hidden py-8 text-center text-[13px] text-[#4B5F5A]">
                <svg class="mx-auto mb-2 h-6 w-6 animate-spin text-[#2D8659]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Memuat data pemagang...
            </div>

            {{-- Empty state --}}
            <div id="internsEmpty" class="rounded-[10px] border border-dashed border-[#DCE7E1] py-12 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-[#DCE7E1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/></svg>
                <p class="text-[13px] font-semibold text-[#4B5F5A]">Pilih brand terlebih dahulu</p>
                <p class="mt-1 text-[12px] text-[#4B5F5A]">Daftar pemagang yang sudah selesai akan muncul di sini.</p>
            </div>

            {{-- Tabel pemagang --}}
            <div id="internsTable" class="hidden overflow-hidden rounded-[10px] border border-[#DCE7E1]">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-10">
                                <input type="checkbox" id="tableSelectAll" class="h-4 w-4 rounded border-white accent-white cursor-pointer">
                            </th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Divisi</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Institusi</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Periode</th>
                        </tr>
                    </thead>
                    <tbody id="internsTableBody" class="divide-y divide-[#DCE7E1]">
                    </tbody>
                </table>
            </div>

            {{-- Pesan tidak ada pemagang --}}
            <div id="internsNone" class="hidden rounded-[10px] border border-dashed border-amber-200 bg-amber-50 py-8 text-center">
                <svg class="mx-auto mb-2 h-8 w-8 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p class="text-[13px] font-semibold text-amber-700">Tidak ada pemagang selesai untuk brand ini</p>
                <p class="mt-1 text-[12px] text-amber-600">Pemagang dengan status <strong>Selesai</strong> pada brand ini belum ada.</p>
            </div>
        </div>

        {{-- ===== SEKSI 3: Aset Visual ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 3 — Aset Visual</p>
            <p class="mb-4 text-[12.5px] text-[#4B5F5A]">Aset ini berlaku untuk semua sertifikat yang dibuat. Setiap brand biasanya punya background dan logo masing-masing.</p>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Background --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Background <span class="text-[#D32F2F]">*</span></label>
                    <select id="sel_bg" name="background_image" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file background</option>
                        @foreach($backgroundFiles as $f)
                        <option value="{{ $f }}" {{ old('background_image') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali bg_</p>
                    <img id="prev_bg" class="mt-2 max-h-20 w-full rounded-[8px] object-cover border border-[#DCE7E1] hidden" alt="Preview">
                </div>

                {{-- Logo 1 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 1 <span class="text-[#D32F2F]">*</span></label>
                    <select id="sel_logo1" name="logo1" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file logo</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ old('logo1') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali logo_</p>
                    <img id="prev_logo1" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                </div>

                {{-- Logo 2 --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select id="sel_logo2" name="logo2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa logo 2 -</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ old('logo2') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <img id="prev_logo2" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 4: Penandatangan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 4 — Penandatangan</p>
            <p class="mb-4 text-[12.5px] text-[#4B5F5A]">Penandatangan juga berlaku untuk semua sertifikat. Sesuaikan dengan brand yang dipilih.</p>
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

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 1 <span class="text-[#D32F2F]">*</span></label>
                    <select id="sel_ttd1" name="signature_image1" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file tanda tangan</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ old('signature_image1') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Diawali ttd_</p>
                    <img id="prev_ttd1" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 2 <span class="text-[11px] font-normal text-[#4B5F5A]">(opsional)</span></label>
                    <select id="sel_ttd2" name="signature_image2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">- Tanpa tanda tangan 2 -</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ old('signature_image2') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <img id="prev_ttd2" class="mt-2 max-h-16 rounded-[8px] border border-[#DCE7E1] hidden" alt="Preview">
                </div>
            </div>
        </div>

        {{-- ===== Ringkasan & Submit ===== --}}
        <div id="submitSection" class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div id="summaryText" class="text-[13px] text-[#4B5F5A]">
                    <span id="summaryCount" class="font-semibold text-[#1B3A34]">0 pemagang</span> dipilih
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.certificate.index') }}"
                        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn" disabled
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span id="submitBtnText">Buat Sertifikat</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = "{{ route('admin.certificate.interns-by-brand') }}";

    // Elements
    const brandSel    = document.getElementById('brandSelect');
    const tableWrap   = document.getElementById('internsTable');
    const tableBody   = document.getElementById('internsTableBody');
    const emptyWrap   = document.getElementById('internsEmpty');
    const noneWrap    = document.getElementById('internsNone');
    const loadingWrap = document.getElementById('internsLoading');
    const selectAllWrap = document.getElementById('selectAllWrap');
    const tableSelectAll = document.getElementById('tableSelectAll');
    const summaryCount  = document.getElementById('summaryCount');
    const submitBtn     = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');

    // Brand map for company auto-fill
    const brandCompanyMap = {
        'MJ': 'Magangjogja.com', 'AK': 'Areakerja.com', 'RW': 'Republikweb.com',
        'TS': 'Titipsini.com', 'AP': 'Ambilpaket.com', 'BK': 'Bikinkepo.com',
        'BC': 'Bimbelcerdas.com', 'LK': 'Latihankerja.com', 'LJT': 'Lowkerjateng.com',
        'LJG': 'Lowkerjogja.com', 'PJ': 'Pijatjogja.com', 'SB': 'Sayabantu.com',
        'TV': 'Titikvisual.com', 'TN': 'Tuantanah.com', 'TL': 'Tukanglas.org',
        'AKI': 'Adakamar.id', 'SI': 'Seven Inc',
    };

    // Division labels
    const divLabels = {
        'ADM':'Administrasi','UIUX':'UI/UX Designer','PROG':'Programmer','HR':'Human Resource',
        'SMM':'Social Media Specialist','PV':'Photographer','VID':'Videographer','CW':'Content Writer',
        'MS':'Marketing & Sales','CD':'Content Creative','DM':'Digital Marketing',
        'PR':'Public Relations','TC':'TikTok Creator','CP':'Content Planner',
        'PM':'Project Manager','LAS':'Las','ANIM':'Animasi','EXT':'Eksternal',
    };

    let internsList = [];

    function updateCount() {
        const checked = tableBody.querySelectorAll('input[name="intern_ids[]"]:checked').length;
        summaryCount.textContent = checked + ' pemagang';
        submitBtn.disabled = checked === 0;
        submitBtnText.textContent = checked > 0 ? `Buat ${checked} Sertifikat` : 'Buat Sertifikat';
    }

    function renderTable(interns) {
        tableBody.innerHTML = '';
        internsList = interns;

        if (interns.length === 0) {
            tableWrap.classList.add('hidden');
            noneWrap.classList.remove('hidden');
            selectAllWrap.style.display = 'none !important';
            updateCount();
            return;
        }

        noneWrap.classList.add('hidden');
        tableWrap.classList.remove('hidden');
        selectAllWrap.style.cssText = '';

        interns.forEach((intern, i) => {
            const divLabel = divLabels[intern.division_code] || intern.division_code || '—';
            const period = (intern.start_date && intern.end_date)
                ? `${intern.start_date} s/d ${intern.end_date}`
                : '—';

            const tr = document.createElement('tr');
            tr.className = 'transition hover:bg-[#F4F8F6] cursor-pointer';
            tr.innerHTML = `
                <td class="px-4 py-3">
                    <input type="checkbox" name="intern_ids[]" value="${intern.id}"
                        class="intern-check h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659] cursor-pointer">
                </td>
                <td class="px-4 py-3">
                    <p class="font-semibold text-[13px] text-[#1B3A34]">${intern.fullname}</p>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 border border-blue-200">
                        ${divLabel}
                    </span>
                </td>
                <td class="px-4 py-3 text-[12.5px] text-[#4B5F5A]">${intern.institution_name || '—'}</td>
                <td class="px-4 py-3 text-[12.5px] text-[#4B5F5A]">${period}</td>
            `;

            // Klik row = toggle checkbox
            tr.addEventListener('click', (e) => {
                if (e.target.tagName !== 'INPUT') {
                    const cb = tr.querySelector('.intern-check');
                    cb.checked = !cb.checked;
                    updateCount();
                    syncTableSelectAll();
                }
            });

            tableBody.appendChild(tr);
        });

        tableBody.querySelectorAll('.intern-check').forEach(cb => {
            cb.addEventListener('change', () => { updateCount(); syncTableSelectAll(); });
        });

        // Auto-select semua setelah load
        tableSelectAll.checked = false;
        updateCount();
    }

    function syncTableSelectAll() {
        const all = tableBody.querySelectorAll('.intern-check');
        const checked = tableBody.querySelectorAll('.intern-check:checked');
        tableSelectAll.checked = all.length > 0 && all.length === checked.length;
        tableSelectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }

    // Select all via table header checkbox
    tableSelectAll.addEventListener('change', () => {
        tableBody.querySelectorAll('.intern-check').forEach(cb => {
            cb.checked = tableSelectAll.checked;
        });
        updateCount();
    });

    // Load pemagang saat brand berubah
    brandSel.addEventListener('change', async () => {
        const brand = brandSel.value;

        // Auto-fill company
        if (brand && brandCompanyMap[brand]) {
            document.getElementById('companyInput').value = brandCompanyMap[brand];
        }

        if (!brand) {
            tableWrap.classList.add('hidden');
            noneWrap.classList.add('hidden');
            emptyWrap.classList.remove('hidden');
            loadingWrap.classList.add('hidden');
            selectAllWrap.style.cssText = 'display:none!important';
            updateCount();
            return;
        }

        // Show loading
        emptyWrap.classList.add('hidden');
        noneWrap.classList.add('hidden');
        tableWrap.classList.add('hidden');
        loadingWrap.classList.remove('hidden');

        try {
            const res = await fetch(`${API_URL}?brand=${encodeURIComponent(brand)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const json = await res.json();
            loadingWrap.classList.add('hidden');
            renderTable(json.interns || []);
        } catch(e) {
            loadingWrap.classList.add('hidden');
            noneWrap.classList.remove('hidden');
        }
    });

    // Trigger jika ada old value dari brand
    @if(old('brand'))
    brandSel.dispatchEvent(new Event('change'));
    @endif

    // Preview aset visual
    function bindPreview(selId, imgId, baseDir) {
        const sel = document.getElementById(selId);
        const img = document.getElementById(imgId);
        if (!sel || !img) return;
        sel.addEventListener('change', () => {
            if (!sel.value) { img.src = ''; img.classList.add('hidden'); return; }
            img.src = '/storage/' + baseDir + '/' + sel.value;
            img.classList.remove('hidden');
        });
        // Trigger jika ada old value
        if (sel.value) sel.dispatchEvent(new Event('change'));
    }
    bindPreview('sel_bg',    'prev_bg',    'images/backgrounds');
    bindPreview('sel_logo1', 'prev_logo1', 'images/logos');
    bindPreview('sel_logo2', 'prev_logo2', 'images/logos');
    bindPreview('sel_ttd1',  'prev_ttd1',  'images/signature');
    bindPreview('sel_ttd2',  'prev_ttd2',  'images/signature');

    // Konfirmasi sebelum submit → ganti ke AJAX + modal
    const certForm = document.getElementById('certBulkForm');
    certForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const count = tableBody.querySelectorAll('.intern-check:checked').length;
        if (count === 0) {
            alert('Pilih minimal satu pemagang terlebih dahulu.');
            return;
        }

        if (!confirm(`Buat sertifikat untuk ${count} pemagang? Proses ini tidak dapat dibatalkan.`)) {
            return;
        }

        submitBtn.disabled = true;
        submitBtnText.textContent = 'Sedang membuat sertifikat...';

        const fd = new FormData(certForm);

        try {
            const res = await fetch(certForm.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const contentType = res.headers.get('Content-Type') || '';

            let success = false;
            let message = '';
            let details = [];

            if (contentType.includes('application/json')) {
                const json = await res.json();
                success = json.success ?? res.ok;
                message = json.message || (res.ok ? `${count} sertifikat berhasil dibuat.` : 'Terjadi kesalahan.');
                details = json.details || [];
            } else if (res.ok) {
                success = true;
                message = `${count} sertifikat berhasil dibuat dan tersedia untuk pemagang di halaman Dokumen Saya.`;
            } else {
                message = 'Terjadi kesalahan di server. Coba lagi.';
            }

            showCertModal(success ? 'success' : 'error',
                success ? 'Sertifikat Berhasil Dibuat!' : 'Gagal Membuat Sertifikat',
                message, details);

        } catch (err) {
            showCertModal('error', 'Gagal Membuat Sertifikat', 'Koneksi gagal. Periksa server.', []);
        }

        submitBtn.disabled = false;
        updateCount();
    });

    function showCertModal(type, title, message, details) {
        // Buat modal on-the-fly kalau belum ada
        let modal = document.getElementById('certResultModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'certResultModal';
            modal.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4';
            modal.innerHTML = `
                <div class="w-full max-w-md rounded-[16px] bg-white shadow-2xl p-6">
                    <div id="certModalIcon" class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"></div>
                    <h3 id="certModalTitle" class="mb-2 text-center text-lg font-bold text-[#1B3A34]"></h3>
                    <p id="certModalMessage" class="mb-5 text-center text-[13px] text-[#4B5F5A]"></p>
                    <div id="certModalDetails" class="mb-4 hidden rounded-[10px] bg-[#F4F8F6] p-3 text-[12px] text-[#4B5F5A] space-y-1 max-h-40 overflow-y-auto"></div>
                    <button id="certModalClose" class="w-full rounded-[10px] bg-[#2D8659] py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">Tutup</button>
                </div>`;
            document.body.appendChild(modal);
        }

        const icon    = document.getElementById('certModalIcon');
        const titleEl = document.getElementById('certModalTitle');
        const msgEl   = document.getElementById('certModalMessage');
        const detailEl = document.getElementById('certModalDetails');

        if (type === 'success') {
            icon.className = 'mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100';
            icon.innerHTML = '<svg class="h-7 w-7 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
        } else {
            icon.className = 'mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100';
            icon.innerHTML = '<svg class="h-7 w-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        }

        titleEl.textContent = title;
        msgEl.textContent   = message;

        if (details && details.length > 0) {
            detailEl.classList.remove('hidden');
            detailEl.innerHTML = details.map(d => `<p>• ${d}</p>`).join('');
        } else {
            detailEl.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('certModalClose').onclick = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (type === 'success') {
                window.location.href = "{{ route('admin.certificate.index') }}";
            }
        };
    }
});
</script>
@endpush

@endsection
