@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Manajemen Pengguna</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Edit Pengguna</h1>
        </div>
    </div>

    <div class="max-w-lg">
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-6 shadow-sm">

            {{-- Info pengguna --}}
            <div class="mb-6 flex items-center gap-4 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                @php
                    $initials = collect(explode(' ', $user->name))->take(2)->map(fn($w)=>strtoupper($w[0]??''))->implode('');
                @endphp
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-sm font-bold text-[#1F5F3F]">
                    {{ $initials }}
                </div>
                <div>
                    <p class="font-semibold text-[#1B3A34]">{{ $user->name }}</p>
                    <p class="text-[12px] text-[#4B5F5A]">{{ $user->email }}</p>
                </div>
            </div>

            @if($errors->any())
            <div class="mb-5 rounded-[9px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#D32F2F]">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')

                {{-- Nama (tampilkan saja, tidak bisa diubah karena controller hanya update role) --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama</label>
                    <div class="rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#4B5F5A]">
                        {{ $user->name }}
                    </div>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Nama tidak dapat diubah dari sini.</p>
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Email</label>
                    <div class="rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#4B5F5A]">
                        {{ $user->email }}
                    </div>
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                        Role <span class="text-[#D32F2F]">*</span>
                    </label>
                    <select name="role" id="role"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none transition focus:border-[#2D8659]">
                        <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                        <option value="user"     {{ $user->role === 'user'     ? 'selected' : '' }}>User</option>
                        <option value="pemagang" {{ $user->role === 'pemagang' ? 'selected' : '' }}>Pemagang</option>
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Perubahan role berlaku segera setelah disimpan.</p>
                </div>

                {{-- Tombol --}}
                <div class="flex items-center gap-3 border-t border-[#DCE7E1] pt-5">
                    <button type="submit"
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
