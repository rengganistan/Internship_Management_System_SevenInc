@extends('layouts.dashboard')

@section('content')
@php
  $val = fn($key, $default = '') => old($key, $prefill[$key] ?? $default);
@endphp

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  {{-- Header --}}
  <div class="mb-6 flex items-center gap-3">
    <a href="{{ isset($webinarId) && $webinarId ? route('admin.webinars.attendances', $webinarId) : route('admin.certificate.index') }}"
       class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
    </a>
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Sertifikat</p>
      <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Sertifikat Webinar</h1>
      <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">
        @if(isset($webinarId) && $webinarId && isset($approvedParticipants) && $approvedParticipants->isNotEmpty())
          {{ $approvedParticipants->count() }} peserta approved akan dibuatkan sertifikat.
        @else
          Generate sertifikat kehadiran webinar untuk beberapa peserta sekaligus.
        @endif
      </p>
    </div>
  </div>

  @if(session('success'))
  <div class="mb-4 flex items-center gap-2 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
  </div>
  @endif

  @if($errors->any())
  <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
    <ul class="space-y-1 text-[13px] text-[#D32F2F]">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  @if(isset($webinarId) && $webinarId && isset($approvedParticipants) && $approvedParticipants->isNotEmpty())
  <div class="mb-5 flex items-start gap-3 rounded-[10px] border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
    <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
    <div>
      <strong>Data prefilled dari webinar.</strong>
      Judul, tanggal, dan daftar peserta sudah terisi otomatis.
      Lengkapi <strong>Background</strong>, <strong>Logo</strong>, dan <strong>Tanda Tangan</strong>.
    </div>
  </div>
  @endif

  <form method="POST" action="{{ route('admin.certificate.webinar.store') }}">
  @csrf
  @if(isset($webinarId) && $webinarId)
    <input type="hidden" name="webinar_id" value="{{ $webinarId }}">
  @endif

  <div class="space-y-5">

    {{-- SEKSI 1: Informasi Webinar --}}
    <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
      <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Informasi Webinar</p>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="sm:col-span-2">
          <label class="mb-1.5 flex items-center gap-2 text-[12.5px] font-semibold text-[#1B3A34]">
            Judul Webinar <span class="text-[#D32F2F]">*</span>
            @if(isset($webinarId) && $webinarId && ($prefill['webinar_title'] ?? null))
              <span class="text-[10px] font-normal text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-200">✓ Otomatis</span>
            @endif
          </label>
          <input type="text" name="webinar_title" value="{{ $val('webinar_title') }}" required
            placeholder='Contoh: "Webinar Membangun Kepemimpinan Efektif"'
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
          <p class="mt-1 text-[11px] text-[#4B5F5A]">Judul ini akan muncul di badan sertifikat.</p>
        </div>

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Perusahaan Penyelenggara <span class="text-[#D32F2F]">*</span></label>
          <input type="text" name="company" value="{{ $val('company', 'Seven Inc') }}" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-[#D32F2F]">*</span></label>
          <input type="text" name="city" value="{{ $val('city', 'Yogyakarta') }}" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand <span class="text-[#D32F2F]">*</span></label>
          <select name="brand" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
            <option value="">Pilih Brand</option>
            @foreach($brands as $code => $label)
              <option value="{{ $code }}" {{ $val('brand') === $code ? 'selected' : '' }}>{{ $label }} ({{ $code }})</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-1.5 flex items-center gap-2 text-[12.5px] font-semibold text-[#1B3A34]">
            Tanggal Webinar <span class="text-[#D32F2F]">*</span>
            @if(isset($webinarId) && $webinarId && ($prefill['event_date'] ?? null))
              <span class="text-[10px] font-normal text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-200">✓ Otomatis</span>
            @endif
          </label>
          <input type="date" name="event_date" value="{{ $val('event_date', now()->toDateString()) }}" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

      </div>
    </div>

    {{-- SEKSI 2: Aset Visual --}}
    <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
      <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
      <p class="mb-4 text-[12px] text-[#4B5F5A]">
        Upload file dulu via tombol <strong>Background</strong>, <strong>Logo</strong>, <strong>Tanda Tangan</strong>
        di halaman <a href="{{ route('admin.certificate.index') }}" class="text-[#2D8659] underline" target="_blank">Sertifikat</a>, lalu pilih di sini.
      </p>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Background</label>
          <select name="background_image"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
            <option value="">- Tidak ada (putih) -</option>
            @foreach($backgroundFiles as $f)
              <option value="{{ $f }}" {{ $val('background_image') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo</label>
          <select name="logo1"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
            <option value="">- Tidak ada -</option>
            @foreach($logoFiles as $f)
              <option value="{{ $f }}" {{ $val('logo1') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan <span class="text-[#D32F2F]">*</span></label>
          <select name="signature_image1" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
            <option value="">Pilih file</option>
            @foreach($signatureFiles as $f)
              <option value="{{ $f }}" {{ $val('signature_image1') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
          </select>
        </div>

      </div>
    </div>

    {{-- SEKSI 3: Penandatangan --}}
    <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
      <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Penandatangan</p>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan <span class="text-[#D32F2F]">*</span></label>
          <input type="text" name="name_signatory1" value="{{ $val('name_signatory1') }}" required
            placeholder="Contoh: Chintya"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan <span class="text-[#D32F2F]">*</span></label>
          <input type="text" name="role1" value="{{ $val('role1') }}" required
            placeholder="Contoh: HR Areakerja.com"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>
      </div>
    </div>

    {{-- SEKSI 4: Daftar Peserta --}}
    <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Daftar Peserta</p>
          <p class="mt-0.5 text-[12px] text-[#4B5F5A]">
            @if(isset($webinarId) && $webinarId && isset($approvedParticipants) && $approvedParticipants->isNotEmpty())
              Sudah terisi otomatis dari peserta approved. Tambah manual jika perlu.
            @else
              Satu baris = satu sertifikat.
            @endif
          </p>
        </div>
        <button type="button" id="btnAddRow"
          class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Peserta
        </button>
      </div>

      <div id="participantRows" class="space-y-2">
        @php
          $rows = old('participants');
          if (!$rows) {
            if (isset($approvedParticipants) && $approvedParticipants->isNotEmpty()) {
              $rows = $approvedParticipants->map(fn($p) => [
                'name'          => $p['name'],
                'attendance_id' => $p['attendance_id'],
              ])->toArray();
            } else {
              $rows = [['name' => '', 'attendance_id' => null]];
            }
          }
        @endphp

        @foreach($rows as $i => $row)
        <div class="flex items-center gap-2 row-item">
          <span class="w-6 shrink-0 text-center text-[12px] font-semibold text-[#4B5F5A]">{{ $i + 1 }}</span>
          <input type="text" name="participants[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}"
            placeholder="Nama lengkap peserta" required
            class="flex-1 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
          @if(!empty($row['attendance_id']))
            <input type="hidden" name="participants[{{ $i }}][attendance_id]" value="{{ $row['attendance_id'] }}">
            <span class="text-[10px] text-green-600 bg-green-50 border border-green-200 px-2 py-1 rounded-full whitespace-nowrap">
              <i class="fas fa-check text-[9px]"></i> Approved
            </span>
          @endif
          <button type="button" class="btnDel flex h-9 w-9 shrink-0 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        @endforeach
      </div>

      <template id="tplRow">
        <div class="flex items-center gap-2 row-item">
          <span class="w-6 shrink-0 text-center text-[12px] font-semibold text-[#4B5F5A]">__NUM__</span>
          <input type="text" name="__NAME__" placeholder="Nama lengkap peserta" required
            class="flex-1 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
          <button type="button" class="btnDel flex h-9 w-9 shrink-0 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </template>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ isset($webinarId) && $webinarId ? route('admin.webinars.attendances', $webinarId) : route('admin.certificate.index') }}"
        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
        Batal
      </a>
      <button type="submit"
        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        Generate Sertifikat Webinar
      </button>
    </div>

  </div>
  </form>
</div>

@push('scripts')
<script>
(function () {
  const rowsEl = document.getElementById('participantRows');
  const tpl    = document.getElementById('tplRow').innerHTML;
  let idx      = rowsEl.querySelectorAll('.row-item').length;

  document.getElementById('btnAddRow').addEventListener('click', () => {
    const html = tpl
      .replace('__NAME__', `participants[${idx}][name]`)
      .replace('__NUM__', idx + 1);
    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    rowsEl.appendChild(wrap.firstChild);
    idx++;
  });

  rowsEl.addEventListener('click', e => {
    if (e.target.closest('.btnDel')) {
      const item = e.target.closest('.row-item');
      if (rowsEl.querySelectorAll('.row-item').length > 1) item?.remove();
    }
  });
})();
</script>
@endpush

@endsection
