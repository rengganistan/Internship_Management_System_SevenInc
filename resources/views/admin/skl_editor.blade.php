@extends('layouts.dashboard')

@section('title', 'Template SKL')

@section('content')
<div class="min-h-screen bg-[#F4F8F6]">

    {{-- ===== LAYOUT: kiri (form) + kanan (preview) ===== --}}
    <div class="flex h-[calc(100vh-64px)] overflow-hidden">

        {{-- ===== KIRI: FORM ===== --}}
        <div class="w-full max-w-[420px] shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-white">
            <div class="p-5 space-y-5">

                {{-- Header --}}
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Template & Pengaturan</p>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Template SKL</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Surat Keterangan Selesai Magang</p>
                </div>

                @if(session('success'))
                <div class="flex items-start gap-2 rounded-[9px] border border-[#A5D6A7] bg-[#E8F5E9] px-3 py-2.5 text-[13px] font-semibold text-[#1F5F3F]">
                    <svg class="h-4 w-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{!! session('success') !!}</span>
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-2 rounded-[9px] border border-red-200 bg-red-50 px-3 py-2.5 text-[13px] font-semibold text-[#D32F2F]">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                {{-- ===== FORM GENERATE SKL ===== --}}
                <form action="{{ route('admin.skl.generate_brand') }}" method="POST" enctype="multipart/form-data" id="sklGenerateForm" class="space-y-4">
                    @csrf

                    {{-- 1. Pilih Brand --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                        <p class="mb-3 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">1. Pilih Brand</p>
                        <div>
                            <label class="mb-1.5 block text-[12px] font-semibold text-[#1B3A34]">Nama Perusahaan / Brand</label>
                            <select id="brandSelect" name="brand"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                <option value="">-- Pilih Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                @endforeach
                            </select>
                            @if($brands->isEmpty())
                                <p class="mt-1.5 text-[11.5px] text-amber-600">Tidak ada pemagang berstatus <em>Selesai</em> yang belum mendapat SKL.</p>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Daftar Pemagang (muncul setelah brand dipilih) --}}
                    <div id="internsSection" class="rounded-[10px] border border-[#DCE7E1] p-4 hidden">
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">2. Pilih Pemagang</p>
                            <label class="flex items-center gap-1.5 cursor-pointer text-[12px] text-[#4B5F5A]">
                                <input type="checkbox" id="checkAll" class="rounded border-[#DCE7E1] text-[#2D8659]">
                                <span>Pilih Semua</span>
                            </label>
                        </div>
                        <div id="internsList" class="space-y-2 max-h-52 overflow-y-auto pr-1">
                            {{-- Diisi via JS --}}
                        </div>
                        <p id="noInternsMsg" class="hidden text-[12px] text-amber-600 mt-2">Semua pemagang dari brand ini sudah mendapat SKL.</p>
                    </div>

                    {{-- 3. Pengaturan Isi Surat, Aset Visual & Data Perusahaan --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-4">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">3. Pengaturan Surat</p>

                        {{-- Informasi Perusahaan --}}
                        <div class="space-y-3">
                            <p class="text-[11px] font-semibold text-[#4B5F5A] uppercase tracking-wide">Informasi Perusahaan</p>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                                <input type="text" name="company_name" id="companyName"
                                    value="{{ old('company_name', $config->company_name) }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"
                                    placeholder="Nama perusahaan / brand">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
                                <textarea name="company_address" id="companyAddress" rows="2"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', $config->company_address) }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kota</label>
                                    <input type="text" name="company_city" id="companyCity"
                                        value="{{ old('company_city', $config->company_city) }}"
                                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Pimpinan</label>
                                    <input type="text" name="leader_name" id="leaderName"
                                        value="{{ old('leader_name', $config->leader_name) }}"
                                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan Pimpinan</label>
                                <input type="text" name="leader_title" id="leaderTitle"
                                    value="{{ old('leader_title', $config->leader_title) }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                        </div>

                        {{-- Isi Surat --}}
                        <div class="space-y-3 border-t border-[#DCE7E1] pt-3">
                            <p class="text-[11px] font-semibold text-[#4B5F5A] uppercase tracking-wide">Isi Surat</p>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Deskripsi Kegiatan</label>
                                <textarea name="activity_description" id="activityDesc" rows="5"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('activity_description', $config->activity_description ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Pencapaian Peserta</label>
                                <textarea name="participant_achievement" id="achievementDesc" rows="5"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('participant_achievement', $config->participant_achievement ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- Aset Visual --}}
                        <div class="space-y-3 border-t border-[#DCE7E1] pt-3">
                            <p class="text-[11px] font-semibold text-[#4B5F5A] uppercase tracking-wide">Aset Visual</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Logo</label>
                                    <input type="file" name="logo" id="logoUpload" accept="image/*"
                                        class="block w-full text-[11.5px] text-[#4B5F5A] file:mr-2 file:rounded file:border-0 file:bg-[#E8F5E9] file:px-2 file:py-1 file:text-[11px] file:font-semibold file:text-[#1F5F3F]">
                                    @if(!empty($config->logo_path))
                                        <p class="mt-0.5 text-[10.5px] text-[#4B5F5A]">Tersimpan: <em>{{ basename($config->logo_path) }}</em></p>
                                    @endif
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Stempel / TTD</label>
                                    <input type="file" name="stamp" id="stampUpload" accept="image/*"
                                        class="block w-full text-[11.5px] text-[#4B5F5A] file:mr-2 file:rounded file:border-0 file:bg-[#E8F5E9] file:px-2 file:py-1 file:text-[11px] file:font-semibold file:text-[#1F5F3F]">
                                    @if(!empty($config->stamp_path))
                                        <p class="mt-0.5 text-[10.5px] text-[#4B5F5A]">Tersimpan: <em>{{ basename($config->stamp_path) }}</em></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Simpan Perubahan --}}
                        <div class="border-t border-[#DCE7E1] pt-3 flex justify-end">
                            <button type="button" id="btnSaveSettings"
                                class="flex items-center gap-2 rounded-[9px] border border-[#2D8659] bg-white px-4 py-2 text-[12.5px] font-semibold text-[#1F5F3F] transition hover:bg-[#E8F5E9]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    {{-- Generate Button --}}
                    <button type="submit" id="btnGenerate"
                        disabled
                        class="w-full flex items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span id="btnGenerateLabel">Generate SKL</span>
                    </button>

                    <p class="text-[11px] text-center text-[#4B5F5A]">Simpan perubahan terlebih dahulu sebelum generate. 1 pemagang → PDF langsung. Lebih dari 1 → dikemas ZIP.</p>

                </form>

            </div>
        </div>

        {{-- ===== KANAN: PREVIEW ===== --}}
        <div class="flex-1 flex flex-col bg-[#F4F8F6]">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#2D8659]"></span>
                    <span class="text-[13px] font-semibold text-[#1B3A34]">Preview SKL</span>
                    <span class="text-[11px] text-[#4B5F5A]">— diperbarui otomatis saat mengetik</span>
                </div>
                <button id="btnRefreshPreview"
                    class="flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Refresh
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <iframe id="sklPreview"
                    src="{{ route('admin.skl.preview') }}"
                    class="h-full w-full border-0">
                </iframe>
            </div>
        </div>
    </div>
</div>

{{-- Modal hasil generate SKL --}}
<div id="sklResultModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-[16px] bg-white shadow-2xl p-6">
        <div id="modalIcon" class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"></div>
        <h3 id="modalTitle" class="mb-2 text-center text-lg font-bold text-[#1B3A34]"></h3>
        <p id="modalMessage" class="mb-5 text-center text-[13px] text-[#4B5F5A]"></p>
        <div id="modalDetails" class="mb-4 hidden rounded-[10px] bg-[#F4F8F6] p-3 text-[12px] text-[#4B5F5A] space-y-1"></div>
        <button id="modalClose"
            class="w-full rounded-[10px] bg-[#2D8659] py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
            Tutup
        </button>
    </div>
</div>

{{-- Hidden form untuk simpan pengaturan (disubmit via JS) --}}
<form id="saveSettingsForm" action="{{ route('admin.skl.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
    @csrf
</form>

<script>
(function () {
    const $brandSelect    = document.getElementById('brandSelect');
    const $internsSection = document.getElementById('internsSection');
    const $internsList    = document.getElementById('internsList');
    const $noInternsMsg   = document.getElementById('noInternsMsg');
    const $checkAll       = document.getElementById('checkAll');
    const $btnGenerate    = document.getElementById('btnGenerate');
    const $btnLabel       = document.getElementById('btnGenerateLabel');
    const $iframe         = document.getElementById('sklPreview');
    const $btnRefresh     = document.getElementById('btnRefreshPreview');
    const $btnSave        = document.getElementById('btnSaveSettings');
    const $generateForm   = document.getElementById('sklGenerateForm');
    const $saveForm       = document.getElementById('saveSettingsForm');

    const PREVIEW_URL = "{{ route('admin.skl.preview') }}";
    const API_URL     = "{{ route('admin.skl.interns_by_brand') }}";

    // Apakah sudah simpan perubahan (track supaya generate hanya bisa setelah save)
    let settingsSaved = @json(session()->has('success') ? 'true' : 'false');

    // ── Fetch pemagang by brand ──────────────────────────────────────────────
    $brandSelect?.addEventListener('change', async () => {
        const brand = $brandSelect.value;
        $internsList.innerHTML = '';
        $noInternsMsg.classList.add('hidden');
        $internsSection.classList.add('hidden');
        updateGenerateBtn();

        if (!brand) return;

        // Auto-fill nama perusahaan berdasarkan brand yang dipilih
        const brandToCompany = {
            'Magangjogja': 'Magangjogja.com',
            'Magangjogja.com': 'Magangjogja.com',
            'Areakerja': 'Areakerja.com',
            'Areakerja.com': 'Areakerja.com',
            'Titikvisual': 'Titikvisual',
            'Titikvisual.com': 'Titikvisual',
            'Seven Inc': 'Seven Inc',
            'Seveninc': 'Seven Inc',
        };
        const companyNameEl = document.getElementById('companyName');
        if (companyNameEl && brandToCompany[brand]) {
            companyNameEl.value = brandToCompany[brand];
        } else if (companyNameEl) {
            companyNameEl.value = brand; // fallback: isi dengan nama brand itu sendiri
        }

        $internsList.innerHTML = '<p class="text-[12px] text-[#4B5F5A] animate-pulse">Memuat data...</p>';
        $internsSection.classList.remove('hidden');

        try {
            const res  = await fetch(`${API_URL}?brand=${encodeURIComponent(brand)}`);
            const data = await res.json();

            $internsList.innerHTML = '';

            if (!data.interns || data.interns.length === 0) {
                $noInternsMsg.classList.remove('hidden');
                updateGenerateBtn();
                return;
            }

            const fmtID = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            function formatDate(iso) {
                if (!iso) return null;
                const d = new Date(iso);
                return isNaN(d.getTime()) ? null : fmtID.format(d);
            }

            data.interns.forEach(intern => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 rounded-[8px] bg-[#F4F8F6] px-3 py-2.5';

                const start  = formatDate(intern.start_date);
                const end    = formatDate(intern.end_date);
                const periode = (start && end) ? `${start} – ${end}` : '-';

                div.innerHTML = `
                    <input type="checkbox" name="intern_ids[]" value="${intern.id}"
                        class="intern-checkbox rounded border-[#DCE7E1] text-[#2D8659] shrink-0"
                        data-fullname="${intern.fullname}"
                        data-institution="${intern.institution_name}"
                        data-study-program="${intern.study_program}"
                        data-start="${intern.start_date}"
                        data-end="${intern.end_date}">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-[#1B3A34] truncate">${intern.fullname}</p>
                        <p class="text-[11px] text-[#4B5F5A] truncate">${intern.institution_name} · ${intern.study_program || '-'}</p>
                        <p class="text-[10.5px] text-[#4B5F5A]">${periode}</p>
                    </div>`;

                $internsList.appendChild(div);
            });

            document.querySelectorAll('.intern-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    updateCheckAll();
                    updateGenerateBtn();
                });
            });

        } catch (err) {
            $internsList.innerHTML = '<p class="text-[12px] text-red-500">Gagal memuat data. Coba refresh.</p>';
        }

        updateGenerateBtn();
    });

    // ── Pilih Semua ──────────────────────────────────────────────────────────
    $checkAll?.addEventListener('change', () => {
        document.querySelectorAll('.intern-checkbox').forEach(cb => cb.checked = $checkAll.checked);
        updateGenerateBtn();
    });

    function updateCheckAll() {
        const all = document.querySelectorAll('.intern-checkbox');
        $checkAll.checked = all.length > 0 && Array.from(all).every(cb => cb.checked);
    }

    // ── Tombol Generate ──────────────────────────────────────────────────────
    function updateGenerateBtn() {
        const checked = document.querySelectorAll('.intern-checkbox:checked').length;
        // Generate hanya aktif jika sudah simpan DAN ada pemagang dipilih
        $btnGenerate.disabled = checked === 0 || !settingsSaved;
        if (checked === 0) {
            $btnLabel.textContent = 'Generate SKL';
        } else if (!settingsSaved) {
            $btnLabel.textContent = 'Simpan Perubahan dulu untuk Generate';
        } else if (checked === 1) {
            $btnLabel.textContent = 'Generate SKL (1 pemagang)';
        } else {
            $btnLabel.textContent = `Generate SKL (${checked} pemagang — ZIP)`;
        }
    }

    // ── Simpan Perubahan ─────────────────────────────────────────────────────
    $btnSave?.addEventListener('click', () => {
        // Pindahkan semua input dari generate form ke save form (kecuali intern_ids)
        // Buat FormData manual & submit
        const form = $saveForm;

        // Hapus input lama dari save form (selain csrf)
        const toRemove = Array.from(form.querySelectorAll('input:not([name="_token"]), textarea'));
        toRemove.forEach(el => el.remove());

        // Copy input dari generate form
        const fieldsToSave = ['company_name', 'company_address', 'company_city', 'leader_name', 'leader_title', 'activity_description', 'participant_achievement'];
        fieldsToSave.forEach(name => {
            const src = $generateForm.querySelector(`[name="${name}"]`);
            if (!src) return;
            const el = src.tagName === 'TEXTAREA'
                ? Object.assign(document.createElement('textarea'), { name: src.name, textContent: src.value })
                : Object.assign(document.createElement('input'), { type: 'hidden', name: src.name, value: src.value });
            form.appendChild(el);
        });

        // File inputs harus disubmit via form biasa — pakai FormData
        const fd = new FormData(form);

        // Logo & stamp dari generate form
        const logoFile  = document.getElementById('logoUpload');
        const stampFile = document.getElementById('stampUpload');
        if (logoFile?.files[0])  fd.set('logo', logoFile.files[0], logoFile.files[0].name);
        if (stampFile?.files[0]) fd.set('stamp', stampFile.files[0], stampFile.files[0].name);

        // Tambahkan fields teks langsung ke fd
        fieldsToSave.forEach(name => {
            const src = $generateForm.querySelector(`[name="${name}"]`);
            if (src) fd.set(name, src.value);
        });

        $btnSave.disabled = true;
        $btnSave.textContent = 'Menyimpan...';

        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then(res => res.json()).then(json => {
            if (json.success) {
                settingsSaved = true;
                $btnSave.textContent = '✅ Tersimpan!';
                $btnSave.disabled = false;
                updateGenerateBtn();
                // Refresh preview dengan data baru
                refreshPreview();
                setTimeout(() => {
                    $btnSave.innerHTML = `<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg> Simpan Perubahan`;
                }, 2000);
            } else {
                $btnSave.textContent = 'Gagal — coba lagi';
                $btnSave.disabled = false;
                setTimeout(() => {
                    $btnSave.innerHTML = `<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg> Simpan Perubahan`;
                }, 2500);
            }
        }).catch(() => {
            $btnSave.textContent = 'Gagal — coba lagi';
            $btnSave.disabled = false;
        });
    });

    // Reset saved state saat ada perubahan input
    const watchedInputs = $generateForm.querySelectorAll('input:not([name^="intern"]):not([type="checkbox"]), textarea');
    watchedInputs.forEach(el => {
        el.addEventListener('input', () => {
            settingsSaved = false;
            updateGenerateBtn();
        });
    });
    document.getElementById('logoUpload')?.addEventListener('change', () => { settingsSaved = false; updateGenerateBtn(); });
    document.getElementById('stampUpload')?.addEventListener('change', () => { settingsSaved = false; updateGenerateBtn(); });

    // ── Preview refresh ───────────────────────────────────────────────────────
    function refreshPreview() {
        const params = new URLSearchParams();
        const actDesc  = document.getElementById('activityDesc');
        const achDesc  = document.getElementById('achievementDesc');
        const cName    = document.getElementById('companyName');
        const cAddress = document.getElementById('companyAddress');
        const cCity    = document.getElementById('companyCity');
        const lName    = document.getElementById('leaderName');
        const lTitle   = document.getElementById('leaderTitle');

        if (actDesc)  params.append('activity_description',    actDesc.value);
        if (achDesc)  params.append('participant_achievement', achDesc.value);
        if (cName)    params.append('company_name',            cName.value);
        if (cAddress) params.append('company_address',         cAddress.value);
        if (cCity)    params.append('company_city',            cCity.value);
        if (lName)    params.append('leader_name',             lName.value);
        if (lTitle)   params.append('leader_title',            lTitle.value);

        $iframe.src = PREVIEW_URL + '?' + params.toString();
    }

    let delay;
    const liveFields = ['activityDesc', 'achievementDesc', 'companyName', 'companyAddress', 'companyCity', 'leaderName', 'leaderTitle'];
    liveFields.forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            clearTimeout(delay);
            delay = setTimeout(refreshPreview, 400);
        });
    });

    $btnRefresh?.addEventListener('click', refreshPreview);

    // Jika sudah save via redirect, anggap settingsSaved = true
    @if(session()->has('success'))
        settingsSaved = true;
        updateGenerateBtn();
        if ($iframe) $iframe.src = PREVIEW_URL + '?t=' + Date.now();
    @endif

    // === AJAX Generate SKL (ganti submit biasa → modal hasil) ===
    $generateForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const checkedInterns = document.querySelectorAll('.intern-checkbox:checked');
        if (checkedInterns.length === 0) return;

        $btnGenerate.disabled = true;
        $btnLabel.textContent = 'Sedang generate...';

        const fd = new FormData($generateForm);

        // Logo & stamp dari input file
        const logoFile  = document.getElementById('logoUpload');
        const stampFile = document.getElementById('stampUpload');
        if (logoFile?.files[0])  fd.set('logo', logoFile.files[0], logoFile.files[0].name);
        if (stampFile?.files[0]) fd.set('stamp', stampFile.files[0], stampFile.files[0].name);

        try {
            const res = await fetch($generateForm.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            // Cek apakah respons adalah JSON atau file download
            const contentType = res.headers.get('Content-Type') || '';

            if (contentType.includes('application/json')) {
                const json = await res.json();
                if (json.success) {
                    showModal('success', 'SKL Berhasil Dibuat!', json.message || 'SKL telah dikirim ke pemagang.', json.details || null);
                } else {
                    showModal('error', 'Gagal Generate SKL', json.message || 'Terjadi kesalahan.', null);
                }
            } else if (res.ok) {
                // Respons adalah file (PDF/ZIP) — berarti berhasil, tapi masih download
                // Tampilkan modal sukses tanpa download
                const count = checkedInterns.length;
                showModal('success', 'SKL Berhasil Dibuat!',
                    `${count} SKL telah berhasil dibuat dan tersedia untuk pemagang.`, null);
            } else {
                showModal('error', 'Gagal Generate SKL', 'Server error. Coba lagi.', null);
            }
        } catch (err) {
            showModal('error', 'Gagal Generate SKL', 'Koneksi gagal. Periksa server.', null);
        }

        $btnGenerate.disabled = false;
        updateGenerateBtn();
    });

    function showModal(type, title, message, details) {
        const modal   = document.getElementById('sklResultModal');
        const icon    = document.getElementById('modalIcon');
        const titleEl = document.getElementById('modalTitle');
        const msgEl   = document.getElementById('modalMessage');
        const detailEl = document.getElementById('modalDetails');

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
            detailEl.innerHTML = '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('modalClose').onclick = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reload brand list setelah tutup modal sukses
            if (type === 'success') {
                window.location.reload();
            }
        };
    }
})();
</script>
@endsection
