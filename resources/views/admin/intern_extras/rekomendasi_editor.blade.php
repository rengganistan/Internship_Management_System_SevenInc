@extends('layouts.dashboard')

@section('title', 'Template Surat Rekomendasi')

@section('content')
<div class="min-h-screen bg-[#F4F8F6]">

  {{-- Layout: kiri (form) + kanan (preview iframe) --}}
  <div class="flex h-[calc(100vh-64px)] overflow-hidden">

    {{-- ===== KIRI: FORM ===== --}}
    <div class="w-full max-w-md shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-white">
      <div class="p-5">

        {{-- Header --}}
        <div class="mb-5">
          <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Informasi Alumni</p>
          <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Template Surat Rekomendasi</h1>
          <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Pilih brand → pilih pemagang → isi pengaturan → generate.</p>
        </div>

        {{-- ═══════════════════════════════════════════════
             LANGKAH 1: PILIH BRAND
        ═══════════════════════════════════════════════ --}}
        <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
          <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 1 — Pilih Brand</p>
          <div>
            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Brand Perusahaan</label>
            <select id="brandSelect"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              <option value="">-- Pilih Brand --</option>
              @foreach($brands as $brand)
                <option value="{{ $brand }}">{{ $brand }}</option>
              @endforeach
            </select>
          </div>
          <p class="text-[11px] text-[#4B5F5A]">Setelah memilih brand, nama pemagang yang statusnya <strong>Selesai</strong> akan muncul.</p>
        </div>

        {{-- ═══════════════════════════════════════════════
             LANGKAH 2: PILIH PEMAGANG (muncul setelah pilih brand)
        ═══════════════════════════════════════════════ --}}
        <div id="stepInterns" class="hidden rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
          <div class="flex items-center justify-between">
            <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 2 — Pilih Pemagang</p>
            <label class="flex items-center gap-1.5 text-[12px] font-semibold text-[#2D8659] cursor-pointer select-none">
              <input type="checkbox" id="checkAll" class="accent-[#2D8659]">
              Pilih Semua
            </label>
          </div>

          {{-- Loading state --}}
          <div id="internsLoading" class="hidden py-3 text-center text-[12px] text-[#4B5F5A]">
            <svg class="inline-block h-4 w-4 animate-spin mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" opacity=".25"/><path d="M21 12a9 9 0 0 1-9 9" stroke-linecap="round"/></svg>
            Memuat data pemagang...
          </div>

          {{-- Empty state --}}
          <div id="internsEmpty" class="hidden py-3 text-center text-[12px] text-[#4B5F5A]">
            Tidak ada pemagang dengan status Selesai untuk brand ini.
          </div>

          {{-- List pemagang --}}
          <div id="internsList" class="space-y-2 max-h-52 overflow-y-auto pr-1"></div>

          <p id="selectedCount" class="text-[11.5px] text-[#4B5F5A]">0 pemagang dipilih</p>
        </div>

        {{-- ═══════════════════════════════════════════════
             LANGKAH 3: PENGATURAN (muncul setelah pilih brand)
        ═══════════════════════════════════════════════ --}}
        <div id="stepSettings" class="hidden">

          <form method="POST" action="{{ route('admin.rekomendasi.update') }}"
                enctype="multipart/form-data" class="space-y-4" id="templateForm">
            @csrf

            {{-- Informasi Perusahaan --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Informasi Perusahaan</p>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                <input type="text" name="company_name" id="f_company_name"
                  value="{{ old('company_name', $config->company_name) }}"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              </div>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
                <textarea name="company_address" id="f_company_address" rows="2"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', $config->company_address) }}</textarea>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kota</label>
                  <input type="text" name="company_city" id="f_company_city"
                    value="{{ old('company_city', $config->company_city) }}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
                <div>
                  <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kode Pos</label>
                  <input type="text" name="company_postal_code"
                    value="{{ old('company_postal_code', $config->company_postal_code) }}"
                    placeholder="55198"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>
              </div>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nomor Telepon</label>
                <input type="text" name="company_phone"
                  value="{{ old('company_phone', $config->company_phone) }}"
                  placeholder="0274-4534571"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              </div>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Brand Perusahaan</label>
                <input type="text" name="company_brand" id="f_company_brand"
                  value="{{ old('company_brand', $config->company_brand) }}"
                  placeholder="Seven Inc (Magangjogja.com)"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              </div>
            </div>

            {{-- Penandatangan --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Penandatangan</p>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Nama Pimpinan</label>
                <input type="text" name="leader_name" id="f_leader_name"
                  value="{{ old('leader_name', $config->leader_name) }}"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              </div>

              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jabatan Pimpinan</label>
                <input type="text" name="leader_title" id="f_leader_title"
                  value="{{ old('leader_title', $config->leader_title) }}"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
              </div>
            </div>

            {{-- Isi Surat --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Isi Surat</p>
              <p class="text-[11.5px] text-[#4B5F5A] leading-relaxed">
                Gunakan placeholder:<br>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{nama}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{divisi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{mulai}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{selesai}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{durasi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{instansi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{nim}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{company_brand}</code>
              </p>
              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Paragraf Isi Surat</label>
                <textarea name="body_template" id="f_body_template" rows="6"
                  class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('body_template', $config->body_template ?? \App\Models\RekomendasiSetting::defaultBodyTemplate()) }}</textarea>
              </div>
            </div>

            {{-- Aset Visual --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Logo</label>
                <input type="file" name="logo" id="f_logo" accept="image/*"
                  class="block w-full text-[12.5px] text-[#4B5F5A]">
                @if($config->logo_path && Storage::disk('public')->exists($config->logo_path))
                <img src="{{ asset('storage/' . $config->logo_path) }}"
                  class="mt-2 h-12 rounded-[6px] border border-[#DCE7E1] object-contain" alt="Logo">
                @endif
              </div>
              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Tanda Tangan / Stempel</label>
                <input type="file" name="stamp" id="f_stamp" accept="image/*"
                  class="block w-full text-[12.5px] text-[#4B5F5A]">
                @if($config->stamp_path && Storage::disk('public')->exists($config->stamp_path))
                <img src="{{ asset('storage/' . $config->stamp_path) }}"
                  class="mt-2 h-12 rounded-[6px] border border-[#DCE7E1] object-contain" alt="TTD">
                @endif
              </div>
            </div>

            {{-- Tombol Simpan Template --}}
            <div class="flex justify-between items-center pt-1 gap-3">
              <button type="submit" form="templateForm"
                class="flex items-center gap-2 rounded-[9px] border border-[#2D8659] px-4 py-2 text-sm font-semibold text-[#2D8659] transition hover:bg-[#F4F8F6]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Simpan Template
              </button>

              {{-- Tombol Generate --}}
              <button type="button" id="btnGenerate"
                disabled
                class="flex flex-1 items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <span id="btnGenerateLabel">Generate Surat</span>
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>

    {{-- ===== KANAN: PREVIEW IFRAME ===== --}}
    <div class="flex-1 flex flex-col bg-[#F4F8F6]">
      <div class="flex items-center justify-between border-b border-[#DCE7E1] bg-white px-4 py-3">
        <div class="flex items-center gap-2">
          <span class="h-2 w-2 rounded-full bg-[#2D8659]"></span>
          <span class="text-[13px] font-semibold text-[#1B3A34]">Preview Surat Rekomendasi</span>
          <span class="text-[11px] text-[#4B5F5A]">— diperbarui otomatis saat mengetik</span>
        </div>
        <button id="btnRefresh"
          class="flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
          Refresh
        </button>
      </div>
      <div class="flex-1 overflow-hidden">
        <iframe id="rekPreview"
          src="{{ route('admin.rekomendasi.preview') }}"
          class="h-full w-full border-0">
        </iframe>
      </div>
    </div>

  </div>
</div>

{{-- ===== MODAL: HASIL GENERATE ===== --}}
<div id="modalResult" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
  <div class="w-full max-w-sm rounded-[14px] bg-white shadow-xl mx-4 overflow-hidden">

    {{-- Success state --}}
    <div id="modalSuccess" class="hidden p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
        <svg class="h-7 w-7 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1">Berhasil Generate!</h3>
      <p id="modalSuccessMsg" class="text-[13px] text-[#4B5F5A] mb-1"></p>
      <p class="text-[12px] text-[#4B5F5A]">Surat sudah tersedia di halaman <strong>Dokumen</strong> masing-masing pemagang.</p>
      <div id="modalFailedInfo" class="hidden mt-3 rounded-[8px] bg-red-50 border border-red-200 p-3 text-left">
        <p class="text-[12px] font-semibold text-red-700 mb-1">Gagal generate:</p>
        <p id="modalFailedNames" class="text-[12px] text-red-600"></p>
      </div>
      <button id="btnModalClose"
        class="mt-5 w-full rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-[#1F5F3F] transition">
        Tutup
      </button>
    </div>

    {{-- Error state --}}
    <div id="modalError" class="hidden p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
        <svg class="h-7 w-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1">Gagal Generate</h3>
      <p id="modalErrorMsg" class="text-[13px] text-[#4B5F5A]"></p>
      <button id="btnModalCloseErr"
        class="mt-5 w-full rounded-[9px] bg-red-600 px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-red-700 transition">
        Tutup
      </button>
    </div>

    {{-- Loading state --}}
    <div id="modalLoading" class="p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50">
        <svg class="h-7 w-7 animate-spin text-[#2D8659]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" opacity=".2"/><path d="M21 12a9 9 0 0 1-9 9" stroke-linecap="round"/></svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1">Sedang Membuat Surat...</h3>
      <p id="modalLoadingMsg" class="text-[13px] text-[#4B5F5A]">Mohon tunggu sebentar</p>
    </div>

  </div>
</div>

<script>
(function () {
  const iframe        = document.getElementById('rekPreview');
  const PREVIEW_URL   = @json(route('admin.rekomendasi.preview'));
  const BRAND_API_URL = @json(route('admin.rekomendasi.interns_by_brand'));
  const GEN_URL       = @json(route('admin.rekomendasi.generate_brand'));
  const csrf          = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ── Preview live update ──────────────────────────────────────────
  const watchFields = [
    'f_company_name','f_company_address','f_company_city',
    'f_company_brand','f_leader_name','f_leader_title','f_body_template'
  ];

  function buildPreviewParams() {
    const params = new URLSearchParams();
    watchFields.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value) params.append(el.name || id.replace('f_',''), el.value);
    });
    return params;
  }

  function refreshPreview() {
    // Sertakan intern_id jika ada pemagang yang sudah dipilih
    const params = buildPreviewParams();
    const firstChecked = internsList?.querySelector('input[type="checkbox"]:checked');
    if (firstChecked) params.set('intern_id', firstChecked.value);
    iframe.src = PREVIEW_URL + '?' + params.toString();
  }

  let delay;
  watchFields.forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
      clearTimeout(delay);
      delay = setTimeout(refreshPreview, 450);
    });
  });

  document.getElementById('btnRefresh')?.addEventListener('click', refreshPreview);

  // ── Brand select → load pemagang ─────────────────────────────────
  const brandSel    = document.getElementById('brandSelect');
  const stepInterns = document.getElementById('stepInterns');
  const stepSettings= document.getElementById('stepSettings');
  const internsList = document.getElementById('internsList');
  const internsLoad = document.getElementById('internsLoading');
  const internsEmpty= document.getElementById('internsEmpty');
  const checkAll    = document.getElementById('checkAll');
  const selCount    = document.getElementById('selectedCount');
  const btnGenerate = document.getElementById('btnGenerate');
  const btnGenLabel = document.getElementById('btnGenerateLabel');

  function updateSelectedCount() {
    const checked = internsList.querySelectorAll('input[type="checkbox"]:checked').length;
    selCount.textContent = checked + ' pemagang dipilih';
    btnGenerate.disabled = checked === 0;
    btnGenLabel.textContent = checked > 1
      ? `Generate ${checked} Surat Rekomendasi`
      : checked === 1 ? 'Generate Surat Rekomendasi' : 'Generate Surat';

    // Refresh preview dengan data pemagang pertama yang dipilih
    refreshPreview();
  }

  brandSel?.addEventListener('change', function () {
    const brand = this.value;

    if (!brand) {
      stepInterns.classList.add('hidden');
      stepSettings.classList.add('hidden');
      return;
    }

    // Otomatis isi field company_name dan company_brand dengan nilai brand
    const fName  = document.getElementById('f_company_name');
    const fBrand = document.getElementById('f_company_brand');
    if (fName)  fName.value  = brand;
    if (fBrand) fBrand.value = brand;
    refreshPreview();

    // Tampilkan section
    stepInterns.classList.remove('hidden');
    stepSettings.classList.remove('hidden');

    // Load pemagang
    internsList.innerHTML = '';
    internsLoad.classList.remove('hidden');
    internsEmpty.classList.add('hidden');
    checkAll.checked = false;
    updateSelectedCount();

    fetch(BRAND_API_URL + '?brand=' + encodeURIComponent(brand), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(json => {
      internsLoad.classList.add('hidden');
      const interns = json.interns || [];

      if (interns.length === 0) {
        internsEmpty.classList.remove('hidden');
        return;
      }

      interns.forEach(intern => {
        const label = document.createElement('label');
        label.className = 'flex items-start gap-2.5 rounded-[8px] border border-[#DCE7E1] px-3 py-2.5 cursor-pointer hover:border-[#2D8659] hover:bg-[#F4F8F6] transition select-none';

        const hasBadge = intern.has_rekomendasi
          ? `<span class="ml-auto text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 shrink-0">Sudah punya</span>`
          : '';

        label.innerHTML = `
          <input type="checkbox" name="intern_ids[]" value="${intern.id}"
            class="mt-0.5 h-3.5 w-3.5 accent-[#2D8659] shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-[12.5px] font-semibold text-[#1B3A34] truncate">${intern.fullname}</p>
            <p class="text-[11px] text-[#4B5F5A] truncate">${intern.institution_name || '-'} · ${intern.internship_interest || '-'}</p>
          </div>
          ${hasBadge}
        `;
        internsList.appendChild(label);

        label.querySelector('input').addEventListener('change', updateSelectedCount);
      });
    })
    .catch(() => {
      internsLoad.classList.add('hidden');
      internsEmpty.classList.remove('hidden');
      internsEmpty.textContent = 'Gagal memuat data pemagang.';
    });
  });

  // Pilih semua checkbox
  checkAll?.addEventListener('change', function () {
    internsList.querySelectorAll('input[type="checkbox"]')
      .forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
  });

  // ── Modal helpers ────────────────────────────────────────────────
  const modal       = document.getElementById('modalResult');
  const mSuccess    = document.getElementById('modalSuccess');
  const mError      = document.getElementById('modalError');
  const mLoading    = document.getElementById('modalLoading');
  const mSuccessMsg = document.getElementById('modalSuccessMsg');
  const mErrorMsg   = document.getElementById('modalErrorMsg');
  const mFailedInfo = document.getElementById('modalFailedInfo');
  const mFailedNames= document.getElementById('modalFailedNames');

  function showModal(state) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    mLoading.classList.add('hidden');
    mSuccess.classList.add('hidden');
    mError.classList.add('hidden');
    if (state === 'loading') mLoading.classList.remove('hidden');
    if (state === 'success') mSuccess.classList.remove('hidden');
    if (state === 'error')   mError.classList.remove('hidden');
  }

  function hideModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  document.getElementById('btnModalClose')?.addEventListener('click', hideModal);
  document.getElementById('btnModalCloseErr')?.addEventListener('click', hideModal);
  modal.addEventListener('click', e => { if (e.target === modal) hideModal(); });

  // ── Generate ─────────────────────────────────────────────────────
  btnGenerate?.addEventListener('click', async function () {
    const checked = [...internsList.querySelectorAll('input[type="checkbox"]:checked')];
    if (checked.length === 0) return;

    // Konfirmasi
    const names = checked.map(cb => {
      const label = cb.closest('label');
      return label?.querySelector('p')?.textContent?.trim() ?? 'Pemagang';
    });

    if (!confirm(`Generate surat rekomendasi untuk ${checked.length} pemagang?\n\n${names.slice(0,5).join('\n')}${names.length > 5 ? '\n...' : ''}`)) {
      return;
    }

    showModal('loading');
    document.getElementById('modalLoadingMsg').textContent =
      `Membuat ${checked.length} surat rekomendasi...`;

    // Kumpulkan data form
    const form     = document.getElementById('templateForm');
    const formData = new FormData(form);

    // Tambahkan intern_ids
    checked.forEach(cb => formData.append('intern_ids[]', cb.value));

    // Tambahkan logo & stamp file jika ada
    const logoFile  = document.getElementById('f_logo')?.files[0];
    const stampFile = document.getElementById('f_stamp')?.files[0];
    if (logoFile)  formData.set('logo',  logoFile);
    if (stampFile) formData.set('stamp', stampFile);

    try {
      const res  = await fetch(GEN_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
        credentials: 'same-origin',
      });

      const json = await res.json();

      if (json.success || json.generated > 0) {
        mSuccessMsg.textContent = json.message;

        if (json.failed > 0 && json.failed_names?.length) {
          mFailedInfo.classList.remove('hidden');
          mFailedNames.textContent = json.failed_names.join(', ');
        } else {
          mFailedInfo.classList.add('hidden');
        }

        showModal('success');
      } else {
        mErrorMsg.textContent = json.message || 'Terjadi kesalahan saat generate surat.';
        showModal('error');
      }
    } catch (err) {
      mErrorMsg.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
      showModal('error');
    }
  });

})();
</script>
@endsection
