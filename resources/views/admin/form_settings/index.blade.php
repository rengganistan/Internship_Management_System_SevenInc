@extends('layouts.dashboard')

@section('content')
@php
    $needsOptionsTypes = array_keys(array_filter(
        \App\Models\FormCustomField::$supportedTypes,
        fn($v, $k) => \App\Models\FormCustomField::needsOptions($k),
        ARRAY_FILTER_USE_BOTH
    ));
    $allSections = array_unique(array_merge(
        array_keys($sections),
        $customFields->pluck('section')->toArray(),
        ['Field Tambahan']
    ));
@endphp

{{-- 2-Panel Layout: kiri form, kanan preview --}}
<div class="flex h-[calc(100vh-64px)] overflow-hidden">

  {{-- ===== PANEL KIRI ===== --}}
  <div class="w-full max-w-2xl shrink-0 overflow-y-auto border-r border-[#DCE7E1] bg-[#F4F8F6]">
    <div class="p-5">

      {{-- Header --}}
      <div class="mb-5 flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dashboard & Monitoring</p>
          <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Pengaturan Form Pendaftaran</h1>
          <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Field <span class="font-semibold text-[#2D8659]">Inti</span> tidak bisa dinonaktifkan.</p>
        </div>
        <button onclick="openAddModal()"
            class="flex shrink-0 items-center gap-1.5 rounded-[9px] bg-[#2D8659] px-3.5 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Field
        </button>
      </div>

      {{-- Alerts --}}
      @if(session('success'))
      <div class="mb-4 flex items-center gap-2 rounded-[9px] border border-[#A5D6A7] bg-[#E8F5E9] px-3 py-2.5 text-[13px] font-semibold text-[#1F5F3F]">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
          {{ session('success') }}
      </div>
      @endif
      @if($errors->any())
      <div class="mb-4 rounded-[9px] border border-red-200 bg-red-50 px-3 py-2.5 text-[13px] text-[#D32F2F]">
          <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      {{-- Form toggle system fields --}}
      <form method="POST" action="{{ route('admin.form-settings.update') }}" id="systemForm">
        @csrf
        <div class="space-y-4">

          {{-- System Fields per section --}}
          @foreach($sections as $sectionName => $sectionFields)
          <div class="overflow-hidden rounded-[11px] border border-[#DCE7E1] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5">
              <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-[#1B3A34]">{{ $sectionName }}</p>
              <span class="rounded-full border border-[#DCE7E1] bg-white px-2 py-0.5 text-[10px] text-[#4B5F5A]">System</span>
            </div>
            <div class="divide-y divide-[#DCE7E1]">
              @foreach($sectionFields as $field)
              @php $isCore = (bool)($field['is_core'] ?? false); @endphp
              <div class="flex items-center gap-3 px-4 py-3 {{ !$field['is_active'] ? 'opacity-55' : '' }}">
                {{-- Icon tipe --}}
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[7px] bg-[#E8F5E9] text-[#2D8659]">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 7h18M3 12h18M3 17h10"/></svg>
                </div>
                {{-- Label + key --}}
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5">
                    <p class="text-[13px] font-semibold text-[#1B3A34] truncate" data-sys-key="{{ $field['key'] }}">{{ $field['label'] }}</p>
                    @if($isCore)<span class="shrink-0 rounded-full bg-[#E8F5E9] border border-[#A5D6A7] px-1.5 py-0.5 text-[9px] font-bold text-[#2D8659]">Inti</span>@endif
                  </div>
                  <p class="text-[11px] font-mono text-[#4B5F5A]">{{ $field['key'] }}</p>
                </div>
                {{-- Aktif --}}
                <div class="flex flex-col items-center gap-0.5">
                  <span class="text-[9px] font-bold uppercase text-[#4B5F5A]">Aktif</span>
                  @if($isCore)
                    <div class="relative h-5 w-9 rounded-full bg-[#2D8659] opacity-60 cursor-not-allowed"><span class="absolute top-0.5 right-0.5 h-4 w-4 rounded-full bg-white shadow"></span></div>
                    <input type="hidden" name="active_{{ $field['key'] }}" value="1">
                  @else
                    <label class="relative inline-flex cursor-pointer">
                      <input type="checkbox" name="active_{{ $field['key'] }}" value="1" {{ $field['is_active'] ? 'checked' : '' }}
                        class="peer sr-only" onchange="syncRequired(this,'{{ $field['key'] }}'); updatePreview()">
                      <div class="peer h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-[#2D8659] after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow after:transition after:content-[''] peer-checked:after:translate-x-4 transition"></div>
                    </label>
                  @endif
                </div>
                {{-- Wajib --}}
                <div class="flex flex-col items-center gap-0.5">
                  <span class="text-[9px] font-bold uppercase text-[#4B5F5A]">Wajib</span>
                  @if($isCore)
                    <div class="relative h-5 w-9 rounded-full bg-amber-400 opacity-60 cursor-not-allowed"><span class="absolute top-0.5 right-0.5 h-4 w-4 rounded-full bg-white shadow"></span></div>
                    <input type="hidden" name="required_{{ $field['key'] }}" value="1">
                  @else
                    <label class="relative inline-flex cursor-pointer">
                      <input type="checkbox" name="required_{{ $field['key'] }}" id="required_{{ $field['key'] }}" value="1"
                        {{ $field['is_required'] ? 'checked' : '' }} {{ !$field['is_active'] ? 'disabled' : '' }} class="peer sr-only"
                        onchange="updatePreview()">
                      <div class="peer h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-amber-400 peer-disabled:opacity-40 after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow after:transition after:content-[''] peer-checked:after:translate-x-4 transition"></div>
                    </label>
                  @endif
                </div>
                {{-- Aksi edit system field (non-core saja) --}}
                @if(!$isCore)
                <button type="button"
                    onclick="openSystemEditModal('{{ $field['key'] }}','{{ addslashes($field['label']) }}','{{ addslashes($field['placeholder'] ?? '') }}')"
                    title="Edit Label"
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[7px] border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-amber-400 hover:text-amber-600 transition">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </button>
                @else
                <div class="w-7 shrink-0"></div>
                @endif
              </div>
              @endforeach
            </div>
          </div>
          @endforeach

          {{-- Custom Fields --}}
          @if($customFields->isNotEmpty())
          <div class="overflow-hidden rounded-[11px] border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-amber-100 bg-amber-50 px-4 py-2.5">
              <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-amber-700">Field Tambahan</p>
              <span class="rounded-full border border-amber-200 bg-amber-100 px-2 py-0.5 text-[10px] text-amber-700">Custom</span>
            </div>
            <div class="divide-y divide-[#DCE7E1]">
              @foreach($customFields as $cf)
              <div class="flex items-center gap-3 px-4 py-3 {{ !$cf->is_active ? 'opacity-55' : '' }}">
                {{-- Icon --}}
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[7px] bg-amber-50 text-amber-600">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="8" x2="12" y2="8"/></svg>
                </div>
                {{-- Label --}}
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5">
                    <p class="text-[13px] font-semibold text-[#1B3A34] truncate">{{ $cf->label }}</p>
                    <span class="shrink-0 rounded-full bg-amber-50 border border-amber-200 px-1.5 py-0.5 text-[9px] font-semibold text-amber-700">{{ \App\Models\FormCustomField::$supportedTypes[$cf->type] ?? $cf->type }}</span>
                  </div>
                  <p class="text-[11px] font-mono text-[#4B5F5A]">{{ $cf->field_key }}@if($cf->section && $cf->section !== 'Field Tambahan') · {{ $cf->section }}@endif</p>
                </div>
                {{-- Status badges --}}
                <span class="text-[10px] rounded-full px-1.5 py-0.5 {{ $cf->is_active ? 'bg-[#E8F5E9] text-[#1F5F3F]' : 'bg-gray-100 text-gray-400' }}">{{ $cf->is_active ? 'Aktif' : 'Off' }}</span>
                <span class="text-[10px] rounded-full px-1.5 py-0.5 {{ $cf->is_required ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-400' }}">{{ $cf->is_required ? 'Wajib' : 'Ops' }}</span>
                {{-- Aksi --}}
                <div class="flex items-center gap-1 shrink-0">
                  <button type="button" onclick='openEditModal(@json($cf))' title="Edit"
                      class="flex h-7 w-7 items-center justify-center rounded-[7px] border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-amber-400 hover:text-amber-600 transition">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                  </button>
                  <form method="POST" action="{{ route('admin.form-settings.custom.toggle', $cf->id) }}" class="inline">
                    @csrf
                    <button type="submit" title="{{ $cf->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                        class="flex h-7 w-7 items-center justify-center rounded-[7px] border transition {{ $cf->is_active ? 'border-orange-200 bg-orange-50 text-orange-500 hover:bg-orange-500 hover:text-white' : 'border-[#A5D6A7] bg-[#E8F5E9] text-[#2D8659] hover:bg-[#2D8659] hover:text-white' }}">
                        @if($cf->is_active)<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        @else<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>@endif
                    </button>
                  </form>
                  <form method="POST" action="{{ route('admin.form-settings.custom.destroy', $cf->id) }}" class="inline"
                        onsubmit="return confirm('Hapus field \'{{ addslashes($cf->label) }}\'?\nData jawaban lama tidak akan terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Hapus"
                        class="flex h-7 w-7 items-center justify-center rounded-[7px] border border-red-200 bg-red-50 text-[#D32F2F] hover:bg-red-500 hover:text-white transition">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endif

        </div>

        {{-- Simpan + Reset --}}
        <div class="mt-4 flex items-center gap-2">
          <button type="submit" class="flex items-center gap-1.5 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
              Simpan
          </button>
          <form method="POST" action="{{ route('admin.form-settings.reset') }}" class="inline" onsubmit="return confirm('Reset ke default?')">
              @csrf
              <button type="submit" class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] hover:border-red-300 hover:text-red-600 transition">Reset</button>
          </form>
          <a href="{{ route('admin.form-settings.divisions') }}" class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#1F5F3F] transition">Pengaturan Divisi</a>
        </div>
      </form>

    </div>
  </div>

  {{-- ===== PANEL KANAN: PREVIEW LIVE ===== --}}
  <div class="hidden lg:flex flex-1 flex-col bg-white">
    <div class="flex items-center gap-2 border-b border-[#DCE7E1] px-5 py-3">
      <div class="h-2 w-2 rounded-full bg-[#2D8659]"></div>
      <span class="text-[13px] font-semibold text-[#1B3A34]">Preview Live</span>
      <span class="text-[11px] text-[#4B5F5A]">— tampilan form yang akan dilihat pemagang</span>
    </div>
    <div class="flex-1 overflow-y-auto p-6" id="preview-pane">
      {{-- Diisi oleh renderPreview() --}}
    </div>
  </div>

</div>

{{-- ===== MODAL TAMBAH FIELD ===== --}}
<div id="addModal" class="fixed inset-0 z-[110] hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeAddModal()"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-[16px] bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
      <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4 shrink-0">
        <h3 class="text-[15px] font-bold text-[#1B3A34]">Tambah Field Baru</h3>
        <button onclick="closeAddModal()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <form method="POST" action="{{ route('admin.form-settings.custom.store') }}" id="addForm" class="overflow-y-auto p-5 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Label / Pertanyaan <span class="text-red-500">*</span></label>
            <input type="text" name="label" id="add_label" required placeholder="Contoh: Domisili Saat Ini"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"
              oninput="autoGenerateKey(this.value)">
          </div>
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Key (nama field) <span class="text-red-500">*</span></label>
            <input type="text" name="field_key" id="add_field_key" required placeholder="domicile" pattern="[a-z][a-z0-9_]*"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] font-mono text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
            <p class="mt-1 text-[11px] text-[#4B5F5A]">Huruf kecil + angka + underscore</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tipe Input <span class="text-red-500">*</span></label>
            <select name="type" id="add_type" required onchange="toggleOptionsArea('add')"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
              @foreach(\App\Models\FormCustomField::$supportedTypes as $val => $lbl)
              <option value="{{ $val }}">{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Seksi</label>
            <input type="text" name="section" list="section-list" placeholder="Field Tambahan"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
            <datalist id="section-list">
              @foreach($allSections as $sec)<option value="{{ $sec }}">@endforeach
            </datalist>
          </div>
        </div>
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Keterangan / Placeholder</label>
          <input type="text" name="placeholder" placeholder="Contoh: Masukkan kota tempat tinggal Anda"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
        </div>
        <div id="add_options_area" class="hidden rounded-[9px] border border-[#DCE7E1] p-3 space-y-2">
          <p class="text-[12.5px] font-semibold text-[#1B3A34]">Daftar Pilihan <span class="text-red-500">*</span></p>
          <div id="add_options_list" class="space-y-2"></div>
          <button type="button" onclick="addOption('add')"
            class="flex items-center gap-1.5 text-[12.5px] font-semibold text-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Pilihan
          </button>
        </div>
        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer text-[13px] text-[#1B3A34]">
            <input type="checkbox" name="is_required" value="1" class="w-4 h-4 accent-amber-500"> Wajib diisi
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-[13px] text-[#1B3A34]">
            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#2D8659]"> Aktif
          </label>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-[#DCE7E1] pt-4">
          <button type="button" onclick="closeAddModal()" class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#4B5F5A]">Batal</button>
          <button type="submit" class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1F5F3F]">Simpan Field</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ===== MODAL EDIT FIELD ===== --}}
<div id="editModal" class="fixed inset-0 z-[110] hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeEditModal()"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-[16px] bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
      <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4 shrink-0">
        <h3 class="text-[15px] font-bold text-[#1B3A34]">Edit Field Custom</h3>
        <button onclick="closeEditModal()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <form method="POST" id="editForm" class="overflow-y-auto p-5 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Label / Pertanyaan <span class="text-red-500">*</span></label>
            <input type="text" name="label" id="edit_label" required
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
          </div>
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Key (read-only)</label>
            <input type="text" id="edit_field_key_display" disabled
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-gray-100 px-3 py-2.5 text-[13px] font-mono text-gray-400 cursor-not-allowed">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tipe Input</label>
            <select name="type" id="edit_type" onchange="toggleOptionsArea('edit')"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
              @foreach(\App\Models\FormCustomField::$supportedTypes as $val => $lbl)
              <option value="{{ $val }}">{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Seksi</label>
            <input type="text" name="section" id="edit_section" list="section-list"
              class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
          </div>
        </div>
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Keterangan / Placeholder</label>
          <input type="text" name="placeholder" id="edit_placeholder"
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
        </div>
        <div id="edit_options_area" class="hidden rounded-[9px] border border-[#DCE7E1] p-3 space-y-2">
          <p class="text-[12.5px] font-semibold text-[#1B3A34]">Daftar Pilihan</p>
          <div id="edit_options_list" class="space-y-2"></div>
          <button type="button" onclick="addOption('edit')"
            class="flex items-center gap-1.5 text-[12.5px] font-semibold text-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Pilihan
          </button>
        </div>
        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer text-[13px] text-[#1B3A34]">
            <input type="checkbox" name="is_required" id="edit_is_required" value="1" class="w-4 h-4 accent-amber-500"> Wajib diisi
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-[13px] text-[#1B3A34]">
            <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 accent-[#2D8659]"> Aktif
          </label>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-[#DCE7E1] pt-4">
          <button type="button" onclick="closeEditModal()" class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#4B5F5A]">Batal</button>
          <button type="submit" class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1F5F3F]">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ===== MODAL EDIT SYSTEM FIELD (label & placeholder only) ===== --}}
<div id="sysEditModal" class="fixed inset-0 z-[110] hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeSystemEditModal()"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-[16px] bg-white shadow-xl overflow-hidden">
      <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
        <div>
          <h3 class="text-[15px] font-bold text-[#1B3A34]">Edit Field Sistem</h3>
          <p class="text-[11.5px] text-[#4B5F5A]">Hanya label & keterangan yang dapat diubah.</p>
        </div>
        <button onclick="closeSystemEditModal()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <form id="sysEditForm" class="p-5 space-y-4">
        <input type="hidden" id="sys_edit_key">
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Label / Nama Field <span class="text-red-500">*</span></label>
          <input type="text" id="sys_edit_label" required
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>
        <div>
          <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Keterangan / Placeholder</label>
          <input type="text" id="sys_edit_placeholder" placeholder="Teks bantuan untuk pemagang..."
            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
          <p class="mt-1 text-[11px] text-[#4B5F5A]">Key dan tipe tidak dapat diubah karena digunakan oleh sistem.</p>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-[#DCE7E1] pt-4">
          <button type="button" onclick="closeSystemEditModal()" class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#4B5F5A]">Batal</button>
          <button type="submit" class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1F5F3F]">Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_CUSTOM_URL = @json(url('/admin/form-settings/custom'));
const NEEDS_OPTIONS   = @json($needsOptionsTypes);

// ── Modal Add ──────────────────────────────────────────────────────────────────
function openAddModal()  { document.getElementById('addModal').classList.remove('hidden'); }
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addForm').reset();
    document.getElementById('add_options_list').innerHTML = '';
    document.getElementById('add_options_area').classList.add('hidden');
}

function autoGenerateKey(label) {
    document.getElementById('add_field_key').value = label.toLowerCase()
        .replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'')
        .replace(/^_+|_+$/g,'').replace(/_{2,}/g,'_');
}

// ── Modal Edit ──────────────────────────────────────────────────────────────────
function openEditModal(cf) {
    const form = document.getElementById('editForm');
    form.action = BASE_CUSTOM_URL + '/' + cf.id;
    document.getElementById('edit_label').value           = cf.label       || '';
    document.getElementById('edit_field_key_display').value = cf.field_key || '';
    document.getElementById('edit_type').value            = cf.type        || 'text';
    document.getElementById('edit_section').value         = cf.section     || '';
    document.getElementById('edit_placeholder').value     = cf.placeholder || '';
    document.getElementById('edit_is_required').checked   = !!cf.is_required;
    document.getElementById('edit_is_active').checked     = !!cf.is_active;
    const list = document.getElementById('edit_options_list');
    list.innerHTML = '';
    if (cf.options && Array.isArray(cf.options)) cf.options.forEach(o => appendOptionRow('edit', o));
    toggleOptionsArea('edit');
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }

// ── Options ────────────────────────────────────────────────────────────────────
function toggleOptionsArea(prefix) {
    const needs = NEEDS_OPTIONS.includes(document.getElementById(prefix+'_type').value);
    document.getElementById(prefix+'_options_area').classList.toggle('hidden', !needs);
}
function addOption(prefix) { appendOptionRow(prefix, ''); }
function appendOptionRow(prefix, value) {
    const list = document.getElementById(prefix+'_options_list');
    const row  = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `<input type="text" name="options[]" value="${esc(value)}" placeholder="Pilihan..."
        class="flex-1 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] outline-none focus:border-[#2D8659]">
        <button type="button" onclick="this.closest('div').remove()"
            class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        </button>`;
    list.appendChild(row);
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Sync required disable when active off ─────────────────────────────────────
function syncRequired(activeToggle, fieldKey) {
    const req = document.getElementById('required_' + fieldKey);
    if (!req) return;
    req.disabled = !activeToggle.checked;
    if (!activeToggle.checked) req.checked = false;
}

// ── Preview Live ───────────────────────────────────────────────────────────────
function updatePreview() {
    const pane = document.getElementById('preview-pane');
    if (!pane) return;

    // Kumpulkan status field dari form
    const activeFields = {};
    document.querySelectorAll('#systemForm input[name^="active_"]').forEach(el => {
        const key = el.name.replace('active_','');
        activeFields[key] = el.type === 'hidden' ? true : el.checked;
    });
    document.querySelectorAll('#systemForm input[name^="required_"]').forEach(el => {
        const key = el.name.replace('required_','');
        if (!activeFields[key]) return; // skip jika field nonaktif
        activeFields[key + '_required'] = el.type === 'hidden' ? true : el.checked;
    });

    // Render preview sederhana
    const sysFieldDefs = @json($fields);
    const customFieldDefs = @json($customFields);

    // Group by section
    const sections = {};
    sysFieldDefs.forEach(f => {
        if (!activeFields[f.key]) return;
        if (!sections[f.section]) sections[f.section] = [];
        sections[f.section].push({ ...f, isCustom: false, required: activeFields[f.key + '_required'] || f.is_required });
    });
    customFieldDefs.forEach(f => {
        if (!f.is_active) return;
        const sec = f.section || 'Field Tambahan';
        if (!sections[sec]) sections[sec] = [];
        sections[sec].push({ ...f, key: f.field_key, isCustom: true, required: f.is_required });
    });

    let html = '';
    for (const [sec, fields] of Object.entries(sections)) {
        html += `<div class="mb-6"><p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-[#2D8659]">${sec}</p><div class="space-y-3">`;
        fields.forEach(f => {
            const req = f.required ? '<span class="text-red-500 ml-0.5">*</span>' : '';
            const badge = f.isCustom ? '<span class="ml-1 text-[9px] bg-amber-100 text-amber-700 rounded px-1">Custom</span>' : '';
            html += `<div><label class="block text-[12.5px] font-medium text-[#1B3A34] mb-1">${f.label}${req}${badge}</label>`;
            const ph = f.placeholder || '';
            if (f.type === 'textarea') {
                html += `<textarea rows="2" disabled placeholder="${ph}" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F9FAFB] px-3 py-2 text-[13px] text-gray-400 resize-none"></textarea>`;
            } else if (f.type === 'select') {
                const opts = (f.options||[]).map(o => `<option>${o}</option>`).join('');
                html += `<select disabled class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F9FAFB] px-3 py-2 text-[13px] text-gray-400"><option>-- Pilih --</option>${opts}</select>`;
            } else if (f.type === 'radio') {
                const opts = (f.options||[]).map(o => `<label class="flex items-center gap-2 text-[13px] text-gray-500"><input type="radio" disabled> ${o}</label>`).join('');
                html += `<div class="flex gap-4">${opts}</div>`;
            } else if (f.type === 'checkbox') {
                const opts = (f.options||[]).map(o => `<label class="flex items-center gap-2 text-[13px] text-gray-500"><input type="checkbox" disabled> ${o}</label>`).join('');
                html += `<div class="space-y-1">${opts}</div>`;
            } else if (f.type === 'date') {
                html += `<input type="date" disabled class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F9FAFB] px-3 py-2 text-[13px] text-gray-400">`;
            } else {
                html += `<input type="text" disabled placeholder="${ph}" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F9FAFB] px-3 py-2 text-[13px] text-gray-400">`;
            }
            html += `</div>`;
        });
        html += '</div></div>';
    }
    pane.innerHTML = html || '<p class="text-sm text-gray-400">Tidak ada field aktif.</p>';
}

// ── System field edit modal ───────────────────────────────────────────────────
function openSystemEditModal(key, label, placeholder) {
    document.getElementById('sys_edit_key').value         = key;
    document.getElementById('sys_edit_label').value       = label;
    document.getElementById('sys_edit_placeholder').value = placeholder;
    document.getElementById('sysEditModal').classList.remove('hidden');
}
function closeSystemEditModal() { document.getElementById('sysEditModal').classList.add('hidden'); }

// Handle system field edit form submit — update label via AJAX POST
document.getElementById('sysEditForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const key   = document.getElementById('sys_edit_key').value;
    const label = document.getElementById('sys_edit_label').value.trim();
    const ph    = document.getElementById('sys_edit_placeholder').value.trim();
    if (!label) return;

    // Update label di DOM
    const rows = document.querySelectorAll(`[data-sys-key="${key}"]`);
    rows.forEach(r => r.textContent = label);

    // Store di hidden input so it saves with the form
    let hi = document.getElementById('label_override_' + key);
    if (!hi) {
        hi = document.createElement('input');
        hi.type = 'hidden';
        hi.id   = 'label_override_' + key;
        hi.name = 'label_' + key;
        document.getElementById('systemForm').appendChild(hi);
    }
    hi.value = label;

    let hip = document.getElementById('placeholder_override_' + key);
    if (!hip) {
        hip = document.createElement('input');
        hip.type = 'hidden';
        hip.id   = 'placeholder_override_' + key;
        hip.name = 'placeholder_' + key;
        document.getElementById('systemForm').appendChild(hip);
    }
    hip.value = ph;

    closeSystemEditModal();
    updatePreview();
});

// Escape modal on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeAddModal(); closeEditModal(); closeSystemEditModal(); } });

// Init preview on load
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endsection
