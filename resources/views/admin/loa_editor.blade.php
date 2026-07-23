@extends('layouts.dashboard')

@section('content')
<div class="pt-6 container mx-auto">
  <h1 class="text-2xl font-bold mb-4">LOA Editor & Generator</h1>

  @if(session('success'))
    <div class="bg-green-100 border border-green-300 p-3 rounded mb-4">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="bg-red-100 border border-red-300 p-3 rounded mb-4">{{ session('error') }}</div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- ===== KIRI: FORM ===== --}}
    <div class="space-y-8">
      {{-- Settings Form --}}
      <div class="bg-white shadow rounded p-4 mb-8">
        <h2 class="text-xl font-semibold mb-3">Pengaturan LOA</h2>
        <form action="{{ route('admin.loa.update') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label>Nama Perusahaan</label>
              <input type="text" name="company_name" class="form-input w-full" value="{{ old('company_name', $loaSettings->company_name ?? '') }}">
            </div>
            <div>
              <label>Email Kontak</label>
              <input type="text" name="company_contact_email" class="form-input w-full" value="{{ old('company_contact_email', $loaSettings->company_contact_email ?? '') }}">
            </div>
            <div>
              <label>Nama Penandatangan</label>
              <input type="text" name="signatory_name" class="form-input w-full" value="{{ old('signatory_name', $loaSettings->signatory_name ?? '') }}">
            </div>
            <div>
              <label>Jabatan Penandatangan</label>
              <input type="text" name="signatory_position" class="form-input w-full" value="{{ old('signatory_position', $loaSettings->signatory_position ?? '') }}">
            </div>
            <div>
              <label>Logo (opsional)</label>
              <input type="file" name="logo_path" class="form-input w-full">
            </div>
            <div>
              <label>Tanda Tangan/Stamp (opsional)</label>
              <input type="file" name="stamp_path" class="form-input w-full">
            </div>
            <div class="col-span-2">
              <label>Kop/Heading (opsional)</label>
              <input type="text" name="header_text" class="form-input w-full" value="{{ old('header_text', $loaSettings->header_text ?? '') }}">
            </div>
            <div class="col-span-2">
              <label>Footer (opsional)</label>
              <input type="text" name="footer_text" class="form-input w-full" value="{{ old('footer_text', $loaSettings->footer_text ?? '') }}">
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded">Simpan Pengaturan</button>
          </div>
        </form>
      </div>

      {{-- Generate SINGLE --}}
      <div class="bg-white shadow rounded p-4 mb-8">
        <h2 class="text-xl font-semibold mb-3">Generate LOA (Single)</h2>
        <form action="{{ route('admin.loa.generate') }}" method="POST">
          @csrf
          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
              <label>Pilih Pemagang</label>
              <select name="intern_id" id="intern_id" class="form-select w-full">
                <option value="">-- pilih --</option>
                @foreach($registrations as $r)
                  <option
                    value="{{ $r->id }}"
                    data-fullname="{{ $r->fullname }}"
                    data-student-id="{{ $r->student_id }}"
                    data-study-program="{{ $r->study_program }}"
                    data-institution-name="{{ $r->institution_name }}"
                    data-start-date="{{ $r->start_date }}"
                    data-end-date="{{ $r->end_date }}"
                    data-phone-number="{{ $r->phone_number }}"
                    data-status="{{ $r->internship_status }}"
                  >
                    {{ $r->fullname }} ({{ $r->student_id }}) | {{ $r->institution_name }} | status: {{ $r->internship_status }}
                  </option>
                @endforeach
              </select>
            </div>
            <div>
              <label>&nbsp;</label>
              <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">Generate PDF</button>
            </div>
          </div>
        </form>
      </div>

      {{-- Generate BATCH --}}
      <div class="bg-white shadow rounded p-4">
        <h2 class="text-xl font-semibold mb-3">Generate LOA (Multiple)</h2>
        <form action="{{ route('admin.loa.generateBatch') }}" method="POST">
          @csrf
          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
              <div class="flex items-center justify-between mb-1">
                <label>Pilih Pemagang (bisa lebih dari satu)</label>
                <label class="text-sm text-gray-500 cursor-pointer flex items-center gap-1">
                  <input type="checkbox" id="selectAllInterns"> Pilih Semua
                </label>
              </div>
              <p class="text-xs text-gray-400 mb-1">Tahan <kbd class="bg-gray-100 border px-1 rounded">Ctrl</kbd> (Windows) atau <kbd class="bg-gray-100 border px-1 rounded">⌘ Cmd</kbd> (Mac) untuk pilih lebih dari satu.</p>
              <select name="intern_ids[]" id="intern_ids" class="form-multiselect w-full" multiple size="10">
                @foreach($registrations as $r)
                  <option
                    value="{{ $r->id }}"
                    data-fullname="{{ $r->fullname }}"
                    data-student-id="{{ $r->student_id }}"
                    data-study-program="{{ $r->study_program }}"
                    data-institution-name="{{ $r->institution_name }}"
                    data-start-date="{{ $r->start_date }}"
                    data-end-date="{{ $r->end_date }}"
                    data-phone-number="{{ $r->phone_number }}"
                    data-status="{{ $r->internship_status }}"
                  >
                    {{ $r->fullname }} ({{ $r->student_id }}) | {{ $r->institution_name }} | status: {{ $r->internship_status }}
                  </option>
                @endforeach
              </select>
            </div>
            <div>
              <label>&nbsp;</label>
              <button class="bg-indigo-600 text-white px-4 py-2 rounded w-full">Generate PDF Batch</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- ===== KANAN: LIVE PREVIEW ===== --}}
    <div>
      <div class="bg-white shadow rounded p-4">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-xl font-semibold">Live Preview LOA</h2>
          <button id="btnRefreshPreview" class="text-sm px-3 py-1 border rounded">Refresh</button>
        </div>
        <iframe
          id="loaPreview"
          src="{{ route('user.loa.preview') }}"
          class="w-full"
          style="height: calc(100vh - 220px); border:1px solid #e5e7eb; border-radius: 8px;"
        ></iframe>
        <p class="text-xs text-gray-500 mt-2">Preview mencerminkan pilihan pemagang pada form di kiri (single/multiple).</p>
      </div>
    </div>
  </div>
