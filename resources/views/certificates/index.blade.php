@extends('layouts.dashboard')

@php use Illuminate\Support\Str; @endphp

@section('content')

{{-- Modal konfirmasi hapus --}}
<div id="deleteModal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-[#D32F2F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus sertifikat?</h3>
                <p class="text-[12.5px] text-[#4B5F5A] leading-relaxed">
                    Sertifikat <strong id="deleteCertName"></strong> akan dihapus permanen.
                </p>
            </div>
            <div class="flex justify-center gap-3 border-t border-[#DCE7E1] px-5 py-4">
                <button type="button" onclick="closeDeleteModal()"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]">
                    Batal
                </button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="rounded-[9px] bg-[#D32F2F] px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal upload aset --}}
<div id="uploadModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" onclick="closeUploadModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
                <h3 id="uploadModalTitle" class="text-[15px] font-bold text-[#1B3A34]">Upload File</h3>
                <button onclick="closeUploadModal()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="p-5">
                <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                            Pilih File Gambar
                            <span id="uploadPrefix" class="ml-1 text-[11px] font-normal text-[#4B5F5A]"></span>
                        </label>
                        <input type="file" name="file" accept=".png,.jpg,.jpeg,.webp" required
                            onchange="previewUpload(event)"
                            class="block w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        <p id="uploadHint" class="mt-1 text-[11px] text-[#4B5F5A]"></p>
                    </div>
                    <div id="uploadPreviewWrap" class="hidden">
                        <img id="uploadPreview" class="max-h-40 w-full rounded-[8px] object-contain border border-[#DCE7E1]" alt="Preview">
                    </div>
                    <div class="flex justify-end gap-3 border-t border-[#DCE7E1] pt-4">
                        <button type="button" onclick="closeUploadModal()"
                            class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1F5F3F]">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Sertifikat</h1>
            <p class="mt-1 text-sm text-[#4B5F5A]">
                Total <strong class="text-[#1B3A34]">{{ $certificates->count() }}</strong> sertifikat tersimpan
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.certificate.create') }}"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Sertifikat Magang
            </a>
            <a href="{{ route('admin.certificate.external.create') }}"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Sertifikat Non-Magang
            </a>
            {{-- Upload aset --}}
            <button type="button" onclick="openUploadModal('bg')"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Background
            </button>
            <button type="button" onclick="openUploadModal('logo')"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Logo
            </button>
            <button type="button" onclick="openUploadModal('ttd')"
                class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Tanda Tangan
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Filter toolbar --}}
    <div class="mb-5 rounded-[12px] border border-[#DCE7E1] bg-white p-4 shadow-sm">
        <div class="flex flex-wrap gap-3">
            <label class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 flex-1 min-w-[200px]">
                <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="searchInput" type="text" placeholder="Cari nama, serial, perusahaan..."
                    oninput="filterTable()"
                    class="w-full border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A]">
            </label>
            <select id="brandFilter" onchange="filterTable()"
                class="rounded-[9px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                <option value="">Semua Brand</option>
                @foreach(collect($certificates)->pluck('brand')->filter()->unique()->values() as $b)
                <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
            </select>
            <select id="divisionFilter" onchange="filterTable()"
                class="rounded-[9px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                <option value="">Semua Divisi</option>
                @foreach(collect($certificates)->pluck('division')->filter()->unique()->values() as $d)
                <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Divisi</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Perusahaan</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Brand</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nomor Serial</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="certTable" class="divide-y divide-[#DCE7E1]">
                    @forelse($certificates as $cert)
                    <tr class="transition hover:bg-[#F4F8F6]"
                        data-brand="{{ strtolower($cert->brand) }}"
                        data-division="{{ strtolower($cert->division) }}"
                        data-search="{{ Str::lower($cert->name.' '.$cert->company.' '.$cert->serial_number) }}">

                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#1B3A34]">{{ $cert->name }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 border border-blue-200">
                                {{ $cert->division }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $cert->company }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 border border-amber-200">
                                {{ $cert->brand }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <code class="rounded-[6px] bg-[#F4F8F6] px-2 py-1 text-[11px] text-[#1B3A34] border border-[#DCE7E1]">
                                    {{ $cert->serial_number }}
                                </code>
                                <button type="button" onclick="copySerial('{{ $cert->serial_number }}', this)" title="Salin serial"
                                    class="flex h-7 w-7 items-center justify-center rounded-[6px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#2D8659]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.certificate.show', $cert->id) }}" title="Preview"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('admin.certificate.pdf', $cert->id) }}" title="Download PDF"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                <a href="{{ route('admin.certificate.edit', $cert->id) }}" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                                <button type="button" title="Hapus"
                                    onclick="openDeleteModal('{{ route('admin.certificate.destroy', $cert->id) }}', '{{ addslashes($cert->name) }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Belum ada sertifikat tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ── Filter tabel ────────────────────────────────────────────────────────────
function filterTable() {
    const q = (document.getElementById('searchInput').value || '').toLowerCase();
    const b = (document.getElementById('brandFilter').value || '').toLowerCase();
    const d = (document.getElementById('divisionFilter').value || '').toLowerCase();
    document.querySelectorAll('#certTable tr').forEach(row => {
        const ok = (!q || (row.dataset.search||'').includes(q))
                && (!b || (row.dataset.brand||'') === b)
                && (!d || (row.dataset.division||'') === d);
        row.style.display = ok ? '' : 'none';
    });
}

// ── Salin serial ─────────────────────────────────────────────────────────────
function copySerial(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="h-3.5 w-3.5 text-[#2D8659]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
        setTimeout(() => btn.innerHTML = orig, 1200);
    });
}

// ── Modal hapus ───────────────────────────────────────────────────────────────
function openDeleteModal(action, name) {
    document.getElementById('deleteCertName').textContent = name;
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
document.getElementById('deleteModal').querySelector('.absolute.inset-0').addEventListener('click', closeDeleteModal);

// ── Modal upload ──────────────────────────────────────────────────────────────
const uploadConfig = {
    bg:   { title: 'Upload Background', hint: 'Nama file akan diawali bg_ (contoh: bg_nama.png). Maks 2 MB.', action: '{{ route("admin.uploads.backgrounds.store") }}' },
    logo: { title: 'Upload Logo',        hint: 'Nama file akan diawali logo_ (contoh: logo_nama.png). Maks 2 MB.', action: '{{ route("admin.uploads.logos.store") }}' },
    ttd:  { title: 'Upload Tanda Tangan',hint: 'Nama file akan diawali ttd_ (contoh: ttd_nama.png). Maks 2 MB.',  action: '{{ route("admin.uploads.signatures.store") }}' },
};
function openUploadModal(type) {
    const cfg = uploadConfig[type];
    document.getElementById('uploadModalTitle').textContent = cfg.title;
    document.getElementById('uploadHint').textContent = cfg.hint;
    document.getElementById('uploadForm').action = cfg.action;
    document.getElementById('uploadPreviewWrap').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('hidden');
}
function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}
function previewUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('uploadPreview').src = ev.target.result;
        document.getElementById('uploadPreviewWrap').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
</script>

@endsection
