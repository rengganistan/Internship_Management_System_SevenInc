@php
  $val = fn($key, $default = '') => old($key, $webinar?->$key ?? $default);
  $inp = 'w-full rounded-lg border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-sm text-[#1B3A34] focus:outline-none focus:ring-2 focus:ring-[#2D8659] focus:border-[#2D8659]';

  // Brands
  $allBrands = [
    'MJ'=>'Magangjogja','AK'=>'Areakerja','RW'=>'Republikweb','TS'=>'Titipsini',
    'AP'=>'Ambilpaket','BK'=>'Bikinkepo','BC'=>'Bimbelcerdas.com','LK'=>'Latihankerja.com',
    'LJT'=>'Lowkerjateng.com','LJG'=>'Lowkerjogja.com','PJ'=>'Pijatjogja.com',
    'SB'=>'Sayabantu.com','TV'=>'Titikvisual','TN'=>'Tuantanah','TL'=>'Tukanglas.org',
    'AKI'=>'Adakamar.id','SI'=>'Seven Inc',
  ];

  // Brand yang boleh ikut: dari old() atau model (JSON array), null = semua
  $savedAllowedBrands = old('allowed_brands', $webinar?->allowed_brands ?? null);
  $isAllBrandsAllowed = empty($savedAllowedBrands); // null/kosong = semua

  // Brand sertifikat terpilih (untuk auto-fill)
  $selectedCertBrand = old('certificate_brand', $webinar?->certificate_brand ?? 'SI');
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

    {{-- Brand yang boleh ikut webinar --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">
        Brand yang Boleh Ikut Webinar
        <span class="ml-1 text-[11px] font-normal text-[#4B5F5A]">(kosongkan = semua brand)</span>
      </label>

      {{-- Toggle: semua atau pilih --}}
      <div class="mb-3 flex items-center gap-4">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-[#1B3A34]">
          <input type="radio" name="_allowed_brands_mode" value="all" id="brands_mode_all"
                 @checked($isAllBrandsAllowed) class="accent-[#2D8659]">
          Semua brand boleh ikut
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-[#1B3A34]">
          <input type="radio" name="_allowed_brands_mode" value="specific" id="brands_mode_specific"
                 @checked(!$isAllBrandsAllowed) class="accent-[#2D8659]">
          Pilih brand tertentu
        </label>
      </div>

      {{-- Grid checkbox brand --}}
      <div id="brands_checkbox_wrap"
           class="{{ $isAllBrandsAllowed ? 'hidden' : '' }} rounded-lg border border-[#DCE7E1] bg-[#F4F8F6] p-4">
        <div class="mb-2 flex items-center justify-between">
          <span class="text-xs font-semibold text-[#4B5F5A] uppercase tracking-wide">Pilih brand:</span>
          <button type="button" id="selectAllBrandsBtn"
                  class="text-xs font-semibold text-[#2D8659] hover:underline">Pilih Semua</button>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
          @foreach($allBrands as $code => $label)
          <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-[#DCE7E1] bg-white px-3 py-2 text-sm transition hover:border-[#2D8659]">
            <input type="checkbox" name="allowed_brands[]" value="{{ $code }}"
                   @checked(is_array($savedAllowedBrands) && in_array($code, $savedAllowedBrands))
                   class="brand-checkbox h-4 w-4 rounded accent-[#2D8659]">
            <span class="text-[#1B3A34]">{{ $label }}</span>
            <span class="ml-auto text-[10px] text-[#4B5F5A] font-mono">{{ $code }}</span>
          </label>
          @endforeach
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <input type="checkbox" name="is_active" value="1" id="is_active"
             style="accent-color:#2D8659;width:16px;height:16px;"
             @checked(old('is_active', $webinar?->is_active ?? true))>
      <label for="is_active" class="text-sm text-[#1B3A34] cursor-pointer">
        Publikasikan sekarang (pemagang dari brand yang dipilih akan melihat webinar ini)
      </label>
    </div>
  </div>
</div>

{{-- ===== PENGATURAN SERTIFIKAT ===== --}}
<div class="bg-white rounded-xl border border-[#DCE7E1] p-6 shadow-sm mb-5">
  <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-1">Pengaturan Sertifikat</p>
  <p class="text-xs text-[#4B5F5A] mb-4">Konfigurasi ini akan dipakai saat generate sertifikat kehadiran webinar. Perusahaan penerbit otomatis sesuai brand yang dipilih.</p>

  <div class="grid grid-cols-2 gap-4 mb-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Brand Sertifikat <span class="text-red-500">*</span></label>
      <select id="certBrandSelect" name="certificate_brand" class="{{ $inp }}"
              onchange="updateCertCompany(this.value)">
        <option value="">-- Pilih Brand --</option>
        @foreach($allBrands as $code => $label)
          <option value="{{ $code }}" @selected($selectedCertBrand === $code)>
            {{ $label }}
          </option>
        @endforeach
      </select>
      <p class="mt-1 text-[11px] text-[#4B5F5A]">Perusahaan penerbit di sertifikat = nama brand ini.</p>
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Kota</label>
      <input type="text" name="certificate_city"
             value="{{ $val('certificate_city', 'Yogyakarta') }}"
             class="{{ $inp }}">
    </div>
  </div>

  {{-- Hidden: certificate_company auto-sync dari brand --}}
  <input type="hidden" id="certCompanyHidden" name="certificate_company"
         value="{{ $webinar?->certificate_company ?? ($allBrands[$selectedCertBrand] ?? 'Seven Inc') }}">

  {{-- Macro CSS inline untuk upload zone --}}
  @php
    $uploadZoneCls = 'mt-2 flex items-center gap-2';
    $uploadBtnCls  = 'inline-flex items-center gap-1.5 rounded-lg border border-dashed border-[#2D8659] bg-[#F4F8F6] px-3 py-1.5 text-xs font-semibold text-[#2D8659] cursor-pointer hover:bg-[#e6f2ec] transition';
    $uploadHintCls = 'text-[10px] text-[#4B5F5A]';
  @endphp

  <div class="grid grid-cols-3 gap-4 mb-4">

    {{-- BACKGROUND --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Background</label>
      @if($assetOptions['bg']->isNotEmpty())
        <select name="certificate_background" id="certBg" class="{{ $inp }} mb-1.5"
                onchange="previewAsset('certBg', 'prevCertBg', 'images/backgrounds')">
          <option value="">— Tidak ada —</option>
          @foreach($assetOptions['bg'] as $f)
            <option value="{{ $f }}" @selected($val('certificate_background') === $f)>{{ $f }}</option>
          @endforeach
        </select>
      @else
        <p class="mb-1.5 text-xs text-[#4B5F5A] italic">Belum ada file — upload baru di bawah.</p>
        <input type="hidden" name="certificate_background" value="">
      @endif
      <div class="{{ $uploadZoneCls }}">
        <label for="upload_background" class="{{ $uploadBtnCls }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Upload background baru
        </label>
        <span class="{{ $uploadHintCls }}">JPG/PNG, maks 4MB</span>
      </div>
      <input type="file" id="upload_background" name="upload_background"
             accept="image/jpeg,image/png,image/gif"
             class="hidden" onchange="previewUpload(this,'prevCertBg')">
      <img id="prevCertBg" class="mt-2 max-h-16 w-full rounded-lg object-cover border border-[#DCE7E1] hidden" alt="">
      <p id="upload_background_name" class="mt-1 text-[10px] text-[#2D8659] hidden"></p>
    </div>

    {{-- LOGO 1 --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Logo 1</label>
      @if($assetOptions['logo']->isNotEmpty())
        <select name="certificate_logo1" id="certLogo1" class="{{ $inp }} mb-1.5"
                onchange="previewAsset('certLogo1', 'prevCertLogo1', 'images/logos')">
          <option value="">— Tidak ada —</option>
          @foreach($assetOptions['logo'] as $f)
            <option value="{{ $f }}" @selected($val('certificate_logo1') === $f)>{{ $f }}</option>
          @endforeach
        </select>
      @else
        <p class="mb-1.5 text-xs text-[#4B5F5A] italic">Belum ada file — upload baru di bawah.</p>
        <input type="hidden" name="certificate_logo1" value="">
      @endif
      <div class="{{ $uploadZoneCls }}">
        <label for="upload_logo1" class="{{ $uploadBtnCls }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Upload logo baru
        </label>
        <span class="{{ $uploadHintCls }}">JPG/PNG, maks 2MB</span>
      </div>
      <input type="file" id="upload_logo1" name="upload_logo1"
             accept="image/jpeg,image/png,image/gif"
             class="hidden" onchange="previewUpload(this,'prevCertLogo1')">
      <img id="prevCertLogo1" class="mt-2 max-h-12 rounded-lg border border-[#DCE7E1] hidden" alt="">
      <p id="upload_logo1_name" class="mt-1 text-[10px] text-[#2D8659] hidden"></p>
    </div>

    {{-- LOGO 2 --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Logo 2 <span class="font-normal text-[#4B5F5A]">(opsional)</span></label>
      @if($assetOptions['logo']->isNotEmpty())
        <select name="certificate_logo2" id="certLogo2" class="{{ $inp }} mb-1.5"
                onchange="previewAsset('certLogo2', 'prevCertLogo2', 'images/logos')">
          <option value="">— Tidak ada —</option>
          @foreach($assetOptions['logo'] as $f)
            <option value="{{ $f }}" @selected($val('certificate_logo2') === $f)>{{ $f }}</option>
          @endforeach
        </select>
      @else
        <p class="mb-1.5 text-xs text-[#4B5F5A] italic">Belum ada file — upload baru di bawah.</p>
        <input type="hidden" name="certificate_logo2" value="">
      @endif
      <div class="{{ $uploadZoneCls }}">
        <label for="upload_logo2" class="{{ $uploadBtnCls }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Upload logo 2 baru
        </label>
        <span class="{{ $uploadHintCls }}">JPG/PNG, maks 2MB</span>
      </div>
      <input type="file" id="upload_logo2" name="upload_logo2"
             accept="image/jpeg,image/png,image/gif"
             class="hidden" onchange="previewUpload(this,'prevCertLogo2')">
      <img id="prevCertLogo2" class="mt-2 max-h-12 rounded-lg border border-[#DCE7E1] hidden" alt="">
      <p id="upload_logo2_name" class="mt-1 text-[10px] text-[#2D8659] hidden"></p>
    </div>

    {{-- TANDA TANGAN 1 --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Tanda Tangan 1</label>
      @if($assetOptions['sig']->isNotEmpty())
        <select name="certificate_signature1" id="certSig1" class="{{ $inp }} mb-1.5"
                onchange="previewAsset('certSig1', 'prevCertSig1', 'images/signature')">
          <option value="">— Tidak ada —</option>
          @foreach($assetOptions['sig'] as $f)
            <option value="{{ $f }}" @selected($val('certificate_signature1') === $f)>{{ $f }}</option>
          @endforeach
        </select>
      @else
        <p class="mb-1.5 text-xs text-[#4B5F5A] italic">Belum ada file — upload baru di bawah.</p>
        <input type="hidden" name="certificate_signature1" value="">
      @endif
      <div class="{{ $uploadZoneCls }}">
        <label for="upload_signature1" class="{{ $uploadBtnCls }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Upload TTD baru
        </label>
        <span class="{{ $uploadHintCls }}">JPG/PNG, maks 2MB</span>
      </div>
      <input type="file" id="upload_signature1" name="upload_signature1"
             accept="image/jpeg,image/png,image/gif"
             class="hidden" onchange="previewUpload(this,'prevCertSig1')">
      <img id="prevCertSig1" class="mt-2 max-h-12 rounded-lg border border-[#DCE7E1] hidden" alt="">
      <p id="upload_signature1_name" class="mt-1 text-[10px] text-[#2D8659] hidden"></p>
    </div>

    {{-- TANDA TANGAN 2 --}}
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Tanda Tangan 2 <span class="font-normal text-[#4B5F5A]">(opsional)</span></label>
      @if($assetOptions['sig']->isNotEmpty())
        <select name="certificate_signature2" id="certSig2" class="{{ $inp }} mb-1.5"
                onchange="previewAsset('certSig2', 'prevCertSig2', 'images/signature')">
          <option value="">— Tidak ada —</option>
          @foreach($assetOptions['sig'] as $f)
            <option value="{{ $f }}" @selected($val('certificate_signature2') === $f)>{{ $f }}</option>
          @endforeach
        </select>
      @else
        <p class="mb-1.5 text-xs text-[#4B5F5A] italic">Belum ada file — upload baru di bawah.</p>
        <input type="hidden" name="certificate_signature2" value="">
      @endif
      <div class="{{ $uploadZoneCls }}">
        <label for="upload_signature2" class="{{ $uploadBtnCls }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Upload TTD 2 baru
        </label>
        <span class="{{ $uploadHintCls }}">JPG/PNG, maks 2MB</span>
      </div>
      <input type="file" id="upload_signature2" name="upload_signature2"
             accept="image/jpeg,image/png,image/gif"
             class="hidden" onchange="previewUpload(this,'prevCertSig2')">
      <img id="prevCertSig2" class="mt-2 max-h-12 rounded-lg border border-[#DCE7E1] hidden" alt="">
      <p id="upload_signature2_name" class="mt-1 text-[10px] text-[#2D8659] hidden"></p>
    </div>

  </div>

  {{-- DESKRIPSI SERTIFIKAT --}}
  <div class="mb-4">
    <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">
      Deskripsi Sertifikat
      <span class="ml-1 text-[11px] font-normal text-[#4B5F5A]">(opsional — kosongkan untuk teks otomatis dari judul webinar)</span>
    </label>
    <textarea name="certificate_description" rows="3"
              placeholder='Contoh: Atas partisipasinya sebagai Peserta dalam Webinar "ATTITUDE IS EVERYTHING" yang diselenggarakan oleh Seven Inc'
              class="{{ $inp }} resize-none"
              maxlength="1000">{{ $val('certificate_description') }}</textarea>
    <p class="mt-1 text-[10px] text-[#4B5F5A]">
      Teks ini akan tampil di sertifikat sebagai pengganti deskripsi otomatis. Gunakan <strong>{nama}</strong> untuk menyisipkan nama peserta (opsional).
    </p>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Nama Penandatangan 1 <span class="text-red-500">*</span></label>
      <input type="text" name="certificate_signatory1_name"
             value="{{ $val('certificate_signatory1_name') }}"
             placeholder="Nama penandatangan" required class="{{ $inp }}">
    </div>
    <div>
      <label class="block text-sm font-semibold text-[#1B3A34] mb-1.5">Jabatan Penandatangan 1 <span class="text-red-500">*</span></label>
      <input type="text" name="certificate_signatory1_role"
             value="{{ $val('certificate_signatory1_role') }}"
             placeholder="Contoh: CEO / Penyelenggara" required class="{{ $inp }}">
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

<script>
// Auto-fill company dari brand
const brandLabels = @json($allBrands);
function updateCertCompany(code) {
    const hidden = document.getElementById('certCompanyHidden');
    if (hidden) hidden.value = brandLabels[code] || code || 'Seven Inc';
}
// Trigger on load
(function() {
    const sel = document.getElementById('certBrandSelect');
    if (sel && sel.value) updateCertCompany(sel.value);
})();

// Toggle brand checkbox panel
document.querySelectorAll('input[name="_allowed_brands_mode"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const wrap = document.getElementById('brands_checkbox_wrap');
        if (radio.value === 'specific' && radio.checked) {
            wrap.classList.remove('hidden');
        } else if (radio.value === 'all' && radio.checked) {
            wrap.classList.add('hidden');
            // Uncheck semua checkbox agar tidak terkirim
            wrap.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
        }
    });
});

// Select all brands button
document.getElementById('selectAllBrandsBtn')?.addEventListener('click', () => {
    const allChecked = [...document.querySelectorAll('.brand-checkbox')].every(cb => cb.checked);
    document.querySelectorAll('.brand-checkbox').forEach(cb => cb.checked = !allChecked);
});

// Preview aset gambar dari dropdown (file sudah ada di storage)
function previewAsset(selId, imgId, dir) {
    const sel = document.getElementById(selId);
    const img = document.getElementById(imgId);
    if (!sel || !img) return;
    if (!sel.value) { img.classList.add('hidden'); return; }
    img.src = '/storage/' + dir + '/' + sel.value;
    img.classList.remove('hidden');
}

// Preview file baru yang baru dipilih untuk upload
function previewUpload(input, imgId) {
    const img  = document.getElementById(imgId);
    const name = document.getElementById(input.name + '_name');
    if (!input.files || !input.files[0]) {
        if (img)  img.classList.add('hidden');
        if (name) name.classList.add('hidden');
        return;
    }
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        if (img) {
            img.src = e.target.result;
            img.classList.remove('hidden');
        }
    };
    reader.readAsDataURL(file);
    if (name) {
        name.textContent = '✓ File dipilih: ' + file.name;
        name.classList.remove('hidden');
    }
}

// Trigger existing values dari dropdown (jika sudah ada)
['certBg','certLogo1','certLogo2','certSig1','certSig2'].forEach(id => {
    const sel = document.getElementById(id);
    if (sel && sel.value) sel.dispatchEvent(new Event('change'));
});
</script>