</div>


@php $justSaved = session()->has('success'); @endphp


{{-- ===== SCRIPT: Sinkronisasi ke iframe ===== --}}
<script>
(function(){
  const $single = document.getElementById('intern_id');
  const $multi  = document.getElementById('intern_ids');
  const $iframe = document.getElementById('loaPreview');
  const $btnRefresh = document.getElementById('btnRefreshPreview');

  // ← tambahkan ini: auto refresh jika barusan save settings
  const JUST_SAVED = {{ $justSaved ? 'true' : 'false' }};
  if (JUST_SAVED && $iframe) {
    // pakai cache-buster biar pasti redraw
    $iframe.src = "{{ route('user.loa.preview') }}" + '?t=' + Date.now();
  }

  const fmtID = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
  function formatDate(iso) {
    if (!iso) return null;
    const d = new Date(iso);
    return isNaN(d.getTime()) ? null : fmtID.format(d);
  }

  function optionToRow(opt) {
    const start = formatDate(opt.dataset.startDate);
    const end   = formatDate(opt.dataset.endDate);
    return {
      nama_siswa: opt.dataset.fullname || 'Nama Tidak Diketahui',
      nim_nis: opt.dataset.studentId || 'NIM/NIS Tidak Diketahui',
      jurusan: opt.dataset.studyProgram || 'Jurusan Tidak Diketahui',
      instansi: opt.dataset.institutionName || 'Instansi Tidak Diketahui',
      periode: (start && end) ? (start + ' - ' + end) : 'Periode Tidak Diketahui',
      kontak: opt.dataset.phoneNumber || 'Kontak Tidak Diketahui'
    };
  }

  function collectRows() {
    const rows = [];
    if ($multi && $multi.selectedOptions && $multi.selectedOptions.length > 0) {
      Array.from($multi.selectedOptions).forEach(opt => rows.push(optionToRow(opt)));
    } else if ($single && $single.value) {
      const opt = $single.options[$single.selectedIndex];
      if (opt && opt.value) rows.push(optionToRow(opt));
    }
    return rows;
  }

  function postRowsToIframe() {
    if (!$iframe || !$iframe.contentWindow) return;
    const rows = collectRows();
    $iframe.contentWindow.postMessage({ type: 'updateLOA', rows }, window.location.origin);
  }

  // Checkbox select all
  const $selectAll = document.getElementById('selectAllInterns');
  if ($selectAll && $multi) {
    $selectAll.addEventListener('change', function() {
      Array.from($multi.options).forEach(opt => opt.selected = $selectAll.checked);
      postRowsToIframe();
    });
    // Kalau user deselect manual, uncheck "Pilih Semua"
    $multi.addEventListener('change', function() {
      $selectAll.checked = Array.from($multi.options).every(opt => opt.selected);
      postRowsToIframe();
    });
  }

  // Event: perubahan pilihan = update preview
  if ($single)  $single.addEventListener('change', postRowsToIframe);
  if ($multi)   $multi.addEventListener('change', postRowsToIframe);

  // Tombol manual refresh
  if ($btnRefresh) $btnRefresh.addEventListener('click', function(){
    $iframe.src = "{{ route('user.loa.preview') }}" + '?t=' + Date.now();
  });

  // Saat iframe selesai load, kirim data terpilih
  if ($iframe) $iframe.addEventListener('load', postRowsToIframe);

  // Jaga-jaga: saat window refocus, sinkronkan lagi
  window.addEventListener('focus', postRowsToIframe);

  // Trigger awal
  document.addEventListener('DOMContentLoaded', postRowsToIframe);
})();
</script>

@endsection