@extends('layouts.dashboard')

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
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus data penilaian?</h3>
                <p class="text-[12.5px] text-[#4B5F5A] leading-relaxed">
                    Penilaian <strong id="deleteAssessName"></strong> akan dihapus permanen.
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

<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Surat Penilaian</h1>
            <p class="mt-1 text-sm text-[#4B5F5A]">Kelola data penilaian magang dan cetak surat penilaian.</p>
        </div>
        <a href="{{ route('interns.assessment.create') }}"
            class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Penilaian
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12">No</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">NIM</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Program Studi</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Divisi</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-center">Rata-rata</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Tanggal</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($data as $i => $row)
                    <tr class="transition hover:bg-[#F4F8F6]">
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-[#1B3A34]">{{ $row->fullname }}</p>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $row->nim_or_nis ?: '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $row->study_program ?: '-' }}</td>
                        <td class="px-5 py-4">
                            @if($row->div)
                            <span class="inline-flex items-center rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[11px] font-semibold text-[#1F5F3F] border border-[#A5D6A7]">
                                {{ $row->div }}
                            </span>
                            @else
                            <span class="text-[13px] text-[#4B5F5A]">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $avg = $row->rata_rata ?? 0;
                                $cls = $avg >= 81 ? 'text-[#388E3C]' : ($avg >= 65 ? 'text-blue-700' : ($avg >= 50 ? 'text-amber-700' : 'text-[#D32F2F]'));
                            @endphp
                            <span class="text-[14px] font-bold {{ $cls }}">{{ number_format($avg, 2) }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-[13px] text-[#4B5F5A]">
                            {{ $row->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Edit --}}
                                <a href="{{ route('interns.assessment.edit', $row->id) }}" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                                {{-- Preview --}}
                                <a href="{{ route('interns.assessment.preview', $row->id) }}" target="_blank" title="Preview PDF"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                {{-- Download PDF --}}
                                <a href="{{ route('interns.assessment.pdf', $row->id) }}" title="Download PDF"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                {{-- Hapus --}}
                                <button type="button" title="Hapus"
                                    onclick="openDeleteModal('{{ route('interns.assessment.destroy', $row->id) }}', '{{ addslashes($row->fullname) }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Belum ada data penilaian magang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openDeleteModal(action, name) {
    document.getElementById('deleteAssessName').textContent = name;
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

@endsection
