@extends('layouts.dashboard')

@section('content')
@php
  $allBrandMode = request('mode') === 'all_brand';
@endphp

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-5 flex items-center gap-3">
    <a href="{{ route('admin.intern_extras.index') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#2D8659] transition">
      <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-0.5">Informasi Alumni</p>
      <h1 class="text-xl font-extrabold text-[#1B3A34]">{{ $intern->fullname }}</h1>
      <p class="text-sm text-[#4B5F5A]">{{ $intern->internship_interest }} · {{ $intern->institution_name }}
        @if($intern->brand)
          · <span class="text-[#2D8659] font-semibold">{{ $intern->brand }}</span>
        @endif
      </p>
    </div>
    @if($allBrandMode && $intern->brand)
      <span class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#2D8659] bg-[#EBF5EF] border border-[#BDE3CC] rounded-lg">
        <i class="fas fa-users text-xs"></i>
        Mode: Semua Brand {{ $intern->brand }}
      </span>
    @endif
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">{!! session('success') !!}</div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      <p class="font-semibold mb-2">Perbaiki field berikut:</p>
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- ===================================================
       BAGIAN 1: SURAT REKOMENDASI (Split Layout)
  =================================================== --}}
  <div class="mb-5">
    <div class="flex items-center gap-2 mb-3">
      <div class="w-7 h-7 rounded-lg bg-[#EBF5EF] flex items-center justify-center text-[#2D8659]">
        <i class="fas fa-medal text-xs"></i>
      </div>
      <h2 class="font-bold text-[#1B3A34]">Surat Rekomendasi</h2>
      @if($allBrandMode && $intern->brand)
        <span class="text-xs text-[#4B5F5A] ml-1">— akan dikirim ke semua pemagang brand <strong>{{ $intern->brand }}</strong></span>
      @else
        <span class="text-xs text-[#4B5F5A] ml-1">— khusus untuk <strong>{{ $intern->fullname }}</strong></span>
      @endif
    </div>

    <div class="flex gap-4 items-start" style="min-height:520px;">

      {{-- ===== Kiri: Form Template ===== --}}
      <div class="w-full max-w-sm shrink-0 bg-white rounded-xl border border-[#DCE7E1] shadow-sm overflow-y-auto" style="max-height:76vh;">
        <div class="p-5 space-y-4">

          {{-- Status surat saat ini --}}
          @if($extra->rekomendasi_path && !$allBrandMode)
            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
              <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-green-600"></i>
                <div>
                  <span class="text-sm text-green-800 font-semibold">Surat sudah dikirim</span>
                  <span class="text-xs text-green-600 block">{{ $extra->rekomendasi_granted_at?->format('d M Y') }}</span>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <a href="{{ route('admin.documents.serve', ['type' => 'rekomendasi', 'filename' => basename($extra->rekomendasi_path)]) }}"
                   target="_blank" class="text-xs font-semibold text-[#2D8659] hover:underline">Lihat</a>
                <form method="POST" action="{{ route('admin.intern_extras.rekomendasi.destroy', $intern->id) }}" class="inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-xs font-semibold text-red-600 hover:underline"
                          onclick="return confirm('Hapus surat rekomendasi ini?')">Hapus</button>
                </form>
              </div>
            </div>
          @elseif($allBrandMode)
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
              <i class="fas fa-info-circle mr-1"></i>
              Mode <strong>Kelola Semua</strong>: Surat akan digenerate & dikirim ke semua pemagang brand <strong>{{ $intern->brand }}</strong>.
            </div>
          @else
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
              <i class="fas fa-clock mr-1"></i>
              Belum ada surat rekomendasi untuk pemagang ini.
            </div>
          @endif

          {{-- Form template (tanpa action, submit via JS) --}}
          <form id="rekomendasiForm" enctype="multipart/form-data">
            @csrf

            {{-- Informasi Perusahaan --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
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
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
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
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Isi Surat</p>
              <p class="text-[11px] text-[#4B5F5A] leading-relaxed">
                Placeholder:
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{nama}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{divisi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{mulai}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{selesai}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{durasi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{instansi}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{nim}</code>
                <code class="bg-[#F4F8F6] px-1 rounded text-[11px]">{company_brand}</code>
              </p>
              <textarea name="body_template" id="f_body_template" rows="5"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('body_template', $config->body_template ?? \App\Models\RekomendasiSetting::defaultBodyTemplate()) }}</textarea>
            </div>

            {{-- Aset Visual --}}
            <div class="rounded-[10px] border border-[#DCE7E1] p-4 space-y-3 mb-4">
              <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Logo</label>
                <input type="file" name="logo" id="f_logo" accept="image/*"
                  class="block w-full text-[12.5px] text-[#4B5F5A]">
                @if(isset($config->logo_path) && $config->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($config->logo_path))
                <img src="{{ asset('storage/' . $config->logo_path) }}"
                  class="mt-2 h-10 rounded border border-[#DCE7E1] object-contain" alt="Logo">
                @endif
              </div>
              <div>
                <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Upload Tanda Tangan / Stempel</label>
                <input type="file" name="stamp" id="f_stamp" accept="image/*"
                  class="block w-full text-[12.5px] text-[#4B5F5A]">
                @if(isset($config->stamp_path) && $config->stamp_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($config->stamp_path))
                <img src="{{ asset('storage/' . $config->stamp_path) }}"
                  class="mt-2 h-10 rounded border border-[#DCE7E1] object-contain" alt="TTD">
                @endif
              </div>
            </div>

            {{-- Tombol Aksi — hanya Simpan Perubahan (Kirim Semua ada di bawah Info Kerja) --}}
            <div class="flex gap-2 pt-1">
              <button type="button" id="btnSaveTemplate"
                class="flex items-center gap-1.5 rounded-[9px] border border-[#2D8659] px-4 py-2 text-[13px] font-semibold text-[#2D8659] transition hover:bg-[#F4F8F6]">
                <i class="fas fa-save text-xs"></i>
                Simpan Perubahan
              </button>
            </div>

          </form>

        </div>
      </div>

      {{-- ===== Kanan: Preview Iframe ===== --}}
      <div class="flex-1 bg-white rounded-xl border border-[#DCE7E1] shadow-sm overflow-hidden flex flex-col" style="min-height:520px; max-height:76vh;">
        <div class="flex items-center justify-between border-b border-[#DCE7E1] px-4 py-2.5">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[#2D8659]"></span>
            <span class="text-[13px] font-semibold text-[#1B3A34]">Preview Surat</span>
            <span class="text-[11px] text-[#4B5F5A]">— diperbarui otomatis</span>
          </div>
          <button id="btnRefresh"
            class="flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <i class="fas fa-sync-alt text-xs"></i> Refresh
          </button>
        </div>
        <div class="flex-1">
          <iframe id="rekPreview"
            src="{{ route('admin.rekomendasi.preview') }}?intern_id={{ $intern->id }}"
            class="w-full h-full border-0" style="min-height:460px;"></iframe>
        </div>
      </div>

    </div>
  </div>

  {{-- ===================================================
       BAGIAN 2: LINK GRUP ALUMNI & INFO KERJA
  =================================================== --}}
  <form action="{{ route('admin.intern_extras.update', $intern->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    {{-- Kirim mode agar simpan otomatis ke semua brand bila mode all_brand --}}
    @if($allBrandMode)
      <input type="hidden" name="mode" value="all_brand">
    @endif

    <div class="space-y-4">

      {{-- Link Grup Alumni --}}
      <div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center text-purple-700">
            <i class="fas fa-users"></i>
          </div>
          <h3 class="font-bold text-[#1B3A34]">Link Grup Alumni</h3>
          @if($allBrandMode && $intern->brand)
            <span class="text-xs text-[#4B5F5A] ml-1">— berlaku untuk semua brand <strong>{{ $intern->brand }}</strong></span>
          @endif
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">URL Grup <span class="text-gray-400 text-xs">(WhatsApp/Telegram/dll)</span></label>
            <input type="url" name="alumni_group_url" id="inp_alumni_group_url"
                   value="{{ old('alumni_group_url', $extra->alumni_group_url) }}"
                   placeholder="https://chat.whatsapp.com/..."
                   class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">Label Tombol</label>
            <input type="text" name="alumni_group_label" id="inp_alumni_group_label"
                   value="{{ old('alumni_group_label', $extra->alumni_group_label) }}"
                   placeholder="Grup Alumni Seveninc"
                   class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
          </div>
        </div>
        @if($extra->alumni_group_url)
          <label class="flex items-center gap-2 mt-3 text-sm text-red-600 cursor-pointer">
            <input type="checkbox" name="clear_alumni" value="1" class="accent-red-500">
            Hapus link grup alumni
          </label>
        @endif
      </div>

      {{-- Info Kerja --}}
      <div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700">
            <i class="fas fa-briefcase"></i>
          </div>
          <h3 class="font-bold text-[#1B3A34]">Info Kerja</h3>
          @if($allBrandMode && $intern->brand)
            <span class="text-xs text-[#4B5F5A] ml-1">— berlaku untuk semua brand <strong>{{ $intern->brand }}</strong></span>
          @endif
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">URL Info Kerja</label>
            <input type="url" name="job_info_url" id="inp_job_info_url"
                   value="{{ old('job_info_url', $extra->job_info_url) }}"
                   placeholder="https://..."
                   class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">Deskripsi singkat <span class="text-gray-400 text-xs">(opsional)</span></label>
            <textarea name="job_info_description" id="inp_job_info_description" rows="2"
                      placeholder="Contoh: Lowongan Full Stack Developer di partner Seveninc..."
                      class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659] resize-none">{{ old('job_info_description', $extra->job_info_description) }}</textarea>
          </div>
        </div>
        @if($extra->job_info_url)
          <label class="flex items-center gap-2 mt-3 text-sm text-red-600 cursor-pointer">
            <input type="checkbox" name="clear_job_info" value="1" class="accent-red-500">
            Hapus info kerja
          </label>
        @endif
      </div>

    </div>

    <div class="mt-5 flex flex-wrap items-center gap-3">
      <button type="submit"
              class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl"
              style="background-color:#2D8659;">
        Simpan Perubahan
      </button>
      <a href="{{ route('admin.intern_extras.index') }}"
         class="px-6 py-2.5 text-sm font-semibold text-[#4B5F5A] border border-[#DCE7E1] bg-white rounded-xl hover:bg-[#F4F8F6] transition">
        Batal
      </a>

      {{-- Tombol Kirim Semua: generate surat rekomendasi + simpan link alumni + info kerja sekaligus --}}
      <div class="ml-auto text-right">
        <button type="button" id="btnKirimSemua"
          class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl"
          style="background-color:#1B3A34;">
          <i class="fas fa-paper-plane text-xs"></i>
          @if($allBrandMode && $intern->brand)
            Kirim Semua ke Brand {{ $intern->brand }}
          @else
            Kirim Semua
          @endif
        </button>
        <p class="text-xs text-[#4B5F5A] mt-1">Surat rekomendasi + link alumni + info kerja</p>
      </div>
    </div>

  </form>
</div>

{{-- ===== MODAL HASIL GENERATE / KIRIM ===== --}}
<div id="modalResult" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
  <div class="w-full max-w-sm rounded-[14px] bg-white shadow-xl mx-4 overflow-hidden">

    {{-- Loading --}}
    <div id="modalLoading" class="p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50">
        <svg class="h-7 w-7 animate-spin text-[#2D8659]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" opacity=".2"/>
          <path d="M21 12a9 9 0 0 1-9 9" stroke-linecap="round"/>
        </svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1" id="modalLoadingTitle">Sedang Menyimpan...</h3>
      <p class="text-[13px] text-[#4B5F5A]" id="modalLoadingMsg">Mohon tunggu sebentar</p>
    </div>

    {{-- Sukses --}}
    <div id="modalSuccess" class="hidden p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
        <svg class="h-7 w-7 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1" id="modalSuccessTitle">Berhasil!</h3>
      <p class="text-[13px] text-[#4B5F5A] mb-1" id="modalSuccessMsg"></p>
      <p class="text-[12px] text-[#4B5F5A]" id="modalSuccessSubtitle"></p>
      <div id="modalFailedInfo" class="hidden mt-3 rounded-[8px] bg-red-50 border border-red-200 p-3 text-left">
        <p class="text-[12px] font-semibold text-red-700 mb-1">Gagal:</p>
        <p id="modalFailedNames" class="text-[12px] text-red-600"></p>
      </div>
      <button id="btnModalClose"
        class="mt-5 w-full rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-[#1F5F3F] transition">
        Tutup
      </button>
    </div>

    {{-- Error --}}
    <div id="modalError" class="hidden p-6 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
        <svg class="h-7 w-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <h3 class="text-base font-extrabold text-[#1B3A34] mb-1">Gagal</h3>
      <p class="text-[13px] text-[#4B5F5A]" id="modalErrorMsg"></p>
      <button id="btnModalCloseErr"
        class="mt-5 w-full rounded-[9px] bg-red-600 px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-red-700 transition">
        Tutup
      </button>
    </div>

  </div>
</div>

<script>
(function () {
  const PREVIEW_URL    = @json(route('admin.rekomendasi.preview'));
  const SAVE_URL       = @json(route('admin.intern_extras.rekomendasi.save_template', $intern->id));
  const SEND_ALL_URL   = @json(route('admin.intern_extras.send_all', $intern->id));
  const ALL_BRAND_MODE = @json($allBrandMode);
  const INTERN_ID      = {{ $intern->id }};
  const INTERN_BRAND   = @json($intern->brand ?? '');
  const csrf           = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const iframe      = document.getElementById('rekPreview');
  const watchFields = ['f_company_name','f_company_address','f_company_city','f_company_brand','f_leader_name','f_leader_title','f_body_template'];

  // ── Preview live update ──────────────────────────────────────────
  function buildPreviewParams() {
    const params = new URLSearchParams();
    params.set('intern_id', INTERN_ID);
    watchFields.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value) params.append(el.name || id.replace('f_',''), el.value);
    });
    return params;
  }

  function refreshPreview() {
    iframe.src = PREVIEW_URL + '?' + buildPreviewParams().toString();
  }

  let delay;
  watchFields.forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
      clearTimeout(delay);
      delay = setTimeout(refreshPreview, 450);
    });
  });

  document.getElementById('btnRefresh')?.addEventListener('click', refreshPreview);

  // ── Modal helpers ─────────────────────────────────────────────────
  const modal    = document.getElementById('modalResult');
  const mSuccess = document.getElementById('modalSuccess');
  const mError   = document.getElementById('modalError');
  const mLoading = document.getElementById('modalLoading');

  function showModal(state, opts = {}) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    mLoading.classList.add('hidden');
    mSuccess.classList.add('hidden');
    mError.classList.add('hidden');

    if (state === 'loading') {
      mLoading.classList.remove('hidden');
      document.getElementById('modalLoadingTitle').textContent = opts.title || 'Sedang Memproses...';
      document.getElementById('modalLoadingMsg').textContent   = opts.msg   || 'Mohon tunggu sebentar';
    }
    if (state === 'success') {
      mSuccess.classList.remove('hidden');
      document.getElementById('modalSuccessTitle').textContent    = opts.title    || 'Berhasil!';
      document.getElementById('modalSuccessMsg').textContent      = opts.msg      || '';
      document.getElementById('modalSuccessSubtitle').textContent = opts.subtitle || '';
      const failedInfo  = document.getElementById('modalFailedInfo');
      const failedNames = document.getElementById('modalFailedNames');
      if (opts.failedNames?.length) {
        failedInfo.classList.remove('hidden');
        failedNames.textContent = opts.failedNames.join(', ');
      } else {
        failedInfo.classList.add('hidden');
      }
    }
    if (state === 'error') {
      mError.classList.remove('hidden');
      document.getElementById('modalErrorMsg').textContent = opts.msg || 'Terjadi kesalahan.';
    }
  }

  function hideModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  document.getElementById('btnModalClose')?.addEventListener('click', hideModal);
  document.getElementById('btnModalCloseErr')?.addEventListener('click', hideModal);
  modal.addEventListener('click', e => { if (e.target === modal) hideModal(); });

  // ── Helper: kumpulkan FormData dari rekomendasiForm ───────────────
  function buildRekomendasiFormData(extra = {}) {
    const form     = document.getElementById('rekomendasiForm');
    const formData = new FormData(form);
    const logoFile  = document.getElementById('f_logo')?.files[0];
    const stampFile = document.getElementById('f_stamp')?.files[0];
    if (logoFile)  formData.set('logo',  logoFile);
    if (stampFile) formData.set('stamp', stampFile);
    for (const [k, v] of Object.entries(extra)) formData.set(k, v);
    return formData;
  }

  // ── Simpan Perubahan Template ─────────────────────────────────────
  document.getElementById('btnSaveTemplate')?.addEventListener('click', async function () {
    showModal('loading', { title: 'Menyimpan Template...', msg: 'Mohon tunggu' });

    try {
      const res  = await fetch(SAVE_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: buildRekomendasiFormData(),
        credentials: 'same-origin',
      });
      const json = await res.json();

      if (json.success) {
        showModal('success', {
          title:    'Template Tersimpan!',
          msg:      json.message || 'Template berhasil disimpan.',
          subtitle: 'Perubahan akan digunakan pada generate berikutnya.',
        });
      } else {
        showModal('error', { msg: json.message || 'Gagal menyimpan template.' });
      }
    } catch {
      showModal('error', { msg: 'Terjadi kesalahan jaringan. Silakan coba lagi.' });
    }
  });

  // ── Kirim Semua (rekomendasi + grup alumni + info kerja) ─────────
  document.getElementById('btnKirimSemua')?.addEventListener('click', async function () {
    const targetLabel = ALL_BRAND_MODE && INTERN_BRAND
      ? `semua pemagang brand "${INTERN_BRAND}"`
      : 'pemagang ini';

    if (!confirm(
      `Kirim semua ke ${targetLabel}?\n\n` +
      `• Surat rekomendasi akan digenerate\n` +
      `• Link grup alumni akan disimpan\n` +
      `• Info kerja akan disimpan\n\n` +
      `Pemagang dapat mengakses semuanya di halaman Dokumen mereka.`
    )) return;

    showModal('loading', {
      title: ALL_BRAND_MODE ? 'Mengirim ke Semua Pemagang...' : 'Mengirim...',
      msg:   'Membuat surat rekomendasi dan menyimpan informasi alumni...',
    });

    try {
      // Gabungkan data rekomendasi + alumni + info kerja dalam satu FormData
      const formData = buildRekomendasiFormData({
        mode: ALL_BRAND_MODE ? 'all_brand' : 'single',
      });

      // Ambil nilai link alumni & info kerja dari field form bawah
      const alumniUrl   = document.getElementById('inp_alumni_group_url')?.value   || '';
      const alumniLabel = document.getElementById('inp_alumni_group_label')?.value  || '';
      const jobUrl      = document.getElementById('inp_job_info_url')?.value        || '';
      const jobDesc     = document.getElementById('inp_job_info_description')?.value || '';

      if (alumniUrl)   formData.set('alumni_group_url',   alumniUrl);
      if (alumniLabel) formData.set('alumni_group_label', alumniLabel);
      if (jobUrl)      formData.set('job_info_url',       jobUrl);
      if (jobDesc)     formData.set('job_info_description', jobDesc);

      const res  = await fetch(SEND_ALL_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
        credentials: 'same-origin',
      });
      const json = await res.json();

      if (json.success || json.generated > 0) {
        showModal('success', {
          title:       'Berhasil Dikirim!',
          msg:         json.message,
          subtitle:    'Pemagang dapat mengakses surat dan informasi di halaman Dokumen mereka.',
          failedNames: json.failed_names || [],
        });
        document.getElementById('btnModalClose').addEventListener('click', () => location.reload(), { once: true });
      } else {
        showModal('error', { msg: json.message || 'Gagal mengirim ke pemagang.' });
      }
    } catch (err) {
      showModal('error', { msg: 'Terjadi kesalahan jaringan. Silakan coba lagi.' });
    }
  });

})();
</script>
@endsection
