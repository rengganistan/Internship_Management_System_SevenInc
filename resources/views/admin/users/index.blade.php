@extends('layouts.dashboard')

@section('content')

{{-- Modal Konfirmasi Hapus --}}
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
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus pengguna?</h3>
                <p class="text-[12.5px] text-[#4B5F5A] leading-relaxed">
                    Akun <strong id="deleteUserName"></strong> akan dihapus permanen dan tidak dapat dikembalikan.
                </p>
            </div>
            <div class="flex justify-center gap-3 border-t border-[#DCE7E1] px-5 py-4">
                <button type="button" onclick="closeDeleteModal()"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="inline">
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

{{-- Toast --}}
<div id="toastStack" class="fixed bottom-5 right-5 z-[200] flex flex-col gap-2"></div>

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Manajemen Pengguna</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Semua Pengguna</h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">Kelola akun pengguna, role, dan status aktivitas dalam sistem.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Card --}}
    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">

        {{-- Toolbar --}}
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="flex flex-wrap items-center gap-2 border-b border-[#DCE7E1] px-4 py-4 sm:px-6">

                {{-- Search nama --}}
                <label class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2">
                    <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="name" value="{{ request('name') }}" placeholder="Cari nama..."
                        class="w-36 border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A]">
                </label>

                {{-- Search email --}}
                <label class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2">
                    <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <input type="text" name="email" value="{{ request('email') }}" placeholder="Cari email..."
                        class="w-40 border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A]">
                </label>

                {{-- Filter role dropdown --}}
                <select name="role"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                    <option value=""        {{ request('role','') === ''        ? 'selected':'' }}>Semua Role</option>
                    <option value="admin"   {{ request('role','') === 'admin'   ? 'selected':'' }}>Admin</option>
                    <option value="pemagang"{{ request('role','') === 'pemagang'? 'selected':'' }}>Pemagang</option>
                </select>


                {{-- Tombol Filter --}}
                <button type="submit"
                    class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filter
                </button>

                @if(request()->hasAny(['name','email','role','sort']))
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-1.5 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:text-[#D32F2F]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Reset
                </a>
                @endif
            </div>
        </form>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Pengguna</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Email</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Role</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Status</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($users as $user)
                    @php
                        $role = strtolower($user->role ?? 'user');
                        $roleMeta = match($role) {
                            'admin'    => ['label'=>'Admin',    'cls'=>'bg-purple-50 text-purple-700 border border-purple-200'],
                            'pemagang' => ['label'=>'Pemagang', 'cls'=>'bg-[#E8F5E9] text-[#1F5F3F] border border-[#A5D6A7]'],
                            default    => ['label'=>'User',     'cls'=>'bg-blue-50 text-blue-700 border border-blue-200'],
                        };
                        $initials = collect(explode(' ', $user->name))->take(2)->map(fn($w)=>strtoupper($w[0]??''))->implode('');
                    @endphp
                    <tr class="transition hover:bg-[#F4F8F6]">

                        {{-- Pengguna --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-sm font-bold text-[#1F5F3F]">
                                    {{ $initials }}
                                    @if($user->is_online)
                                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-[#388E3C]"></span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-[#1B3A34]">{{ $user->name }}</p>
                                    <p class="mt-0.5 text-[11.5px] text-[#4B5F5A]">ID #{{ $user->id }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $user->email }}</td>

                        {{-- Role --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $roleMeta['cls'] }}">
                                {{ $roleMeta['label'] }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($user->is_online)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[11px] font-semibold text-[#388E3C] border border-[#A5D6A7]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#388E3C] animate-pulse"></span>
                                Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F4F8F6] px-2.5 py-1 text-[11px] font-semibold text-[#4B5F5A] border border-[#DCE7E1]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#4B5F5A]"></span>
                                Offline
                            </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Lihat laporan --}}
                                <a href="{{ route('admin.user.dailyReports', $user->id) }}" title="Laporan Harian"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user->id) }}" title="Edit Pengguna"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                                {{-- Hapus --}}
                                <button type="button" title="Hapus Pengguna"
                                    onclick="openDeleteModal('{{ route('admin.users.destroy', $user->id) }}', '{{ addslashes($user->name) }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Tidak ada pengguna yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between border-t border-[#DCE7E1] px-5 py-4 text-[12.5px] text-[#4B5F5A]">
            <span>Menampilkan <strong class="text-[#1B3A34]">{{ $users->firstItem() ?? 0 }}</strong>–<strong class="text-[#1B3A34]">{{ $users->lastItem() ?? 0 }}</strong> dari <strong class="text-[#1B3A34]">{{ $users->total() }}</strong> pengguna</span>
            <div>{{ $users->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

<script>
function openDeleteModal(action, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

@endsection
