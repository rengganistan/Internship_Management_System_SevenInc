@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6]">

    {{-- ===== LAYOUT: kiri + kanan ===== --}}
    <div class="flex h-[calc(100vh-64px)] overflow-hidden">

        {{-- ===== KIRI: FORM ===== --}}
        <div class="w-full max-w-[420px] shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-white">
            <div class="p-5 space-y-5">

                {{-- Header --}}
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Template & Pengaturan</p>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Template LOA</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Letter of Acceptance — Surat Penerimaan Magang</p>
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

                {{-- ===== FORM GENERATE LOA ===== --}}
                <form action="{{ route('admin.loa.generate_brand') }}" method="POST" enctype="multipart/form-data" id="loaGenerateForm" class="space-y-4">
                    @csrf

                    {{-- 1. Pilih Brand --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                        <p class="mb-3 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">1. Pilih Brand</p>

                        <div>
                            <label class="mb-1.5 block text-[12px] font-semibold text-[#1B3A34]">Brand Pemagang</label>
                            <select id="brandSelect" name="brand"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                <option value="">-- Pilih Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                @endforeach
                            </select>
                            @if($brands->isEmpty())
                                <p class="mt-1.5 text-[11.5px] text-amber-600">Tidak ada pemagang berstatus <em>diterima</em> yang belum mendapat LOA.</p>
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

                        <p id="noInternsMsg" class="hidden text-[12px] text-amber-600 mt-2">Semua pemagang dari brand ini sudah mendapat LOA.</p>
                    </div>

                    {{-- 3. Pengaturan Logo & Tanda Tangan --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                        <p class="mb-3 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">3. Pengaturan Surat</p>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Logo Brand</label>
                                    <input type="file" name="logo_upload" id="logoUpload" accept="image/*"
                                        class="block w-full text-[11.5px] text-[#4B5F5A] file:mr-2 file:rounded file:border-0 file:bg-[#E8F5E9] file:px-2 file:py-1 file:text-[11px] file:font-semibold file:text-[#1F5F3F]">
                                    @if(!empty($loaSettings?->logo_path))
                                        <p class="mt-0.5 text-[10.5px] text-[#4B5F5A]">Logo tersimpan: <em>{{ basename($loaSettings->logo_path) }}</em></p>
                                    @endif
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Tanda Tangan</label>
                                    <input type="file" name="stamp_upload" id="stampUpload" accept="image/*"
                                        class="block w-full text-[11.5px] text-[#4B5F5A] file:mr-2 file:rounded file:border-0 file:bg-[#E8F5E9] file:px-2 file:py-1 file:text-[11px] file:font-semibold file:text-[#1F5F3F]">
                                    @if(!empty($loaSettings?->stamp_path))
                                        <p class="mt-0.5 text-[10.5px] text-[#4B5F5A]">TTD tersimpan: <em>{{ basename($loaSettings->stamp_path) }}</em></p>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Penandatangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="signatory_name" required
                                        value="{{ old('signatory_name', $loaSettings->signatory_name ?? '') }}"
                                        placeholder="Contoh: Ari Setia Husbana"
                                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan Penandatangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="signatory_position" required
                                        value="{{ old('signatory_position', $loaSettings->signatory_position ?? '') }}"
                                        placeholder="Contoh: HRD"
                                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Generate Button --}}
                    <button type="submit" id="btnGenerate"
                        disabled
                        class="w-full flex items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span id="btnGenerateLabel">Generate LOA</span>
                    </button>

                    <p class="text-[11px] text-center text-[#4B5F5A]">1 pemagang → PDF langsung. Lebih dari 1 → dikemas ZIP (1 PDF per pemagang).</p>

                </form>

                {{-- Divider --}}
                <div class="border-t border-[#DCE7E1] pt-4">
                    <p class="mb-2 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Simpan Pengaturan Default</p>
                    <form action="{{ route('admin.loa.update') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Logo Default</label>
                                <input type="file" name="logo_path" accept="image/*" class="block w-full text-[11.5px] text-[#4B5F5A]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">TTD Default</label>
                                <input type="file" name="stamp_path" accept="image/*" class="block w-full text-[11.5px] text-[#4B5F5A]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Penandatangan</label>
                                <input type="text" name="signatory_name"
                                    value="{{ old('signatory_name', $loaSettings->signatory_name ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan</label>
                                <input type="text" name="signatory_position"
                                    value="{{ old('signatory_position', $loaSettings->signatory_position ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div class="col-span-2">
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Email Kontak</label>
                                <input type="text" name="company_contact_email"
                                    value="{{ old('company_contact_email', $loaSettings->company_contact_email ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="flex items-center gap-2 rounded-[9px] border border-[#2D8659] bg-white px-4 py-2 text-[12.5px] font-semibold text-[#1F5F3F] transition hover:bg-[#E8F5E9]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                                Simpan Default
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- ===== KANAN: PREVIEW ===== --}}
        <div class="flex-1 flex flex-col bg-[#F4F8F6]">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-[#2D8659]"></span>
                    <span class="text-[13px] font-semibold text-[#1B3A34]">Preview LOA</span>
                    <span class="text-[11px] text-[#4B5F5A]">— berubah saat pilih pemagang</span>
                </div>
                <button id="btnRefreshPreview"
                    class="flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Refresh
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <iframe id="loaPreview"
                    src="{{ route('user.loa.preview') }}"
                    class="h-full w-full border-0">
                </iframe>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    const $brandSelect  = document.getElementById('brandSelect');
    const $internsSection = document.getElementById('internsSection');
    const $internsList  = document.getElementById('internsList');
    const $noInternsMsg = document.getElementById('noInternsMsg');
    const $checkAll     = document.getElementById('checkAll');
    const $btnGenerate  = document.getElementById('btnGenerate');
    const $btnLabel     = document.getElementById('btnGenerateLabel');
    const $iframe       = document.getElementById('loaPreview');
    const $btnRefresh   = document.getElementById('btnRefreshPreview');

    const PREVIEW_URL   = "{{ route('user.loa.preview') }}";
    const API_URL       = "{{ route('admin.loa.interns_by_brand') }}";

    const fmtID = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    function formatDate(iso) {
        if (!iso) return null;
        const d = new Date(iso);
        return isNaN(d.getTime()) ? null : fmtID.format(d);
    }

    // ── Fetch pemagang by brand ──────────────────────────────────────────────
    $brandSelect?.addEventListener('change', async () => {
        const brand = $brandSelect.value;
        $internsList.innerHTML = '';
        $noInternsMsg.classList.add('hidden');
        $internsSection.classList.add('hidden');
        updateGenerateBtn();

        if (!brand) return;

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

            data.interns.forEach(intern => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 rounded-[8px] bg-[#F4F8F6] px-3 py-2.5';

                const start = formatDate(intern.start_date);
                const end   = formatDate(intern.end_date);
                const periode = (start && end) ? `${start} – ${end}` : '-';

                div.innerHTML = `
                    <input type="checkbox" name="intern_ids[]" value="${intern.id}"
                        class="intern-checkbox rounded border-[#DCE7E1] text-[#2D8659] shrink-0"
                        data-fullname="${intern.fullname}"
                        data-student-id="${intern.student_id}"
                        data-study-program="${intern.study_program}"
                        data-institution-name="${intern.institution_name}"
                        data-start-date="${intern.start_date}"
                        data-end-date="${intern.end_date}"
                        data-phone-number="${intern.phone_number}">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-[#1B3A34] truncate">${intern.fullname}</p>
                        <p class="text-[11px] text-[#4B5F5A] truncate">${intern.institution_name} · ${intern.study_program || '-'}</p>
                        <p class="text-[10.5px] text-[#4B5F5A]">${periode}</p>
                    </div>`;

                $internsList.appendChild(div);
            });

            // Pasang listener di setiap checkbox
            document.querySelectorAll('.intern-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    updateCheckAll();
                    updateGenerateBtn();
                    pushPreview();
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
        pushPreview();
    });

    function updateCheckAll() {
        const all = document.querySelectorAll('.intern-checkbox');
        $checkAll.checked = all.length > 0 && Array.from(all).every(cb => cb.checked);
    }

    // ── Tombol Generate ──────────────────────────────────────────────────────
    function updateGenerateBtn() {
        const checked = document.querySelectorAll('.intern-checkbox:checked').length;
        $btnGenerate.disabled = checked === 0;
        if (checked === 0) {
            $btnLabel.textContent = 'Generate LOA';
        } else if (checked === 1) {
            $btnLabel.textContent = 'Generate LOA (1 pemagang)';
        } else {
            $btnLabel.textContent = `Generate LOA (${checked} pemagang — ZIP)`;
        }
    }

    // ── Preview iframe ───────────────────────────────────────────────────────
    function collectRows() {
        const rows = [];
        document.querySelectorAll('.intern-checkbox:checked').forEach(cb => {
            const start = formatDate(cb.dataset.startDate);
            const end   = formatDate(cb.dataset.endDate);
            rows.push({
                nama_siswa : cb.dataset.fullname || '-',
                nim_nis    : cb.dataset.studentId || '-',
                jurusan    : cb.dataset.studyProgram || '-',
                instansi   : cb.dataset.institutionName || '-',
                periode    : (start && end) ? `${start} - ${end}` : '-',
                kontak     : cb.dataset.phoneNumber || '-',
            });
        });
        return rows;
    }

    function pushPreview() {
        if (!$iframe?.contentWindow) return;
        $iframe.contentWindow.postMessage(
            { type: 'updateLOA', rows: collectRows() },
            window.location.origin
        );
    }

    $btnRefresh?.addEventListener('click', () => {
        $iframe.src = PREVIEW_URL + '?t=' + Date.now();
    });

    $iframe?.addEventListener('load', pushPreview);

    // Reload preview setelah simpan pengaturan
    @if(session()->has('success'))
        if ($iframe) $iframe.src = PREVIEW_URL + '?t=' + Date.now();
    @endif
})();
</script>
@endsection
