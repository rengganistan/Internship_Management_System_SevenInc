@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.intern_extras.index') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#2D8659] transition">
      <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-0.5">Akses Eksklusif</p>
      <h1 class="text-xl font-extrabold text-[#1B3A34]">{{ $intern->fullname }}</h1>
      <p class="text-sm text-[#4B5F5A]">{{ $intern->internship_interest }} · {{ $intern->institution_name }}</p>
    </div>
  </div>

  <div class="mb-4 rounded-lg border border-[#DCE7E1] bg-white px-4 py-3 text-sm text-[#4B5F5A] shadow-sm">
    Admin memilih secara manual pemagang yang berhak mendapat surat rekomendasi, grup alumni, dan info kerja. Tidak semua pemagang yang sudah selesai otomatis memiliki akses.
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">{!! session('success') !!}</div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      <p class="font-semibold mb-2">Perbaiki field berikut:</p>
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="generateRekomendasiForm" method="POST" action="{{ route('admin.rekomendasi.generate', $intern->id) }}" class="hidden">
    @csrf
  </form>

  <form id="deleteRekomendasiForm" method="POST" action="{{ route('admin.intern_extras.rekomendasi.destroy', $intern->id) }}" class="hidden">
    @csrf
    @method('DELETE')
  </form>

  <form action="{{ route('admin.intern_extras.update', $intern->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm">
      @if($extra->rekomendasi_path)
          <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg mb-4">
            <div class="flex items-center gap-2">
              <i class="fas fa-file-pdf text-green-600"></i>
              <span class="text-sm text-green-800 font-medium">Surat sudah di-generate</span>
              <span class="text-xs text-green-600">· {{ $extra->rekomendasi_granted_at?->format('d M Y') }}</span>
            </div>
            <div class="flex items-center gap-3">
              <a href="{{ route('admin.documents.serve', ['type' => 'rekomendasi', 'filename' => basename($extra->rekomendasi_path)]) }}"
                 target="_blank"
                 class="text-xs font-semibold text-[#2D8659] hover:underline">Lihat</a>
              <button type="submit" form="deleteRekomendasiForm"
                      class="text-xs font-semibold text-red-600 hover:underline"
                      onclick="return confirm('Hapus surat rekomendasi ini?')">
                Hapus
              </button>
            </div>
          </div>
        @endif

        {{-- Tombol generate ulang --}}
        <button type="submit"
          form="generateRekomendasiForm"
          class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold text-white rounded-lg"
          style="background-color:#2D8659;"
          onclick="return confirm('Generate surat rekomendasi untuk {{ addslashes($intern->fullname) }}?')">
          <i class="fas fa-file-pdf text-xs"></i>
          {{ $extra->rekomendasi_path ? 'Generate Ulang PDF' : 'Generate Surat Rekomendasi' }}
        </button>

        <p class="text-[11px] text-[#4B5F5A] mt-2 text-center">
          Template dikelola di
          <a href="{{ route('admin.rekomendasi.editor') }}" class="text-[#2D8659] hover:underline font-semibold">Template Rekomendasi</a>.
        </p>
      </div>

      {{-- Link Grup Alumni --}}
      <div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center text-purple-700">
            <i class="fas fa-users"></i>
          </div>
          <h3 class="font-bold text-[#1B3A34]">Link Grup Alumni</h3>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">URL Grup <span class="text-gray-400 text-xs">(WhatsApp/Telegram/dll)</span></label>
            <input type="url" name="alumni_group_url" value="{{ old('alumni_group_url', $extra->alumni_group_url) }}"
                   placeholder="https://chat.whatsapp.com/..."
                   class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">Label Tombol</label>
            <input type="text" name="alumni_group_label" value="{{ old('alumni_group_label', $extra->alumni_group_label) }}"
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
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">URL Info Kerja</label>
            <input type="url" name="job_info_url" value="{{ old('job_info_url', $extra->job_info_url) }}"
                   placeholder="https://..."
                   class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659]">
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1B3A34] mb-1.5">Deskripsi singkat <span class="text-gray-400 text-xs">(opsional)</span></label>
            <textarea name="job_info_description" rows="2" placeholder="Contoh: Lowongan Full Stack Developer di partner Seveninc..."
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

    <div class="mt-5 flex gap-3">
      <button type="submit"
              class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl"
              style="background-color:#2D8659;">
        Simpan Perubahan
      </button>
      <a href="{{ route('admin.intern_extras.index') }}"
         class="px-6 py-2.5 text-sm font-semibold text-[#4B5F5A] border border-[#DCE7E1] bg-white rounded-xl hover:bg-[#F4F8F6] transition">
        Batal
      </a>
    </div>

  </form>
</div>
@endsection
