@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6]">

    {{-- ===== LAYOUT: kiri + kanan ===== --}}
    <div class="flex h-[calc(100vh-64px)] overflow-hidden">

        {{-- ===== KIRI: FORM ===== --}}
        <div class="w-full max-w-md shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-white">
            <div class="p-5 space-y-5">

                {{-- Header --}}
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Template & Pengaturan</p>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Template LOA</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Letter of Acceptance — Surat Penerimaan Magang</p>
                </div>

                @if(session('success'))
                <div class="flex items-center gap-2 rounded-[9px] border border-[#A5D6A7] bg-[#E8F5E9] px-3 py-2.5 text-[13px] font-semibold text-[#1F5F3F]">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-2 rounded-[9px] border border-red-200 bg-red-50 px-3 py-2.5 text-[13px] font-semibold text-[#D32F2F]">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                {{-- Pengaturan Template --}}
                <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                    <p class="mb-3 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Pengaturan Template</p>
                    <form action="{{ route('admin.loa.update') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf @method('PUT')

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                                <input type="text" name="company_name"
                                    value="{{ old('company_name', $loaSettings->company_name ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Email Kontak</label>
                                <input type="text" name="company_contact_email"
                                    value="{{ old('company_contact_email', $loaSettings->company_contact_email ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Penandatangan</label>
                                <input type="text" name="signatory_name"
                                    value="{{ old('signatory_name', $loaSettings->signatory_name ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan Penandatangan</label>
                                <input type="text" name="signatory_position"
                                    value="{{ old('signatory_position', $loaSettings->signatory_position ?? '') }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kop Surat</label>
                            <input type="text" name="header_text"
                                value="{{ old('header_text', $loaSettings->header_text ?? '') }}"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Footer</label>
                            <input type="text" name="footer_text"
                                value="{{ old('footer_text', $loaSettings->footer_text ?? '') }}"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Logo</label>
                                <input type="file" name="logo_path" accept="image/*"
                                    class="block w-full text-[12px] text-[#4B5F5A]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Tanda Tangan/Stempel</label>
                                <input type="file" name="stamp_path" accept="image/*"
                                    class="block w-full text-[12px] text-[#4B5F5A]">
                            </div>
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit"
                                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Generate Single --}}
                <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                    <p class="mb-3 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Generate LOA — Satu Pemagang</p>
                    <form action="{{ route('admin.loa.generate') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Pilih Pemagang</label>
                            <select name="intern_id" id="intern_id"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                <option value="">-- Pilih --</option>
                                @foreach($registrations as $r)
                                <option value="{{ $r->id }}"
                                    data-fullname="{{ $r->fullname }}"
                                    data-student-id="{{ $r->student_id }}"
                                    data-study-program="{{ $r->study_program }}"
                                    data-institution-name="{{ $r->institution_name }}"
                                    data-start-date="{{ $r->start_date }}"
                                    data-end-date="{{ $r->end_date }}"
                                    data-phone-number="{{ $r->phone_number }}"
                                    data-status="{{ $r->internship_status }}">
                                    {{ $r->fullname }} — {{ $r->institution_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Generate PDF
                        </button>
                    </form>
                </div>

                {{-- Generate Batch --}}
                <div class="rounded-[10px] border border-[#DCE7E1] p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Generate LOA — Multiple Pemagang</p>
                        <label class="flex items-center gap-1.5 text-[12px] text-[#4B5F5A] cursor-pointer">
                            <input type="checkbox" id="selectAllInterns" class="rounded">
                            Pilih Semua
                        </label>
                    </div>
                    <form action="{{ route('admin.loa.generateBatch') }}" method="POST" class="space-y-3">
                        @csrf
                        <p class="text-[11px] text-[#4B5F5A]">Tahan <kbd class="rounded bg-[#F4F8F6] border border-[#DCE7E1] px-1">Ctrl</kbd> untuk pilih lebih dari satu.</p>
                        <select name="intern_ids[]" id="intern_ids" multiple size="8"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            @foreach($registrations as $r)
                            <option value="{{ $r->id }}"
                                data-fullname="{{ $r->fullname }}"
                                data-student-id="{{ $r->student_id }}"
                                data-study-program="{{ $r->study_program }}"
                                data-institution-name="{{ $r->institution_name }}"
                                data-start-date="{{ $r->start_date }}"
                                data-end-date="{{ $r->end_date }}"
                                data-phone-number="{{ $r->phone_number }}"
                                data-status="{{ $r->internship_status }}">
                                {{ $r->fullname }} — {{ $r->institution_name }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 rounded-[9px] border border-[#2D8659] bg-white px-4 py-2.5 text-[13px] font-semibold text-[#1F5F3F] transition hover:bg-[#E8F5E9]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Generate Batch (ZIP)
                        </button>
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

@php $justSaved = session()->has('success'); @endphp

<script>
(function(){
    const $single     = document.getElementById('intern_id');
    const $multi      = document.getElementById('intern_ids');
    const $iframe     = document.getElementById('loaPreview');
    const $btnRefresh = document.getElementById('btnRefreshPreview');
    const $selectAll  = document.getElementById('selectAllInterns');

    const JUST_SAVED  = {{ $justSaved ? 'true' : 'false' }};
    if (JUST_SAVED && $iframe) {
        $iframe.src = "{{ route('user.loa.preview') }}" + '?t=' + Date.now();
    }

    const fmtID = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    function formatDate(iso) {
        if (!iso) return null;
        const d = new Date(iso);
        return isNaN(d.getTime()) ? null : fmtID.format(d);
    }

    function optionToRow(opt) {
        const start = formatDate(opt.dataset.startDate);
        const end   = formatDate(opt.dataset.endDate);
        return {
            nama_siswa: opt.dataset.fullname || '-',
            nim_nis:    opt.dataset.studentId || '-',
            jurusan:    opt.dataset.studyProgram || '-',
            instansi:   opt.dataset.institutionName || '-',
            periode:    (start && end) ? `${start} - ${end}` : '-',
            kontak:     opt.dataset.phoneNumber || '-',
        };
    }

    function collectRows() {
        const rows = [];
        if ($multi?.selectedOptions?.length > 0) {
            Array.from($multi.selectedOptions).forEach(opt => rows.push(optionToRow(opt)));
        } else if ($single?.value) {
            const opt = $single.options[$single.selectedIndex];
            if (opt?.value) rows.push(optionToRow(opt));
        }
        return rows;
    }

    function postRowsToIframe() {
        if (!$iframe?.contentWindow) return;
        $iframe.contentWindow.postMessage(
            { type: 'updateLOA', rows: collectRows() },
            window.location.origin
        );
    }

    // Select all
    $selectAll?.addEventListener('change', () => {
        Array.from($multi?.options || []).forEach(o => o.selected = $selectAll.checked);
        postRowsToIframe();
    });
    $multi?.addEventListener('change', () => {
        $selectAll.checked = Array.from($multi.options).every(o => o.selected);
        postRowsToIframe();
    });

    $single?.addEventListener('change', postRowsToIframe);
    $btnRefresh?.addEventListener('click', () => {
        $iframe.src = "{{ route('user.loa.preview') }}" + '?t=' + Date.now();
    });
    $iframe?.addEventListener('load', postRowsToIframe);
    window.addEventListener('focus', postRowsToIframe);
    document.addEventListener('DOMContentLoaded', postRowsToIframe);
})();
</script>

@endsection
