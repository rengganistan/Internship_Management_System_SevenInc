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
          <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Kelola template & generate surat rekomendasi untuk alumni pilihan.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-[9px] border border-[#A5D6A7] bg-[#E8F5E9] px-3 py-2.5 text-[13px] font-semibold text-[#1F5F3F]">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
          {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-[9px] border border-red-200 bg-red-50 px-3 py-2.5 text-[13px] font-semibold text-[#D32F2F]">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ session('error') }}
        </div>
        @endif

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
              <input type="file" name="logo" accept="image/*"
                class="block w-full text-[12.5px] text-[#4B5F5A]">
              @if($config->logo_path && Storage::disk('public')->exists($config->logo_path))
              <img src="{{ asset('storage/' . $config->logo_path) }}"
                class="mt-2 h-12 rounded-[6px] border border-[#DCE7E1] object-contain" alt="Logo">
              @endif
            </div>
            <div>
              <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Tanda Tangan / Stempel</label>
              <input type="file" name="stamp" accept="image/*"
                class="block w-full text-[12.5px] text-[#4B5F5A]">
              @if($config->stamp_path && Storage::disk('public')->exists($config->stamp_path))
              <img src="{{ asset('storage/' . $config->stamp_path) }}"
                class="mt-2 h-12 rounded-[6px] border border-[#DCE7E1] object-contain" alt="TTD">
              @endif
            </div>
          </div>

          {{-- Generate per Pemagang --}}
          <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3">
            <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Generate Surat Rekomendasi</p>
            <p class="text-[12px] text-[#4B5F5A]">Pilih alumni yang berhak mendapat surat rekomendasi. Hanya pemagang dengan status <strong>Selesai</strong>.</p>

            <div>
              <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Pilih Pemagang</label>
              <select id="rekInternSelect"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                <option value="">-- Pilih alumni selesai --</option>
              </select>
            </div>

            {{-- Tombol submit via JS — bukan nested form --}}
            <button type="button" id="btnGenerate"
              disabled
              class="flex w-full items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Generate & Download PDF
            </button>
          </div>

          {{-- Simpan Template --}}
          <div class="flex justify-end pt-1">
            <button type="submit" form="templateForm"
              class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
              Simpan Template
            </button>
          </div>

        </form>

        {{-- Form generate TERPISAH di luar templateForm — mencegah nested form --}}
        <form id="generateForm" method="POST" action="" style="display:none">
          @csrf
        </form>
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

<script>
(function () {
  const iframe      = document.getElementById('rekPreview');
  const PREVIEW_URL = @json(route('admin.rekomendasi.preview'));
  const API_URL     = @json(route('admin.interns.api'));
  const GEN_BASE    = @json(url('/admin/rekomendasi/generate'));
  const csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // Semua field yang mempengaruhi preview
  const watchFields = [
    'f_company_name','f_company_address','f_company_city',
    'f_company_brand','f_leader_name','f_leader_title','f_body_template'
  ];

  function refreshPreview() {
    const params = new URLSearchParams();
    watchFields.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value) params.append(el.name || id.replace('f_',''), el.value);
    });
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

  // Load pemagang selesai ke select
  const sel     = document.getElementById('rekInternSelect');
  const btnGen  = document.getElementById('btnGenerate');
  const genForm = document.getElementById('generateForm');

  fetch(API_URL + '?scope=completed&per_page=1000', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
  }).then(r => r.json()).then(json => {
    (json.data || []).forEach(it => {
      const opt = new Option(
        `${it.fullname} — ${it.institution_name || '-'}`,
        it.id
      );
      sel.appendChild(opt);
    });
  }).catch(() => {});

  sel?.addEventListener('change', function () {
    if (!this.value) {
      btnGen.disabled = true;
      genForm.action = '';
      return;
    }
    genForm.action = `${GEN_BASE}/${this.value}`;
    btnGen.disabled = false;
  });

  // Klik tombol → submit form generate yang terpisah
  btnGen?.addEventListener('click', function () {
    if (!sel.value || !genForm.action) return;
    genForm.submit();
  });
})();
</script>
@endsection
