@extends('layouts.dashboard')

@section('content')

{{-- Modal: Lihat Detail Feedback --}}
<div id="feedbackDetailModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" onclick="closeFeedbackModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
                <h3 class="text-[15px] font-bold text-[#1B3A34]">Detail Feedback</h3>
                <button onclick="closeFeedbackModal()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-3 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                    <div id="fbDetailAvatar" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-sm font-bold text-[#1F5F3F]">—</div>
                    <div>
                        <p id="fbDetailName" class="font-semibold text-[#1B3A34]">—</p>
                        <p id="fbDetailDate" class="text-[11.5px] text-[#4B5F5A]">—</p>
                    </div>
                </div>
                <div>
                    <p class="mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-[#4B5F5A]">Isi Feedback</p>
                    <p id="fbDetailText" class="rounded-[8px] bg-[#F4F8F6] px-4 py-3 text-[13px] leading-relaxed text-[#1B3A34]">—</p>
                </div>
            </div>
            <div class="flex justify-end border-t border-[#DCE7E1] px-5 py-4">
                <button onclick="closeFeedbackModal()"
                    class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Konfirmasi Hapus --}}
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
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus feedback ini?</h3>
                <p class="text-[12.5px] text-[#4B5F5A]">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="flex justify-center gap-3 border-t border-[#DCE7E1] px-5 py-4">
                <button onclick="closeDeleteModal()"
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
    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Feedback</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">Feedback Pemagang</h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">Masukan dan komentar dari pemagang selama program berlangsung.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12">No</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama Pengguna</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Feedback</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Tanggal</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#DCE7E1]">
                    @forelse($feedbacks as $index => $feedback)
                    @php
                        $initials = collect(explode(' ', $feedback->name ?? '-'))->take(2)->map(fn($w)=>strtoupper($w[0]??''))->implode('');
                    @endphp
                    <tr class="transition hover:bg-[#F4F8F6]">
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">{{ $index + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-[12px] font-bold text-[#1F5F3F]">
                                    {{ $initials }}
                                </div>
                                <p class="font-semibold text-[#1B3A34]">{{ $feedback->name }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">
                            {{ \Illuminate\Support\Str::limit($feedback->feedback, 80) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-[13px] text-[#4B5F5A]">
                            {{ $feedback->created_at?->format('d M Y, H:i') ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Lihat detail --}}
                                <button type="button" title="Lihat Detail"
                                    onclick="openFeedbackModal('{{ addslashes($feedback->name) }}', '{{ addslashes($feedback->feedback) }}', '{{ $feedback->created_at?->format('d M Y, H:i') }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                {{-- Hapus --}}
                                <button type="button" title="Hapus"
                                    onclick="openDeleteModal('{{ route('admin.feedback.destroy', $feedback->id) }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                            Belum ada feedback dari pemagang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Modal detail
function openFeedbackModal(name, text, date) {
    const ini = name.split(' ').slice(0,2).map(w=>w[0]?.toUpperCase()||'').join('') || '?';
    document.getElementById('fbDetailAvatar').textContent = ini;
    document.getElementById('fbDetailName').textContent   = name;
    document.getElementById('fbDetailDate').textContent   = date;
    document.getElementById('fbDetailText').textContent   = text;
    document.getElementById('feedbackDetailModal').classList.remove('hidden');
}
function closeFeedbackModal() {
    document.getElementById('feedbackDetailModal').classList.add('hidden');
}

// Modal hapus
function openDeleteModal(action) {
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

@endsection
