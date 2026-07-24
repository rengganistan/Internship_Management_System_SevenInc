@extends('layouts.dashboard')
@section('title', 'Tugas Pending — ' . ($user->name ?? 'User'))

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">{{ $user->name ?? 'Pengguna' }}</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Tugas Pending</h1>
        </div>
    </div>

    {{-- Subpage nav --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.user.dailyReports', $user) }}"
            class="rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            Laporan Harian
        </a>
        <a href="{{ route('admin.user.leaveRequests', $user) }}"
            class="rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            Pengajuan Izin
        </a>
        <span class="rounded-[8px] bg-[#2D8659] px-3 py-1.5 text-[12.5px] font-semibold text-white">Tugas Pending</span>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-5 rounded-[12px] border border-[#DCE7E1] bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11.5px] font-semibold text-[#4B5F5A]">Cari judul / deskripsi</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kata kunci..."
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
            </div>
            <div>
                <label class="mb-1 block text-[11.5px] font-semibold text-[#4B5F5A]">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
            </div>
            <div>
                <label class="mb-1 block text-[11.5px] font-semibold text-[#4B5F5A]">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Terapkan
            </button>
            <a href="{{ route('admin.user.pendingTasks', $user) }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-[13px] font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Reset
            </a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Dibuat</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Judul</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($tasks as $t)
                    <tr class="transition hover:bg-[#F4F8F6]">
                        <td class="whitespace-nowrap px-5 py-4 text-[12.5px] text-[#4B5F5A]">
                            {{ $t->created_at?->format('d M Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-[#1B3A34]">{{ $t->title }}</td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">
                            {{ \Illuminate\Support\Str::limit($t->description, 160) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm text-[#4B5F5A]">
                            Tidak ada data untuk filter saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#DCE7E1] px-5 py-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
