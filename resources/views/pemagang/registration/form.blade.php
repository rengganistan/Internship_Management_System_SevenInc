@extends('pemagang.layouts.app')

@section('title', 'Daftar Magang')
@section('breadcrumb', 'Pendaftaran')

@section('content')

@php
  $reg   = $registration ?? null;
  $old   = fn($field) => old($field, $reg?->$field ?? '');
  $settings = $settings ?? \App\Models\FormSetting::getInternshipFields();
  $fieldActive = fn(string $key, bool $default = true) => (bool) ($settings[$key]['is_active'] ?? $default);
  $fieldRequired = fn(string $key, bool $default = false) => (bool) ($settings[$key]['is_required'] ?? $default);
  $label = 'block mb-1.5 text-sm font-medium text-gray-700';
  $input = 'block w-full rounded-lg border border-gray-200 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 px-3 py-2.5 text-sm';
  $radio = 'w-4 h-4 text-green-600 border-gray-300 focus:ring-2 focus:ring-green-500';
  $check = $radio;
  $item  = 'flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition cursor-pointer';
  $group = 'border border-gray-200 rounded-lg divide-y divide-gray-100 overflow-hidden';

  // Normalize tanggal ke format Y-m-d untuk input type="date"
  $toDateInput = function(string $val): string {
    if ($val === '') return '';
    // Sudah format Y-m-d
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
    // Coba parse dengan Carbon
    try { return \Carbon\Carbon::parse($val)->format('Y-m-d'); }
    catch (\Throwable) { return ''; }
  };
@endphp

