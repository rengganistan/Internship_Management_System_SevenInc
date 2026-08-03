@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dashboard & Monitoring</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Pengaturan Divisi</h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">Kelola daftar divisi yang tersedia pada form pendaftaran pemagang.</p>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-[#D32F2F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Form Tambah Divisi --}}
        <div class="lg:col-span-1">
            <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-[14px] font-bold text-[#1B3A34]">Tambah Divisi Baru</h2>
                <form method="POST" action="{{ route('admin.form-settings.divisions.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                            Nama Divisi <span class="text-[#D32F2F]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="Contoh: UI/UX Designer"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition @error('name') border-red-300 @enderror">
                        @error('name')
                        <p class="mt-1 text-[11.5px] text-[#D32F2F]">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Divisi
                    </button>
                </form>

                {{-- Info --}}
                <div class="mt-5 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-3 text-[12px] text-[#4B5F5A] space-y-1.5">
                    <p class="font-semibold text-[#1B3A34]">Panduan:</p>
                    <p>Divisi aktif akan muncul di dropdown form pendaftaran pemagang.</p>
                    <p>Nonaktifkan divisi untuk menyembunyikannya tanpa menghapus data lama.</p>
                    <p>Hapus divisi hanya jika yakin tidak ada data pemagang yang menggunakannya.</p>
                </div>
            </div>
        </div>

        {{-- Daftar Divisi --}}
        <div class="lg:col-span-2">
            <div class="rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
                    <div>
                        <h2 class="text-[14px] font-bold text-[#1B3A34]">Daftar Divisi</h2>
                        <p class="text-[11.5px] text-[#4B5F5A] mt-0.5">
                            {{ $divisions->where('is_active', true)->count() }} aktif,
                            {{ $divisions->where('is_active', false)->count() }} nonaktif
                        </p>
                    </div>
                    <span class="text-[12px] text-[#4B5F5A]">Total: <strong class="text-[#1B3A34]">{{ $divisions->count() }}</strong></span>
                </div>

                {{-- Tabel --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead>
                            <tr>
                                <th class="bg-[#1B3A34] px-5 py-3 text-left text-[11px] font-bold uppercase tracking-[0.06em] text-white w-10">#</th>
                                <th class="bg-[#1B3A34] px-5 py-3 text-left text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama Divisi</th>
                                <th class="bg-[#1B3A34] px-5 py-3 text-left text-[11px] font-bold uppercase tracking-[0.06em] text-white w-24">Status</th>
                                <th class="bg-[#1B3A34] px-5 py-3 text-right text-[11px] font-bold uppercase tracking-[0.06em] text-white w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#DCE7E1]" id="divisions-list">
                            @forelse($divisions as $division)
                            <tr data-id="{{ $division->id }}"
                                class="transition hover:bg-[#F4F8F6] {{ !$division->is_active ? 'opacity-60' : '' }}" id="row-{{ $division->id }}">

                                {{-- Urutan --}}
                                <td class="px-5 py-3 text-[13px] text-[#4B5F5A]">{{ $division->sort_order }}</td>

                                {{-- Nama --}}
                                <td class="px-5 py-3">
                                    <span id="name-{{ $division->id }}"
                                        class="font-semibold {{ $division->is_active ? 'text-[#1B3A34]' : 'text-[#4B5F5A] line-through' }}">
                                        {{ $division->name }}
                                    </span>
                                    {{-- Inline edit form --}}
                                    <form id="edit-form-{{ $division->id }}" method="POST"
                                        action="{{ route('admin.form-settings.divisions.update', $division) }}"
                                        class="hidden mt-1">
                                        @csrf @method('PUT')
                                        <div class="flex gap-2">
                                            <input type="text" name="name" value="{{ $division->name }}"
                                                class="flex-1 rounded-[7px] border border-[#2D8659] bg-white px-2.5 py-1.5 text-[13px] text-[#1B3A34] outline-none">
                                            <button type="submit"
                                                class="rounded-[7px] bg-[#2D8659] px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-[#1F5F3F]">
                                                Simpan
                                            </button>
                                            <button type="button" onclick="cancelEdit({{ $division->id }})"
                                                class="rounded-[7px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12px] font-semibold text-[#4B5F5A] hover:bg-[#F4F8F6]">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3">
                                    @if($division->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-[#E8F5E9] px-2 py-0.5 text-[11px] font-semibold text-[#1F5F3F] border border-[#A5D6A7]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#388E3C]"></span>Aktif
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500 border border-gray-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Nonaktif
                                    </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Edit --}}
                                        <button type="button" title="Edit Nama"
                                            onclick="startEdit({{ $division->id }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-[7px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </button>

                                        {{-- Toggle Aktif/Nonaktif --}}
                                        <form method="POST"
                                            action="{{ route('admin.form-settings.divisions.toggle', $division) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                title="{{ $division->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="flex h-7 w-7 items-center justify-center rounded-[7px] border transition
                                                    {{ $division->is_active
                                                        ? 'border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white'
                                                        : 'border-[#A5D6A7] bg-[#E8F5E9] text-[#1F5F3F] hover:bg-[#2D8659] hover:text-white' }}">
                                                @if($division->is_active)
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                @else
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Hapus --}}
                                        <form method="POST"
                                            action="{{ route('admin.form-settings.divisions.destroy', $division) }}"
                                            class="inline"
                                            onsubmit="return confirm('Hapus divisi \"{{ addslashes($division->name) }}\"?\nData pemagang lama yang memilih divisi ini tidak akan terpengaruh.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                class="flex h-7 w-7 items-center justify-center rounded-[7px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-500 hover:text-white">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#4B5F5A]">
                                    Belum ada divisi. Tambahkan divisi pertama di form sebelah kiri.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="border-t border-[#DCE7E1] px-5 py-3 text-[11.5px] text-[#4B5F5A]">
                    Divisi nonaktif tidak akan muncul di form pendaftaran, tapi data pemagang lama tetap tersimpan.
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function startEdit(id) {
    document.getElementById('name-' + id).classList.add('hidden');
    document.getElementById('edit-form-' + id).classList.remove('hidden');
    document.querySelector('#edit-form-' + id + ' input[name="name"]').focus();
}

function cancelEdit(id) {
    document.getElementById('name-' + id).classList.remove('hidden');
    document.getElementById('edit-form-' + id).classList.add('hidden');
}
</script>
@endsection
