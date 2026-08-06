@php
  $val = fn($key, $default = '') => old($key, $webinar?->$key ?? $default);
  $inp = 'w-full rounded-lg border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-sm text-[#1B3A34] focus:outline-none focus:ring-2 focus:ring-[#2D8659] focus:border-[#2D8659]';
@endphp

{{-- ===== INFO WEBINAR ===== --}}
<div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm mb-5">
  <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-4">Informasi Webinar</p>

  <div class="space-y-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">
        Judul Webinar <span class="text-red-500">*</span>
      </label>
      <input type="text" name="title" required value="{{ $val('title') }}"
             placeholder="Contoh: Workshop UI/UX Design for Beginners"
             class="{{ $inp }}">
    </div>

    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Deskripsi</label>
      <textarea name="description" rows="3" placeholder="Deskripsi singkat webinar..."
                class="{{ $inp }} resize-none">{{ $val('description') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">
          Tanggal & Waktu Mulai <span class="text-red-500">*</span>
        </label>
        <input type="datetime-local" name="event_date" required
               value="{{ old('event_date', $webinar ? $webinar->event_date->format('Y-m-d\TH:i') : '') }}"
               class="{{ $inp }}">
      </div>
      <div>
        <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Tanggal & Waktu Selesai</label>
        <input type="datetime-local" name="event_end_date"
               value="{{ old('event_end_date', $webinar?->event_end_date?->format('Y-m-d\TH:i') ?? '') }}"
               class="{{ $inp }}">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Link Meeting / Zoom</label>
        <input type="url" name="zoom_link" value="{{ $val('zoom_link') }}"
               placeholder="https://zoom.us/j/..."
               class="{{ $inp }}">
      </div>
      <div>
        <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Platform</label>
        <select name="platform" class="{{ $inp }}">
          @foreach(['Zoom','Google Meet','Microsoft Teams','YouTube Live','Lainnya'] as $p)
            <option value="{{ $p }}" @selected($val('platform','Zoom') === $p)>{{ $p }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <input type="checkbox" name="is_active" value="1" id="is_active"
             style="accent-color:#2D8659;width:16px;height:16px;"
             @checked(old('is_active', $webinar?->is_active ?? true))>
      <label for="is_active" class="text-sm text-[#1B3A34] cursor-pointer">
        Publikasikan sekarang (pemagang aktif akan melihat webinar ini)
      </label>
    </div>
  </div>
</div>

{{-- ===== PENGATURAN SERTIFIKAT ===== --}}
<div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm mb-5">
  <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-1">Pengaturan Sertifikat</p>
  <p class="text-xs text-[#4B5F5A] mb-4">Konfigurasi ini akan dipakai saat generate sertifikat kehadiran webinar.</p>

  <div class="grid grid-cols-2 gap-4 mb-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Perusahaan Penerbit</label>
      <input type="text" name="certificate_company"
             value="{{ $val('certificate_company', 'Seven Inc') }}"
             class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Kota</label>
      <input type="text" name="certificate_city"
             value="{{ $val('certificate_city', 'Yogyakarta') }}"
             class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Brand</label>
      <select name="certificate_brand" class="{{ $inp }}">
        @foreach($brands as $code => $label)
          <option value="{{ $code }}" @selected($val('certificate_brand', 'SI') === $code)>
            {{ $label }} ({{ $code }})
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid grid-cols-3 gap-4 mb-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Background</label>
      <select name="certificate_background" class="{{ $inp }}">
        <option value="">— Tidak ada —</option>
        @foreach($assetOptions['bg'] as $f)
          <option value="{{ $f }}" @selected($val('certificate_background') === $f)>{{ $f }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Logo 1</label>
      <select name="certificate_logo1" class="{{ $inp }}">
        <option value="">— Tidak ada —</option>
        @foreach($assetOptions['logo'] as $f)
          <option value="{{ $f }}" @selected($val('certificate_logo1') === $f)>{{ $f }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Logo 2 (opsional)</label>
      <select name="certificate_logo2" class="{{ $inp }}">
        <option value="">— Tidak ada —</option>
        @foreach($assetOptions['logo'] as $f)
          <option value="{{ $f }}" @selected($val('certificate_logo2') === $f)>{{ $f }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Tanda Tangan 1</label>
      <select name="certificate_signature1" class="{{ $inp }}">
        <option value="">— Tidak ada —</option>
        @foreach($assetOptions['sig'] as $f)
          <option value="{{ $f }}" @selected($val('certificate_signature1') === $f)>{{ $f }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Tanda Tangan 2 (opsional)</label>
      <select name="certificate_signature2" class="{{ $inp }}">
        <option value="">— Tidak ada —</option>
        @foreach($assetOptions['sig'] as $f)
          <option value="{{ $f }}" @selected($val('certificate_signature2') === $f)>{{ $f }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Nama Penandatangan 1</label>
      <input type="text" name="certificate_signatory1_name"
             value="{{ $val('certificate_signatory1_name') }}"
             placeholder="Nama penandatangan" class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Jabatan Penandatangan 1</label>
      <input type="text" name="certificate_signatory1_role"
             value="{{ $val('certificate_signatory1_role') }}"
             placeholder="Contoh: CEO / Penyelenggara" class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Nama Penandatangan 2 (opsional)</label>
      <input type="text" name="certificate_signatory2_name"
             value="{{ $val('certificate_signatory2_name') }}"
             class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Jabatan Penandatangan 2 (opsional)</label>
      <input type="text" name="certificate_signatory2_role"
             value="{{ $val('certificate_signatory2_role') }}"
             class="{{ $inp }}">
    </div>
  </div>
</div>