<div class="max-w-2xl mx-auto">
  <div class="bg-white rounded-xl border border-gray-100 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Form Pendaftaran Magang</h2>
    <p class="text-sm text-gray-500 mb-6">Lengkapi data berikut untuk mendaftar program magang Seveninc</p>

    @if($reg && $reg->is_draft)
      <div class="mb-5 flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-save"></i>
        Draft tersimpan {{ $reg->draft_saved_at ? \Carbon\Carbon::parse($reg->draft_saved_at)->diffForHumans() : '' }}. Lanjutkan dan kirim pendaftaran Anda.
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

      @if($fieldActive('fullname') || $fieldActive('student_id') || $fieldActive('born_date') || $fieldActive('email') || $fieldActive('phone_number') || $fieldActive('gender') || $fieldActive('institution_name') || $fieldActive('study_program') || $fieldActive('faculty') || $fieldActive('current_city'))
      {{-- Data Pribadi & Akademik --}}
      @if($fieldActive('fullname'))
      <div>
        <label class="{{ $label }}">Nama Lengkap @if($fieldRequired('fullname'))<span class="text-red-500">*</span>@endif</label>
        <input type="text" name="fullname" @if($fieldRequired('fullname')) required @endif placeholder="Muhammad Sumbul"
          class="{{ $input }}" value="{{ $old('fullname') }}">
      </div>
      @endif

      @if($fieldActive('student_id') || $fieldActive('born_date'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('student_id'))
        <div>
          <label class="{{ $label }}">NIM / NPM @if($fieldRequired('student_id'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="student_id" @if($fieldRequired('student_id')) required @endif placeholder="21552011045"
            pattern="[0-9A-Za-z\-]+" inputmode="text"
            class="{{ $input }}" value="{{ $old('student_id') }}">
          <p class="mt-1 text-xs text-gray-400">Contoh: 21552011045</p>
        </div>
        @endif
        @if($fieldActive('born_date'))
        <div>
          <label class="{{ $label }}">Tanggal Lahir @if($fieldRequired('born_date'))<span class="text-red-500">*</span>@endif</label>
          <input type="date" name="born_date" @if($fieldRequired('born_date')) required @endif
            max="{{ date('Y-m-d') }}"
            class="{{ $input }}" value="{{ $toDateInput($old('born_date')) }}">
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('institution_name') || $fieldActive('study_program'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('institution_name'))
        <div>
          <label class="{{ $label }}">Universitas @if($fieldRequired('institution_name'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="institution_name" @if($fieldRequired('institution_name')) required @endif placeholder="Telkom University"
            class="{{ $input }}" value="{{ $old('institution_name') }}">
        </div>
        @endif
        @if($fieldActive('study_program'))
        <div>
          <label class="{{ $label }}">Program Studi @if($fieldRequired('study_program'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="study_program" @if($fieldRequired('study_program')) required @endif placeholder="Rekayasa Perangkat Lunak"
            class="{{ $input }}" value="{{ $old('study_program') }}">
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('faculty') || $fieldActive('current_city'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('faculty'))
        <div>
          <label class="{{ $label }}">Fakultas @if($fieldRequired('faculty'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="faculty" @if($fieldRequired('faculty')) required @endif placeholder="Ilmu Komputer"
            class="{{ $input }}" value="{{ $old('faculty') }}">
        </div>
        @endif
        @if($fieldActive('current_city'))
        <div>
          <label class="{{ $label }}">Kota Domisili @if($fieldRequired('current_city'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="current_city" @if($fieldRequired('current_city')) required @endif placeholder="Yogyakarta"
            class="{{ $input }}" value="{{ $old('current_city') }}">
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('email') || $fieldActive('phone_number'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('email'))
        <div>
          <label class="{{ $label }}">Email @if($fieldRequired('email'))<span class="text-red-500">*</span>@endif</label>
          <input type="email" name="email" @if($fieldRequired('email')) required @endif placeholder="kamu@email.com"
            class="{{ $input }}" value="{{ $old('email') }}">
        </div>
        @endif
        @if($fieldActive('phone_number'))
        <div>
          <label class="{{ $label }}">No. HP (WhatsApp) @if($fieldRequired('phone_number'))<span class="text-red-500">*</span>@endif</label>
          <input type="tel" name="phone_number" @if($fieldRequired('phone_number')) required @endif placeholder="08xxxxxxxxxx"
            pattern="[0-9]{10,15}" inputmode="numeric" title="Hanya boleh angka, 10-15 digit"
            class="{{ $input }}" value="{{ $old('phone_number') }}">
          <p class="mt-1 text-xs text-gray-400">Hanya angka, contoh: 08123456789</p>
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('gender'))
      <div>
        <label class="{{ $label }}">Jenis Kelamin @if($fieldRequired('gender'))<span class="text-red-500">*</span>@endif</label>
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
      @endif
      @endif

      @if($fieldActive('internship_interest'))
      <div>
        <label class="{{ $label }}">Divisi Diminati @if($fieldRequired('internship_interest'))<span class="text-red-500">*</span>@endif</label>
        <select name="internship_interest" @if($fieldRequired('internship_interest')) required @endif class="{{ $input }}">
          <option value="">-- Pilih Divisi --</option>
          @foreach($divisions ?? [] as $div)
            <option value="{{ $div }}" @selected($old('internship_interest') === $div)>
              {{ $div }}
            </option>
          @endforeach
        </select>
      </div>
      @endif

      @if($fieldActive('start_date') || $fieldActive('end_date') || $fieldActive('internship_type') || $fieldActive('internship_arrangement') || $fieldActive('internship_reason') || $fieldActive('current_status') || $fieldActive('english_book_ability') || $fieldActive('design_software') || $fieldActive('programming_languages') || $fieldActive('video_software'))
      {{-- Durasi & Informasi Magang --}}
      @if($fieldActive('start_date') || $fieldActive('end_date'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('start_date'))
        <div>
          <label class="{{ $label }}">Tanggal Mulai @if($fieldRequired('start_date'))<span class="text-red-500">*</span>@endif</label>
          <input type="date" name="start_date" @if($fieldRequired('start_date')) required @endif
            min="{{ date('Y-m-d') }}"
            class="{{ $input }}" value="{{ $toDateInput($old('start_date')) }}">
        </div>
        @endif
        @if($fieldActive('end_date'))
        <div>
          <label class="{{ $label }}">Tanggal Selesai @if($fieldRequired('end_date'))<span class="text-red-500">*</span>@endif</label>
          <input type="date" name="end_date" @if($fieldRequired('end_date')) required @endif
            class="{{ $input }}" value="{{ $toDateInput($old('end_date')) }}">
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('internship_type') || $fieldActive('internship_arrangement'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('internship_type'))
        <div>
          <label class="{{ $label }}">Jenis Magang @if($fieldRequired('internship_type'))<span class="text-red-500">*</span>@endif</label>
          <div class="space-y-3">
            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="internship_type" value="Magang Mitra" class="mt-1 {{ $radio }}" @checked($old('internship_type') === 'Magang Mitra')>
              <span>
                <span class="block text-sm font-medium text-gray-800">Magang Mitra</span>
                <span class="block text-xs text-gray-500">Magang melalui kerja sama kampus dengan perusahaan berdasarkan rekomendasi atau penempatan dari kampus.</span>
              </span>
            </label>
            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="internship_type" value="Magang Reguler (Mandiri)" class="mt-1 {{ $radio }}" @checked($old('internship_type') === 'Magang Reguler (Mandiri)')>
              <span>
                <span class="block text-sm font-medium text-gray-800">Magang Reguler (Mandiri)</span>
                <span class="block text-xs text-gray-500">Magang dari kampus yang dipilih dan diajukan sendiri oleh pemagang, serta digunakan untuk pemenuhan atau penilaian akademik.</span>
              </span>
            </label>
            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="internship_type" value="Magang Inisiatif Pribadi" class="mt-1 {{ $radio }}" @checked($old('internship_type') === 'Magang Inisiatif Pribadi')>
              <span>
                <span class="block text-sm font-medium text-gray-800">Magang Inisiatif Pribadi</span>
                <span class="block text-xs text-gray-500">Magang atas inisiatif sendiri tanpa rekomendasi atau kerja sama khusus dari kampus, kemauan sendiri.</span>
              </span>
            </label>
          </div>
        </div>
        @endif
        @if($fieldActive('internship_arrangement'))
        <div>
          <label class="{{ $label }}">Sistem Magang @if($fieldRequired('internship_arrangement'))<span class="text-red-500">*</span>@endif</label>
          <select name="internship_arrangement" @if($fieldRequired('internship_arrangement')) required @endif class="{{ $input }}">
            <option value="Onsite" @selected($old('internship_arrangement') === 'Onsite')>Onsite (WFO)</option>
          </select>
        </div>
        @endif
      </div>
      @endif

      @if($fieldActive('internship_reason'))
      <div>
        <label class="{{ $label }}">Alasan Ingin Magang di Sini @if($fieldRequired('internship_reason'))<span class="text-red-500">*</span>@endif</label>
        <textarea name="internship_reason" @if($fieldRequired('internship_reason')) required @endif rows="3" placeholder="Tuliskan alasan Anda..."
          class="{{ $input }} resize-none">{{ $old('internship_reason') }}</textarea>
      </div>
      @endif

      @if($fieldActive('current_status'))
      <div>
        <label class="{{ $label }}">Status Saat Ini @if($fieldRequired('current_status'))<span class="text-red-500">*</span>@endif</label>
        <select name="current_status" @if($fieldRequired('current_status')) required @endif class="{{ $input }}">
          <option value="">-- Pilih --</option>
          <option value="Mahasiswa/Pelajar" @selected($old('current_status') === 'Mahasiswa/Pelajar')>Masih Kuliah/Sekolah</option>
          <option value="Tidak Bekerja" @selected($old('current_status') === 'Tidak Bekerja')>Lulus & Belum Bekerja</option>
          <option value="Karyawan" @selected($old('current_status') === 'Karyawan')>Lulus & Sudah Bekerja</option>
        </select>
      </div>
      @endif

      @if($fieldActive('english_book_ability'))
      <div>
        <label class="{{ $label }}">Kemampuan Membaca Buku Bahasa Inggris @if($fieldRequired('english_book_ability'))<span class="text-red-500">*</span>@endif</label>
        <select name="english_book_ability" @if($fieldRequired('english_book_ability')) required @endif class="{{ $input }}">
          <option value="">-- Pilih --</option>
          <option value="Saya bisa" @selected($old('english_book_ability') === 'Saya bisa')>Saya bisa</option>
          <option value="Kurang bisa" @selected($old('english_book_ability') === 'Kurang bisa')>Kurang bisa</option>
          <option value="Tidak bisa" @selected($old('english_book_ability') === 'Tidak bisa')>Tidak bisa</option>
        </select>
      </div>
      @endif

      @if($fieldActive('design_software') || $fieldActive('programming_languages') || $fieldActive('video_software'))
      <div class="grid grid-cols-3 gap-4">
        @if($fieldActive('design_software'))
        <div>
          <label class="{{ $label }}">Software Desain @if($fieldRequired('design_software'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="design_software" placeholder="Figma, Photoshop"
            class="{{ $input }}" value="{{ $old('design_software') }}">
        </div>
        @endif
        @if($fieldActive('programming_languages'))
        <div>
          <label class="{{ $label }}">Bahasa Pemrograman @if($fieldRequired('programming_languages'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="programming_languages" placeholder="PHP, JS"
            class="{{ $input }}" value="{{ $old('programming_languages') }}">
        </div>
        @endif
        @if($fieldActive('video_software'))
        <div>
          <label class="{{ $label }}">Materi Digital Marketing @if($fieldRequired('video_software'))<span class="text-red-500">*</span>@endif</label>
          <input type="text" name="video_software" placeholder="SEO, Ads"
            class="{{ $input }}" value="{{ $old('video_software') }}">
        </div>
        @endif
      </div>
      @endif
      @endif

      @if($fieldActive('cv_ktp_portofolio_pdf') || $fieldActive('portofolio_visual'))
      <div class="grid grid-cols-2 gap-4">
        @if($fieldActive('cv_ktp_portofolio_pdf'))
        <div>
          <label class="{{ $label }}">Dokumen Pendukung (PDF) @if($fieldRequired('cv_ktp_portofolio_pdf'))<span class="text-red-500">*</span>@endif</label>
          <input type="file" name="cv_ktp_portofolio_pdf" accept=".pdf"
            class="{{ $input }} file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:text-white cursor-pointer"
            style="--file-bg: #1a5c38;">
          @if($reg?->cv_ktp_portofolio_pdf)
            <p class="text-xs text-gray-400 mt-1">
              File sebelumnya: {{ basename($reg->cv_ktp_portofolio_pdf) }}
            </p>
          @endif
        </div>
        @endif
        @if($fieldActive('portofolio_visual'))
        <div>
          <label class="{{ $label }}">CV / Portfolio @if($fieldRequired('portofolio_visual'))<span class="text-red-500">*</span>@endif</label>
          <input type="file" name="portofolio_visual" accept=".pdf,.jpg,.jpeg,.png"
            class="{{ $input }} file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:text-white cursor-pointer">
          @if($reg?->portofolio_visual)
            <p class="text-xs text-gray-400 mt-1">
              File sebelumnya: {{ basename($reg->portofolio_visual) }}
            </p>
          @endif
        </div>
        @endif
      </div>
      @if($fieldActive('cv_ktp_portofolio_pdf') || $fieldActive('portofolio_visual'))
      <p class="text-xs text-gray-400 -mt-3">Upload dokumen bersifat opsional kecuali jika admin mengaktifkan dan menjadikannya wajib.</p>
      @endif
      @endif

      {{-- Hidden fields dengan nilai default yang tidak tampil di form --}}
      <input type="hidden" name="supervisor_contact" value="-">
      <input type="hidden" name="current_activities" value="-">

      {{-- ===== INFORMASI TAMBAHAN ===== --}}
      @if($fieldActive('boarding_info') || $fieldActive('parent_wa_contact') || $fieldActive('social_media_instagram') || $fieldActive('internship_info_sources'))
      <div class="border-t border-gray-100 pt-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Tambahan</h3>
        <div class="space-y-4">

          @if($fieldActive('boarding_info'))
          <div>
            <label class="{{ $label }}">Butuh Informasi Kost? @if($fieldRequired('boarding_info'))<span class="text-red-500">*</span>@endif</label>
            <select name="boarding_info" @if($fieldRequired('boarding_info')) required @endif class="{{ $input }}">
              <option value="Tidak" @selected(($old('boarding_info') ?: 'Tidak') === 'Tidak')>Tidak</option>
              <option value="Ya"    @selected($old('boarding_info') === 'Ya')>Ya</option>
            </select>
          </div>
          @endif

          @if($fieldActive('parent_wa_contact'))
          <div>
            <label class="{{ $label }}">No. WA Wali / Orang Tua @if($fieldRequired('parent_wa_contact'))<span class="text-red-500">*</span>@endif</label>
            <input type="tel" name="parent_wa_contact" placeholder="08xxxxxxxxxx"
              pattern="[0-9]*" inputmode="numeric" title="Hanya boleh angka"
              class="{{ $input }}" value="{{ $old('parent_wa_contact', $reg?->parent_wa_contact !== '-' ? $reg?->parent_wa_contact : '') }}">
            <p class="mt-1 text-xs text-gray-400">Hanya angka, opsional</p>
          </div>
          @endif

          @if($fieldActive('social_media_instagram'))
          <div>
            <label class="{{ $label }}">Instagram @if($fieldRequired('social_media_instagram'))<span class="text-red-500">*</span>@endif</label>
            <div class="flex items-center gap-0">
              <span class="inline-flex items-center px-3 py-2.5 rounded-l-lg border border-r-0 border-gray-200 bg-gray-50 text-sm text-gray-500">@</span>
              <input type="text" name="social_media_instagram" placeholder="username_kamu"
                class="block flex-1 rounded-r-lg border border-gray-200 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 px-3 py-2.5 text-sm"
                value="{{ $old('social_media_instagram', $reg?->social_media_instagram !== '-' ? $reg?->social_media_instagram : '') }}">
            </div>
          </div>
          @endif

          @if($fieldActive('internship_info_sources'))
          <div>
            <label class="{{ $label }}">Tahu Info Magang Dari @if($fieldRequired('internship_info_sources'))<span class="text-red-500">*</span>@endif</label>
            <div class="{{ $group }}">
              @php
                $infoSources = $old('internship_info_sources', $reg?->internship_info_sources ?? '');
                $infoSourcesArr = is_array($infoSources)
                    ? $infoSources
                    : array_map('trim', explode(',', (string) $infoSources));
              @endphp
              @foreach([
                'Instagram'           => 'Instagram',
                'TikTok'              => 'TikTok',
                'LinkedIn'            => 'LinkedIn',
                'Referral Teman'      => 'Referral Teman',
                'Website'             => 'Website',
                'Campus'              => 'Kampus/Universitas',
                'Lainnya'             => 'Lainnya',
              ] as $val => $lbl)
              <label class="{{ $item }}">
                <input type="checkbox" name="internship_info_sources[]" value="{{ $val }}" class="{{ $check }}"
                  @checked(in_array($val, $infoSourcesArr))>
                <span class="text-sm text-gray-700">{{ $lbl }}</span>
              </label>
              @endforeach
            </div>
          </div>
          @endif

        </div>
      </div>
      @endif

      {{-- Info unpaid --}}
      @if(!$registration || $registration->is_draft || $registration->internship_status === 'waiting')
      <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
        <strong>Perhatian:</strong> Program magang ini bersifat <strong>unpaid / tidak bergaji</strong>.
        Setelah submit, konfirmasi ke WA Admin <strong>0895 2900 2944</strong> dengan pesan
        <em>"SAYA SUDAH ISI FORM"</em>.
      </div>
      @endif

      {{-- Tombol --}}
      <div class="flex items-center gap-3 pt-2">
        @if(!$registration || $registration->is_draft)
          {{-- Belum submit → tombol kirim + draft --}}
          <button type="submit"
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg"
            style="background-color:#1a5c38;">
            <i class="fas fa-paper-plane text-xs"></i>
            Kirim Pendaftaran
          </button>
          <button type="button" id="btn-save-local"
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            <i class="fas fa-save text-xs"></i>
            Simpan Sementara
          </button>
        @else
          {{-- Sudah submit → hanya bisa update data --}}
          <button type="submit"
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg"
            style="background-color:#1a5c38;">
            <i class="fas fa-save text-xs"></i>
            Simpan Perubahan Data
          </button>
          <a href="{{ route('pemagang.dashboard') }}"
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Kembali
          </a>
        @endif
      </div>

    </form>
  </div>
</div>

@push('scripts')
<script>
  // ===== Validasi real-time field numerik =====
  function setupNumericValidation(selector, message) {
    document.querySelectorAll(selector).forEach(function(input) {
      // Buat elemen pesan error
      const msg = document.createElement('p');
      msg.className = 'text-xs text-red-500 mt-1 hidden';
      msg.textContent = message;
      input.parentNode.appendChild(msg);

      input.addEventListener('input', function() {
        const val = this.value.replace(/\s/g, '');
        const hasLetter = /[a-zA-Z]/.test(val);

        if (hasLetter) {
          this.classList.add('border-red-400', 'ring-red-400');
          this.classList.remove('border-gray-200');
          msg.classList.remove('hidden');
        } else {
          this.classList.remove('border-red-400', 'ring-red-400');
          this.classList.add('border-gray-200');
          msg.classList.add('hidden');
        }
      });

      // Blokir huruf saat paste
      input.addEventListener('paste', function(e) {
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        if (/[a-zA-Z]/.test(pasted)) {
          e.preventDefault();
          input.dispatchEvent(new Event('input'));
        }
      });
    });
  }

  // ===== Validasi visual saat submit — highlight field kosong =====
  function setupRequiredHighlight() {
    const form = document.getElementById('form-daftar');
    if (!form) return;

    form.addEventListener('submit', function(e) {
      let hasError = false;

      // Cek semua input/select/textarea yang required
      form.querySelectorAll('[required]').forEach(function(field) {
        const wrapper = field.closest('div');
        let errMsg = wrapper?.querySelector('.field-error-msg');

        if (!field.value.trim()) {
          hasError = true;
          field.classList.add('border-red-400', 'ring-1', 'ring-red-400');
          field.classList.remove('border-gray-200');

          if (!errMsg) {
            errMsg = document.createElement('p');
            errMsg.className = 'field-error-msg text-xs text-red-500 mt-1';
            errMsg.textContent = '⚠ Field ini wajib diisi';
            field.parentNode.appendChild(errMsg);
          }
          errMsg.classList.remove('hidden');
        } else {
          field.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
          field.classList.add('border-gray-200');
          if (errMsg) errMsg.classList.add('hidden');
        }
      });

      // Cek radio groups yang required
      const radioGroups = {};
      form.querySelectorAll('input[type="radio"][required]').forEach(function(r) {
        radioGroups[r.name] = radioGroups[r.name] || [];
        radioGroups[r.name].push(r);
      });

      Object.entries(radioGroups).forEach(function([name, radios]) {
        const checked = radios.some(r => r.checked);
        const container = radios[0].closest('div.border') || radios[0].closest('div');
        let errMsg = container?.parentNode?.querySelector('.radio-error-' + name);

        if (!checked) {
          hasError = true;
          if (!errMsg) {
            errMsg = document.createElement('p');
            errMsg.className = 'radio-error-' + name + ' text-xs text-red-500 mt-1';
            errMsg.textContent = '⚠ Pilih salah satu opsi';
            container?.parentNode?.appendChild(errMsg);
          }
        } else if (errMsg) {
          errMsg.remove();
        }
      });

      if (hasError) {
        e.preventDefault();
        // Scroll ke field error pertama
        const firstError = form.querySelector('.border-red-400');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstError.focus();
        }

        // Tampilkan toast error di atas
        showFormError('Ada beberapa field yang belum diisi. Silakan periksa kembali.');
      }
    });

    // Clear error saat field diisi
    form.querySelectorAll('[required]').forEach(function(field) {
      field.addEventListener('input', function() {
        if (this.value.trim()) {
          this.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
          this.classList.add('border-gray-200');
          const errMsg = this.parentNode.querySelector('.field-error-msg');
          if (errMsg) errMsg.classList.add('hidden');
        }
      });
      field.addEventListener('change', function() {
        field.dispatchEvent(new Event('input'));
      });
    });
  }

  function showFormError(message) {
    let toast = document.getElementById('form-error-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'form-error-toast';
      toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-red-50 border border-red-300 text-red-700 text-sm font-medium px-5 py-3 rounded-xl shadow-lg';
      document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}
      <button onclick="this.parentNode.remove()" class="ml-3 text-red-400 hover:text-red-600">✕</button>`;
    toast.style.display = 'flex';
    setTimeout(() => toast?.remove(), 5000);
  }

  // ===== localStorage: simpan & restore form =====
  const FORM_KEY = 'pemagang_form_{{ auth()->id() }}';

  function saveFormLocal() {
    const form = document.getElementById('form-daftar');
    if (!form) return;
    const data = {};
    new FormData(form).forEach(function(val, key) {
      if (key === '_token') return;
      data[key] = data[key] ? [].concat(data[key], val) : val;
    });
    localStorage.setItem(FORM_KEY, JSON.stringify(data));
    const btn = document.getElementById('btn-save-local');
    if (btn) {
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check text-xs"></i> Tersimpan!';
      btn.style.color = '#16a34a';
      setTimeout(function() { btn.innerHTML = orig; btn.style.color = ''; }, 2000);
    }
  }

  function restoreFormLocal() {
    const saved = localStorage.getItem(FORM_KEY);
    if (!saved) return;
    try {
      const data = JSON.parse(saved);
      const form = document.getElementById('form-daftar');
      if (!form) return;
      Object.entries(data).forEach(function([key, val]) {
        const name = key.replace('[]', '');
        form.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]').forEach(function(el) {
          if (el.type === 'radio') {
            if (el.value === val) el.checked = true;
          } else if (el.type === 'checkbox') {
            if ([].concat(val).includes(el.value)) el.checked = true;
          } else if (el.type !== 'file' && !el.value) {
            el.value = val;
          }
        });
      });
    } catch(e) {}
  }

  document.getElementById('btn-save-local')?.addEventListener('click', saveFormLocal);
  document.getElementById('form-daftar')?.addEventListener('submit', function() {
    localStorage.removeItem(FORM_KEY);
  });

  document.addEventListener('DOMContentLoaded', function() {
    setupRequiredHighlight();

    // Restore dari localStorage kalau fullname masih kosong (form baru)
    const firstInput = document.querySelector('#form-daftar input[name="fullname"]');
    if (firstInput && !firstInput.value) restoreFormLocal();

    setupNumericValidation(
      'input[name="phone_number"]',
      '⚠ No. HP hanya boleh berisi angka (contoh: 08123456789)'
    );
    setupNumericValidation(
      'input[name="parent_wa_contact"]',
      '⚠ No. HP hanya boleh berisi angka'
    );
  });
</script>
@endpush

@endsection
