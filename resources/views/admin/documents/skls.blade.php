@extends('layouts.dashboard')

@section('title', 'Riwayat Unduhan SKL')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Riwayat Unduhan SKL</h1>
                <p class="mt-1 text-sm text-[#4B5F5A]">Daftar pengguna yang telah mengunduh Surat Keterangan Lulus magang.</p>
            </div>
            <a href="{{ route('admin.skl.editor') }}"
                class="flex shrink-0 items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.55V20.3h-3v-.09a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.08 15a1.7 1.7 0 0 0-1.55-1H5.4v-3h.13A1.7 1.7 0 0 0 7.08 10a1.7 1.7 0 0 0-.34-1.88l-.06-.06L8.8 5.94l.06.06A1.7 1.7 0 0 0 10.74 6.34a1.7 1.7 0 0 0 1-1.55V4.7h3v.09a1.7 1.7 0 0 0 1 1.55A1.7 1.7 0 0 0 17.62 6l.06-.06 2.12 2.12-.06.06A1.7 1.7 0 0 0 19.4 10a1.7 1.7 0 0 0 1.55 1h.13v3h-.13A1.7 1.7 0 0 0 19.4 15Z"/></svg>
                Template SKL
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">No.</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama Pengguna</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Tanggal Unduhan</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Status</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($skls as $key => $skl)
                    <tr class="transition hover:bg-[#F4F8F6]">
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $skls->firstItem() + $key }}</td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#1B3A34]">{{ $skl->user?->name ?? '-' }}</p>
                            <p class="mt-0.5 text-[11.5px] text-[#4B5F5A]">{{ $skl->user?->email ?? '' }}</p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-[13px] text-[#4B5F5A]">
                            {{ $skl->downloaded_at?->format('d M Y, H:i') ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($skl->status === 'success')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[11px] font-semibold text-[#388E3C] border border-[#A5D6A7]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#388E3C]"></span>Berhasil
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-[#D32F2F] border border-red-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>Gagal
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($skl->file_path)
                            @php
                                $sklRawPath  = $skl->file_path;
                                // Handle absolute path lama (C:\xampp\...) maupun relative path baru
                                $sklFilename = basename(str_replace('\\', '/', $sklRawPath));
                                // Deteksi type dari path
                                if (str_contains($sklRawPath, 'documents/skl') || str_contains($sklRawPath, 'documents\\skl')) {
                                    $sklType = 'skl';
                                } elseif (str_contains($sklRawPath, 'documents/loa') || str_contains($sklRawPath, 'documents\\loa')) {
                                    $sklType = 'loa';
                                } else {
                                    $sklType = 'tmp';
                                }
                            @endphp
                            <a href="{{ route('admin.documents.serve', ['type' => $sklType, 'filename' => $sklFilename]) }}"
                               target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-[8px] border border-[#DCE7E1] px-3 py-1.5 text-[12.5px] font-semibold text-[#2D8659] transition hover:bg-[#F4F8F6]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/></svg>
                                Lihat SKL
                            </a>
                            @else
                            <span class="text-[12.5px] text-[#4B5F5A]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Belum ada riwayat unduhan SKL.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#DCE7E1] px-5 py-4">
            {{ $skls->links() }}
        </div>
    </div>
</div>
@endsection
