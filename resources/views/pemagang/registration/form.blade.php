@extends('pemagang.layouts.app')

@section('title', 'Daftar Magang')
@section('breadcrumb', 'Pendaftaran')

@section('content')

@php
  $reg   = $registration ?? null;
  $old   = fn($field) => old($field, $reg?->$field ?? '');
  $label = 'block mb-1.5 text-sm font-medium text-gray-700';
  $input = 'block w-full rounded-lg border border-gray-200 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 px-3 py-2.5 text-sm';
  $radio = 'w-4 h-4 text-green-600 border-gray-300 focus:ring-2 focus:ring-green-500';
  $check = $radio;
  $item  = 'flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition cursor-pointer';
  $group = 'border border-gray-200 rounded-lg divide-y divide-gray-100 overflow-hidden';
@endphp

<div class="max-w-2xl mx-auto">
  <div class="bg-white rounded-xl border border-gray-100 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Form Pendaftaran Magang</h2>
    <p class="text-sm text-gray-500 mb-6">Lengkapi data berikut untuk mendaftar program magang Seveninc</p>

    @if($reg && $reg->is_draft)
      <div class="mb-5 flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-save"></i>
        Draft tersimpan {{ $reg->draft_saved_at?->diffForHumans() ?? '' }}. Lanjutkan dan kirim pendaftaran Anda.
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <strong>Ada kesalahan:</strong>
        <ul class="mt-1 list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="form-daftar" action="{{ route('pemagang.registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
      @csrf

      {{-- Nama Lengkap --}}
      <div>
        <label class="{{ $label }}">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" name="fullname" required placeholder="Muhammad Sumbul"
          class="{{ $input }}" value="{{ $old('fullname') }}">
      </div>

      {{-- NIM / NPM --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">NIM / NPM <span class="text-red-500">*</span></label>
          <input type="text" name="student_id" required placeholder="21552011045"
            class="{{ $input }}" value="{{ $old('student_id') }}">
        </div>
        <div>
          <label class="{{ $label }}">Tanggal Lahir <span class="text-red-500">*</span></label>
          <input type="text" name="born_date" required placeholder="25 Juni 2005"
            class="{{ $input }}" value="{{ $old('born_date') }}">
        </div>
      </div>

      {{-- Universitas & Prodi --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Universitas <span class="text-red-500">*</span></label>
          <input type="text" name="institution_name" required placeholder="Telkom University"
            class="{{ $input }}" value="{{ $old('institution_name') }}">
        </div>
        <div>
          <label class="{{ $label }}">Program Studi <span class="text-red-500">*</span></label>
          <input type="text" name="study_program" required placeholder="Rekayasa Perangkat Lunak"
            class="{{ $input }}" value="{{ $old('study_program') }}">
        </div>
      </div>

      {{-- Fakultas & Kota --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Fakultas <span class="text-red-500">*</span></label>
          <input type="text" name="faculty" required placeholder="Ilmu Komputer"
            class="{{ $input }}" value="{{ $old('faculty') }}">
        </div>
        <div>
          <label class="{{ $label }}">Kota Domisili <span class="text-red-500">*</span></label>
          <input type="text" name="current_city" required placeholder="Yogyakarta"
            class="{{ $input }}" value="{{ $old('current_city') }}">
        </div>
      </div>

      {{-- Email & No HP --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Email <span class="text-red-500">*</span></label>
          <input type="email" name="email" required placeholder="kamu@email.com"
            class="{{ $input }}" value="{{ $old('email') }}">
        </div>
        <div>
          <label class="{{ $label }}">No. HP (WhatsApp) <span class="text-red-500">*</span></label>
          <input type="text" name="phone_number" required placeholder="08xxxxxxxxxx"
            class="{{ $input }}" value="{{ $old('phone_number') }}">
        </div>
      </div>

      {{-- Jenis Kelamin --}}
      <div>
        <label class="{{ $label }}">Jenis Kelamin <span class="text-red-500">*</span></label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="gender" value="Laki-laki" class="{{ $radio }}"
              @checked($old('gender') === 'Laki-laki')> Laki-laki
          </label>
          <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="gender" value="Perempuan" class="{{ $radio }}"
              @checked($old('gender') === 'Perempuan')> Perempuan
          </label>
        </div>
      </div>

      {{-- Divisi Diminati --}}
      <div>
        <label class="{{ $label }}">Divisi Diminati <span class="text-red-500">*</span></label>
        <select name="internship_interest" required class="{{ $input }}">
          <option value="">-- Pilih Divisi --</option>
          @foreach([
            'Project Manager','Administration','Human Resources (HR)',
            'UI/UX','Programmer (Front End / Backend)','Photographer',
            'Videographer','Graphic Designer','Social Media Specialist',
            'Content Writer','Content Planner','Sales & Marketing',
            'Public Relations (Marcomm)','Digital Marketing',
            'TikTok Creator','Welding','Customer Service',
          ] as $div)
            <option value="{{ $div }}" @selected($old('internship_interest') === $div)>
              {{ $div }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Durasi Magang --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Tanggal Mulai</label>
          <input type="text" name="start_date" placeholder="10 September 2025"
            class="{{ $input }}" value="{{ $old('start_date') }}">
        </div>
        <div>
          <label class="{{ $label }}">Tanggal Selesai</label>
          <input type="text" name="end_date" placeholder="10 Desember 2025"
            class="{{ $input }}" value="{{ $old('end_date') }}">
        </div>
      </div>

      {{-- Jenis & Sistem Magang --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Jenis Magang <span class="text-red-500">*</span></label>
          <select name="internship_type" required class="{{ $input }}">
            <option value="">-- Pilih --</option>
            <option value="Magang Mandiri" @selected($old('internship_type') === 'Magang Mandiri')>Magang Mandiri</option>
            <option value="Magang Kampus" @selected($old('internship_type') === 'Magang Kampus')>Magang Kampus</option>
          </select>
        </div>
        <div>
          <label class="{{ $label }}">Sistem Magang <span class="text-red-500">*</span></label>
          <select name="internship_arrangement" required class="{{ $input }}">
            <option value="Onsite" @selected($old('internship_arrangement') === 'Onsite')>Onsite (WFO)</option>
          </select>
        </div>
      </div>

      {{-- Alasan Magang --}}
      <div>
        <label class="{{ $label }}">Alasan Ingin Magang di Sini <span class="text-red-500">*</span></label>
        <textarea name="internship_reason" required rows="3" placeholder="Tuliskan alasan Anda..."
          class="{{ $input }} resize-none">{{ $old('internship_reason') }}</textarea>
      </div>

      {{-- Status Saat Ini --}}
      <div>
        <label class="{{ $label }}">Status Saat Ini <span class="text-red-500">*</span></label>
        <select name="current_status" required class="{{ $input }}">
          <option value="">-- Pilih --</option>
          <option value="Mahasiswa/Pelajar" @selected($old('current_status') === 'Mahasiswa/Pelajar')>Masih Kuliah/Sekolah</option>
          <option value="Tidak Bekerja" @selected($old('current_status') === 'Tidak Bekerja')>Lulus & Belum Bekerja</option>
          <option value="Karyawan" @selected($old('current_status') === 'Karyawan')>Lulus & Sudah Bekerja</option>
        </select>
      </div>

      {{-- Kemampuan Bahasa Inggris --}}
      <div>
        <label class="{{ $label }}">Kemampuan Membaca Buku Bahasa Inggris <span class="text-red-500">*</span></label>
        <select name="english_book_ability" required class="{{ $input }}">
          <option value="">-- Pilih --</option>
          <option value="Saya bisa" @selected($old('english_book_ability') === 'Saya bisa')>Saya bisa</option>
          <option value="Kurang bisa" @selected($old('english_book_ability') === 'Kurang bisa')>Kurang bisa</option>
          <option value="Tidak bisa" @selected($old('english_book_ability') === 'Tidak bisa')>Tidak bisa</option>
        </select>
      </div>

      {{-- Skill Fields --}}
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="{{ $label }}">Software Desain</label>
          <input type="text" name="design_software" placeholder="Figma, Photoshop"
            class="{{ $input }}" value="{{ $old('design_software') }}">
        </div>
        <div>
          <label class="{{ $label }}">Bahasa Pemrograman</label>
          <input type="text" name="programming_languages" placeholder="PHP, JS"
            class="{{ $input }}" value="{{ $old('programming_languages') }}">
        </div>
        <div>
          <label class="{{ $label }}">Materi Digital Marketing</label>
          <input type="text" name="video_software" placeholder="SEO, Ads"
            class="{{ $input }}" value="{{ $old('video_software') }}">
        </div>
      </div>

      {{-- Upload File --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="{{ $label }}">Surat Pengantar (PDF) <span class="text-red-500">*</span></label>
          <input type="file" name="cv_ktp_portofolio_pdf" accept=".pdf"
            class="{{ $input }} file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:text-white cursor-pointer"
            style="--file-bg: #1a5c38;">
          @if($reg?->cv_ktp_portofolio_pdf)
            <p class="text-xs text-gray-400 mt-1">
              File sebelumnya: {{ basename($reg->cv_ktp_portofolio_pdf) }}
            </p>
          @endif
        </div>
        <div>
          <label class="{{ $label }}">CV / Portfolio (PDF) <span class="text-red-500">*</span></label>
          <input type="file" name="portofolio_visual" accept=".pdf,.jpg,.jpeg,.png"
            class="{{ $input }} file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:text-white cursor-pointer">
          @if($reg?->portofolio_visual)
            <p class="text-xs text-gray-400 mt-1">
              File sebelumnya: {{ basename($reg->portofolio_visual) }}
            </p>
          @endif
        </div>
      </div>
      <p class="text-xs text-gray-400 -mt-3">Maks. 2MB per file, format PDF saja</p>

      {{-- Hidden fields dengan nilai default yang tidak tampil di form --}}
      <input type="hidden" name="family_status" value="Tidak">
      <input type="hidden" name="supervisor_contact" value="-">
      <input type="hidden" name="parent_wa_contact" value="-">
      <input type="hidden" name="social_media_instagram" value="-">
      <input type="hidden" name="boarding_info" value="Tidak">
      <input type="hidden" name="current_activities" value="-">

      {{-- Info unpaid --}}
      <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
        <strong>Perhatian:</strong> Program magang ini bersifat <strong>unpaid / tidak bergaji</strong>.
        Setelah submit, konfirmasi ke WA Admin <strong>0895 2900 2944</strong> dengan pesan
        <em>"SAYA SUDAH ISI FORM"</em>.
      </div>

      {{-- Tombol --}}
      <div class="flex items-center gap-3 pt-2">
        <button type="submit"
          class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg"
          style="background-color:#1a5c38;">
          <i class="fas fa-paper-plane text-xs"></i>
          Kirim Pendaftaran
        </button>

        <button type="button" id="btn-draft"
          class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
          <i class="fas fa-save text-xs"></i>
          Simpan sebagai Draft
        </button>
      </div>

    </form>
  </div>
</div>

@push('scripts')
<script>
  // Tombol "Simpan sebagai Draft" — ubah action form ke route draft
  document.getElementById('btn-draft').addEventListener('click', function () {
    const form = document.getElementById('form-daftar');
    form.action = "{{ route('pemagang.registration.draft') }}";
    form.submit();
  });
</script>
@endpush

@endsection
