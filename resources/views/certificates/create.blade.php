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
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Buat Sertifikat Magang</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate.store') }}" id="certForm">
    @csrf
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Cari Intern ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Auto-Fill dari Data Pemagang</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                        Cari Pemagang
                    </label>
                    <div class="flex items-center gap-2 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2">
                        <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="intern_search" autocomplete="off"
                            placeholder="Ketik minimal 2 huruf..."
                            class="w-full border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A]">
                    </div>
                    <input type="hidden" id="intern_id">
                    <div id="intern_results"
                        class="absolute z-20 mt-1 max-h-72 w-full overflow-auto rounded-[10px] border border-[#DCE7E1] bg-white shadow-lg hidden">
                    </div>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Pilih hasil untuk mengisi otomatis Nama, Divisi, Tanggal, dan Kota.</p>
                </div>
                <div class="rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] p-3 text-[13px] space-y-1.5">
                    <div class="flex gap-2">
                        <span class="font-semibold text-[#4B5F5A] w-20 shrink-0">Institusi</span>
                        <span id="preview_institution" class="text-[#1B3A34]">—</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-semibold text-[#4B5F5A] w-20 shrink-0">Minat</span>
                        <span id="preview_interest" class="text-[#1B3A34]">—</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-semibold text-[#4B5F5A] w-20 shrink-0">Periode</span>
                        <span id="preview_period" class="text-[#1B3A34]">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 2: Data Sertifikat ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Sertifikat</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Divisi <span class="text-[#D32F2F]">*</span></label>
                    <select id="division" name="division" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih Divisi</option>
                        @foreach($divisions as $code => $label)
                        <option value="{{ $code }}" {{ old('division') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Perusahaan <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" id="company" name="company" value="{{ old('company') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-[#D32F2F]">*</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-[#D32F2F]">*</span></label>
                    <select id="brand" name="brand" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih Brand</option>
                        @foreach($brands as $code => $label)
                        <option value="{{ $code }}" {{ old('brand') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nomor Serial</label>
                    <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}" readonly
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#4B5F5A] outline-none cursor-default">
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Dihasilkan otomatis. Angka 000 adalah pratinjau.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanggal Mulai <span class="text-[#D32F2F]">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanggal Selesai <span class="text-[#D32F2F]">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 3: Aset Visual ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Background <span class="text-[#D32F2F]">*</span></label>
                    <select name="background_image" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file background</option>
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
                        <option value="">Pilih file logo</option>
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
            </div>
        </div>

        {{-- ===== SEKSI 4: Penandatangan ===== --}}
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

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan 1 <span class="text-[#D32F2F]">*</span></label>
                    <select name="signature_image1" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">Pilih file tanda tangan</option>
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
                        <option value="">- Tanpa tanda tangan 2 -</option>
                        @foreach($signatureFiles as $f)
                        <option value="{{ $f }}" {{ old('signature_image2') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
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
                Buat Sertifikat
            </button>
        </div>

    </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const SEARCH_URL = "{{ route('admin.interns.search') }}";
    const API_URL    = "{{ route('admin.interns.api') }}";

    const elSearch   = document.getElementById('intern_search');
    const elResults  = document.getElementById('intern_results');
    const elDivision = document.getElementById('division');
    const elCompany  = document.getElementById('company');
    const elBrand    = document.getElementById('brand');
    const elEndDate  = document.getElementById('end_date');
    const elStartDate= document.getElementById('start_date');
    const elSerial   = document.getElementById('serial_number');
    const elName     = document.getElementById('name');
    const elCity     = document.getElementById('city');
    const prevInst   = document.getElementById('preview_institution');
    const prevInt    = document.getElementById('preview_interest');
    const prevPeriod = document.getElementById('preview_period');

    const roman = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    const interestMap = {
        'administration':'ADM','administrasi':'ADM','uiux':'UIUX','ui-ux':'UIUX',
        'programmer':'PROG','hr':'HR','social-media-specialist':'SMM','photographer':'PV',
        'videographer':'VID','content-writer':'CW','marketing-and-sales':'MS',
        'graphic-designer':'CD','digital-marketing':'DM','public-relation':'PR',
        'tiktok-creator':'TC','content-planner':'CP','project-manager':'PM',
        'welding':'LAS','animation':'ANIM',
    };

    function companyCode(raw) {
        return ((raw||'').toUpperCase().replace(/\b(PT|CV|CO\.?|LTD\.?|INC\.?|TBK|PERSERO)\b\.?/gi,' ').trim().split(/\s+/)[0]||'').replace(/[^A-Z0-9]/g,'') || 'COMP';
    }

    function buildSerial() {
        const div  = (elDivision?.value||'').toUpperCase().trim() || 'DIV';
        const comp = companyCode(elCompany?.value||'');
        const brand= (elBrand?.value||'').toUpperCase().trim();
        const d    = new Date(elEndDate?.value||'');
        const m    = isNaN(d) ? 'I' : roman[d.getMonth()+1];
        const y    = isNaN(d) ? new Date().getFullYear() : d.getFullYear();
        elSerial.value = `000/SERT/${div}/${comp}${brand?'.'+brand:''}/${m}/${y}`;
    }

    ['change','input'].forEach(ev => {
        elDivision?.addEventListener(ev, buildSerial);
        elCompany?.addEventListener(ev, buildSerial);
        elBrand?.addEventListener(ev, buildSerial);
        elEndDate?.addEventListener(ev, buildSerial);
    });
    buildSerial();

    elStartDate?.addEventListener('change', () => {
        if (elEndDate.value && new Date(elEndDate.value) < new Date(elStartDate.value)) {
            elEndDate.value = elStartDate.value;
        }
        elEndDate.min = elStartDate.value;
        buildSerial();
    });

    // Typeahead
    let timer, items = [], active = -1;

    function hide() { elResults.classList.add('hidden'); elResults.innerHTML = ''; items = []; active = -1; }

    function render(list) {
        items = list; active = -1;
        if (!list.length) {
            elResults.innerHTML = `<div class="px-3 py-2 text-[13px] text-[#4B5F5A]">Tidak ada hasil</div>`;
        } else {
            elResults.innerHTML = list.map((it, i) =>
                `<button type="button" data-i="${i}"
                    class="intern-item w-full text-left px-3 py-2.5 text-[13px] hover:bg-[#F4F8F6] border-b border-[#DCE7E1] last:border-0">
                    <span class="font-semibold text-[#1B3A34]">${it.text}</span>
                </button>`
            ).join('');
        }
        elResults.classList.remove('hidden');
    }

    async function fetchDetail(id) {
        try {
            const res = await fetch(`${API_URL}?scope=all&search=`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        } catch(e) {}
    }

    function pick(it) {
        const nameOnly = String(it.text||'').replace(/\s*\([^)]+\)\s*$/,'').trim();
        if (elName && nameOnly) elName.value = nameOnly;
        if (it.division && elDivision) { elDivision.value = it.division; elDivision.dispatchEvent(new Event('change')); }
        prevInt.textContent = it.division || '—';
        prevInst.textContent = '—'; prevPeriod.textContent = '—';
        elSearch.value = nameOnly;
        hide();
        // fetch detail
        fetch(`${API_URL}?scope=all&per_page=1000&search=${encodeURIComponent(nameOnly)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
        }).then(r=>r.json()).then(json => {
            const row = (json.data||[]).find(r => r.id == it.id);
            if (!row) return;
            prevInst.textContent   = row.institution_name || '—';
            prevInt.textContent    = row.internship_interest || it.division || '—';
            prevPeriod.textContent = (row.start_date && row.end_date) ? `${row.start_date} s/d ${row.end_date}` : '—';
            if (row.start_date && elStartDate) { elStartDate.value = row.start_date; elEndDate.min = row.start_date; }
            if (row.end_date && elEndDate) { elEndDate.value = row.end_date; buildSerial(); }
            if (row.current_city && elCity) elCity.value = row.current_city;
        }).catch(()=>{});
    }

    elSearch.addEventListener('input', () => {
        const q = elSearch.value.trim();
        if (q.length < 2) { hide(); return; }
        clearTimeout(timer);
        timer = setTimeout(async () => {
            try {
                const res = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}&completed=0`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                render(Array.isArray(json.results) ? json.results : []);
            } catch(e) { hide(); }
        }, 250);
    });

    elResults.addEventListener('click', e => {
        const btn = e.target.closest('.intern-item');
        if (btn) pick(items[+btn.dataset.i]);
    });

    elSearch.addEventListener('keydown', e => {
        if (elResults.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(items.length-1, active+1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(0, active-1); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); pick(items[active]); return; }
        else if (e.key === 'Escape') { hide(); return; }
        else return;
        elResults.querySelectorAll('.intern-item').forEach((n,i) => {
            n.classList.toggle('bg-[#F4F8F6]', i === active);
        });
    });

    document.addEventListener('click', e => { if (!e.target.closest('#intern_search') && !e.target.closest('#intern_results')) hide(); });
});
</script>
@endpush

@endsection
