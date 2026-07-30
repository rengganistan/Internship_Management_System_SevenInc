@extends('layouts.dashboard')

@section('title', 'Template SKL')

@section('content')
<div class="min-h-screen bg-[#F4F8F6]">

    {{-- ===== LAYOUT: kiri (form) + kanan (preview) ===== --}}
    <div class="flex h-[calc(100vh-64px)] overflow-hidden">

        {{-- ===== KIRI: FORM ===== --}}
        <div class="w-full max-w-md shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-white">
            <div class="p-5">

                {{-- Header --}}
                <div class="mb-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Template & Pengaturan</p>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Template SKL</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Surat Keterangan Selesai Magang</p>
                </div>

                @if(session('success'))
                <div class="mb-4 flex items-center gap-2 rounded-[9px] border border-[#A5D6A7] bg-[#E8F5E9] px-3 py-2.5 text-[13px] font-semibold text-[#1F5F3F]">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('admin.skl.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                    {{-- Informasi Perusahaan --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Informasi Perusahaan</p>

                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $config['company_name']) }}"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
                            <textarea name="company_address" rows="2"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', $config['company_address']) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kota</label>
                                <input type="text" name="company_city" value="{{ old('company_city', $config['company_city']) }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Pimpinan</label>
                                <input type="text" name="leader_name" value="{{ old('leader_name', $config['leader_name']) }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan Pimpinan</label>
                            <input type="text" name="leader_title" value="{{ old('leader_title', $config['leader_title']) }}"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        </div>
                    </div>

                    {{-- Isi Surat --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Isi Surat</p>

                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Deskripsi Kegiatan</label>
                            <textarea name="activity_description" id="activityDesc" rows="6"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('activity_description', $config->activity_description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Pencapaian Peserta</label>
                            <textarea name="participant_achievement" id="achievementDesc" rows="6"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('participant_achievement', $config->participant_achievement ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Aset Visual --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>

                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Logo</label>
                            <input type="file" name="logo" accept="image/*"
                                class="block w-full text-[12.5px] text-[#4B5F5A]">
                            @if(Storage::disk('public')->exists('images/logos/logo_seveninc.png'))
                            <img src="{{ asset('storage/images/logos/logo_seveninc.png') }}"
                                class="mt-2 h-12 rounded-[6px] border border-[#DCE7E1] object-contain" alt="Logo">
                            @endif
                        </div>

                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Stempel / TTD</label>
                            <input type="file" name="stamp" accept="image/*"
                                class="block w-full text-[12.5px] text-[#4B5F5A]">
                        </div>
                    </div>

                    {{-- Generate untuk Pemagang Tertentu --}}
                    <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Generate SKL</p>
                        <p class="text-[12px] text-[#4B5F5A]">Generate SKL untuk pemagang dengan status <strong>Selesai</strong>.</p>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Pilih Pemagang</label>
                            <select id="sklInternSelect"
                                class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                                <option value="">-- Pilih pemagang selesai --</option>
                                {{-- Diisi via JS dari API --}}
                            </select>
                        </div>
                        <a id="btnGenerateSKL" href="#" target="_blank"
                            class="flex items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F] opacity-50 pointer-events-none">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Download SKL
                        </a>
                    </div>

                    {{-- Simpan --}}
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>

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

<script>
(function() {
    const iframe      = document.getElementById('sklPreview');
    const actDesc     = document.getElementById('activityDesc');
    const achDesc     = document.getElementById('achievementDesc');
    const btnRefresh  = document.getElementById('btnRefreshPreview');

    const BASE_URL    = "{{ route('admin.skl.preview') }}";
    const API_URL     = "{{ route('admin.interns.api') }}";
    const SKL_DL_BASE = "{{ url('/user/documents/skl/download') }}";

    function refreshPreview() {
        const params = new URLSearchParams();
        if (actDesc)  params.append('activity_description',    actDesc.value);
        if (achDesc)  params.append('participant_achievement', achDesc.value);
        iframe.src = BASE_URL + '?' + params.toString();
    }

    let delay;
    [actDesc, achDesc].forEach(el => {
        el?.addEventListener('input', () => {
            clearTimeout(delay);
            delay = setTimeout(refreshPreview, 400);
        });
    });

    btnRefresh?.addEventListener('click', refreshPreview);

    // Load daftar pemagang selesai ke select
    const sklSelect = document.getElementById('sklInternSelect');
    const btnGen    = document.getElementById('btnGenerateSKL');

    fetch(API_URL + '?scope=completed&per_page=1000', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    }).then(r => r.json()).then(json => {
        (json.data || []).forEach(it => {
            const opt = document.createElement('option');
            opt.value       = it.id;
            opt.textContent = `${it.fullname} — ${it.institution_name || '-'}`;
            opt.dataset.userId = it.user_id || '';
            sklSelect.appendChild(opt);
        });
    }).catch(() => {});

    sklSelect?.addEventListener('change', function() {
        if (!this.value) {
            btnGen.href = '#';
            btnGen.classList.add('opacity-50', 'pointer-events-none');
            return;
        }
        const userId = this.options[this.selectedIndex].dataset.userId;
        btnGen.href = SKL_DL_BASE + '?user_id=' + (userId || this.value);
        btnGen.classList.remove('opacity-50', 'pointer-events-none');
    });
})();
</script>
@endsection
