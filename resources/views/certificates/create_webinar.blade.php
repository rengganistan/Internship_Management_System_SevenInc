@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.certificate.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Sertifikat</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Buat Sertifikat Webinar</h1>
            <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Pilih webinar → pilih peserta yang sudah approved → buat sertifikat sekaligus.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 flex items-center gap-2 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.certificate.webinar.store') }}" id="webinarCertForm">
    @csrf
    <input type="hidden" name="webinar_id" id="hiddenWebinarId">
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Pilih Webinar ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 1 — Pilih Webinar</p>
            <p class="mb-4 text-[12.5px] text-[#4B5F5A]">Pilih webinar yang ingin dibuatkan sertifikatnya. Semua data sertifikat sudah diatur saat membuat webinar.</p>

            @if($webinars->isEmpty())
            <div class="rounded-[10px] border border-dashed border-amber-200 bg-amber-50 py-8 text-center">
                <svg class="mx-auto mb-2 h-8 w-8 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p class="text-[13px] font-semibold text-amber-700">Belum ada webinar</p>
                <p class="mt-1 text-[12px] text-amber-600">
                    <a href="{{ route('admin.webinars.create') }}" class="underline font-semibold">Buat webinar baru</a> terlebih dahulu.
                </p>
            </div>
            @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" id="webinarCards">
                @foreach($webinars as $webinar)
                @php
                    $hasCertConfig = !empty($webinar->certificate_signatory1_name);
                    $pendingCount  = $webinar->approved_without_cert_count ?? 0;
                @endphp
                <button type="button"
                    data-webinar-id="{{ $webinar->id }}"
                    data-has-config="{{ $hasCertConfig ? '1' : '0' }}"
                    onclick="selectWebinar({{ $webinar->id }})"
                    class="webinar-card group text-left rounded-[12px] border-2 border-[#DCE7E1] p-4 transition hover:border-[#2D8659] hover:bg-[#F4F8F6] focus:outline-none {{ old('webinar_id') == $webinar->id ? 'border-[#2D8659] bg-[#F4F8F6]' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-[#1B3A34] leading-snug line-clamp-2">{{ $webinar->title }}</p>
                            <p class="mt-1 text-[11px] text-[#4B5F5A]">
                                {{ $webinar->event_date->format('d M Y') }}
                                @if($webinar->certificate_brand)
                                    · <span class="font-semibold">{{ \App\Models\Webinar::brandLabel($webinar->certificate_brand) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0">
                            @if($pendingCount > 0)
                            <span class="inline-flex items-center rounded-full bg-[#E8F5E9] px-2 py-0.5 text-[10px] font-bold text-[#2D8659]">
                                {{ $pendingCount }} peserta
                            </span>
                            @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-400">
                                Semua selesai
                            </span>
                            @endif
                        </div>
                    </div>

                    @if(!$hasCertConfig)
                    <div class="mt-2 flex items-center gap-1.5 rounded-[6px] bg-amber-50 border border-amber-200 px-2 py-1">
                        <svg class="h-3 w-3 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
                        <span class="text-[10px] text-amber-600 font-semibold">Konfigurasi sertifikat belum lengkap</span>
                    </div>
                    @endif

                    {{-- Selected indicator --}}
                    <div class="selected-indicator mt-2 hidden items-center gap-1.5 rounded-[6px] bg-[#2D8659] px-2 py-1">
                        <svg class="h-3 w-3 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span class="text-[10px] text-white font-semibold">Dipilih</span>
                    </div>
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ===== SEKSI 2: Info Webinar (auto-fill) ===== --}}
        <div id="webinarInfoSection" class="hidden rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Info Webinar Terpilih</p>
            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[13px] sm:grid-cols-4" id="webinarInfoGrid">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A]">Judul</p>
                    <p id="infoTitle" class="mt-0.5 font-semibold text-[#1B3A34]">—</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A]">Tanggal</p>
                    <p id="infoDate" class="mt-0.5 text-[#1B3A34]">—</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A]">Brand</p>
                    <p id="infoBrand" class="mt-0.5 text-[#1B3A34]">—</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A]">Kota</p>
                    <p id="infoCity" class="mt-0.5 text-[#1B3A34]">—</p>
                </div>
            </div>

            {{-- Warning: konfigurasi belum lengkap --}}
            <div id="configWarning" class="hidden mt-4 flex items-start gap-3 rounded-[10px] border border-amber-200 bg-amber-50 px-4 py-3">
                <svg class="h-4 w-4 mt-0.5 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div class="text-[12.5px] text-amber-700">
                    <strong>Konfigurasi sertifikat belum lengkap.</strong>
                    Webinar ini belum memiliki penandatangan. Silakan
                    <a id="editWebinarLink" href="#" class="underline font-semibold">edit webinar</a>
                    untuk melengkapi pengaturan sertifikat sebelum membuat sertifikat.
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 3: Pilih Peserta ===== --}}
        <div id="participantsSection" class="hidden rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Langkah 2 — Pilih Peserta</p>
                    <p class="mt-0.5 text-[12.5px] text-[#4B5F5A]">Peserta yang ditampilkan sudah di-<strong>approve</strong> dan belum memiliki sertifikat untuk webinar ini.</p>
                </div>
            </div>

            {{-- Loading --}}
            <div id="participantsLoading" class="hidden py-8 text-center text-[13px] text-[#4B5F5A]">
                <svg class="mx-auto mb-2 h-6 w-6 animate-spin text-[#2D8659]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Memuat daftar peserta...
            </div>

            {{-- Kosong --}}
            <div id="participantsEmpty" class="hidden rounded-[10px] border border-dashed border-green-200 bg-green-50 py-8 text-center">
                <svg class="mx-auto mb-2 h-8 w-8 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                <p class="text-[13px] font-semibold text-green-700">Semua peserta sudah memiliki sertifikat</p>
                <p class="mt-1 text-[12px] text-green-600">Tidak ada peserta baru yang perlu dibuatkan sertifikat untuk webinar ini.</p>
            </div>

            {{-- Tabel peserta --}}
            <div id="participantsTable" class="hidden overflow-hidden rounded-[10px] border border-[#DCE7E1]">
                <div class="flex items-center justify-between border-b border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5">
                    <label class="flex cursor-pointer items-center gap-2 text-[13px] font-semibold text-[#1B3A34]">
                        <input type="checkbox" id="selectAllParticipants"
                            class="h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659]">
                        Pilih Semua
                    </label>
                    <span id="participantSelectedCount" class="rounded-full bg-[#2D8659] px-2.5 py-0.5 text-[11px] font-bold text-white">0</span>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-10"></th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Nama Peserta</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Email</th>
                        </tr>
                    </thead>
                    <tbody id="participantsTableBody" class="divide-y divide-[#DCE7E1]">
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== Submit ===== --}}
        <div id="submitSection" class="hidden rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-[13px] text-[#4B5F5A]">
                    <span id="submitSummary" class="font-semibold text-[#1B3A34]">0 peserta</span> dipilih
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.certificate.index') }}"
                        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn" disabled
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F] disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span id="submitBtnText">Buat Sertifikat</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = "{{ route('admin.certificate.webinar-participants') }}";

    const hiddenWebinarId      = document.getElementById('hiddenWebinarId');
    const webinarInfoSection   = document.getElementById('webinarInfoSection');
    const participantsSection  = document.getElementById('participantsSection');
    const submitSection        = document.getElementById('submitSection');
    const participantsLoading  = document.getElementById('participantsLoading');
    const participantsEmpty    = document.getElementById('participantsEmpty');
    const participantsTable    = document.getElementById('participantsTable');
    const participantsTableBody= document.getElementById('participantsTableBody');
    const selectAllParticipants= document.getElementById('selectAllParticipants');
    const countBadge           = document.getElementById('participantSelectedCount');
    const submitSummary        = document.getElementById('submitSummary');
    const submitBtn            = document.getElementById('submitBtn');
    const submitBtnText        = document.getElementById('submitBtnText');
    const configWarning        = document.getElementById('configWarning');
    const editWebinarLink      = document.getElementById('editWebinarLink');

    let currentWebinarId = null;

    function updateCount() {
        const checked = participantsTableBody.querySelectorAll('input[name="attendance_ids[]"]:checked').length;
        countBadge.textContent   = checked;
        submitSummary.textContent = checked + ' peserta';
        submitBtn.disabled = checked === 0;
        submitBtnText.textContent = checked > 0 ? `Buat ${checked} Sertifikat` : 'Buat Sertifikat';
        syncSelectAll();
    }

    function syncSelectAll() {
        const all     = participantsTableBody.querySelectorAll('input[name="attendance_ids[]"]');
        const checked = participantsTableBody.querySelectorAll('input[name="attendance_ids[]"]:checked');
        selectAllParticipants.checked       = all.length > 0 && all.length === checked.length;
        selectAllParticipants.indeterminate = checked.length > 0 && checked.length < all.length;
    }

    selectAllParticipants.addEventListener('change', () => {
        participantsTableBody.querySelectorAll('input[name="attendance_ids[]"]').forEach(cb => {
            cb.checked = selectAllParticipants.checked;
        });
        updateCount();
    });

    window.selectWebinar = async function(webinarId) {
        // Visual: deselect semua card, select yang dipilih
        document.querySelectorAll('.webinar-card').forEach(card => {
            card.classList.remove('border-[#2D8659]', 'bg-[#F4F8F6]');
            card.querySelector('.selected-indicator')?.classList.add('hidden');
            card.querySelector('.selected-indicator')?.classList.remove('flex');
        });
        const selectedCard = document.querySelector(`.webinar-card[data-webinar-id="${webinarId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('border-[#2D8659]', 'bg-[#F4F8F6]');
            const ind = selectedCard.querySelector('.selected-indicator');
            ind?.classList.remove('hidden');
            ind?.classList.add('flex');
        }

        currentWebinarId = webinarId;
        hiddenWebinarId.value = webinarId;

        // Tampilkan loading
        webinarInfoSection.classList.remove('hidden');
        participantsSection.classList.remove('hidden');
        submitSection.classList.remove('hidden');
        participantsLoading.classList.remove('hidden');
        participantsEmpty.classList.add('hidden');
        participantsTable.classList.add('hidden');
        participantsTableBody.innerHTML = '';
        updateCount();

        try {
            const res  = await fetch(`${API_URL}?webinar_id=${webinarId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const json = await res.json();

            // Isi info webinar
            document.getElementById('infoTitle').textContent = json.webinar.title;
            document.getElementById('infoDate').textContent  = json.webinar.event_date;
            document.getElementById('infoBrand').textContent = json.webinar.brand_label + ' (' + json.webinar.brand + ')';
            document.getElementById('infoCity').textContent  = json.webinar.city;

            // Tampil/sembunyikan warning konfigurasi
            if (!json.webinar.has_cert_config) {
                configWarning.classList.remove('hidden');
                configWarning.classList.add('flex');
                editWebinarLink.href = `/admin/webinars/${webinarId}/edit`;
                submitBtn.disabled = true;
                participantsLoading.classList.add('hidden');
                return;
            } else {
                configWarning.classList.add('hidden');
                configWarning.classList.remove('flex');
            }

            participantsLoading.classList.add('hidden');

            if (!json.participants || json.participants.length === 0) {
                participantsEmpty.classList.remove('hidden');
                updateCount();
                return;
            }

            // Render tabel peserta
            participantsTable.classList.remove('hidden');
            json.participants.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'transition hover:bg-[#F4F8F6] cursor-pointer';
                tr.innerHTML = `
                    <td class="px-4 py-3">
                        <input type="checkbox" name="attendance_ids[]" value="${p.attendance_id}"
                            class="attendance-check h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659] cursor-pointer">
                    </td>
                    <td class="px-4 py-3 font-semibold text-[13px] text-[#1B3A34]">${p.name}</td>
                    <td class="px-4 py-3 text-[12.5px] text-[#4B5F5A]">${p.email}</td>
                `;
                tr.addEventListener('click', e => {
                    if (e.target.tagName !== 'INPUT') {
                        const cb = tr.querySelector('.attendance-check');
                        cb.checked = !cb.checked;
                        updateCount();
                    }
                });
                tr.querySelector('.attendance-check').addEventListener('change', updateCount);
                participantsTableBody.appendChild(tr);
            });

            // Auto-pilih semua
            participantsTableBody.querySelectorAll('.attendance-check').forEach(cb => cb.checked = true);
            updateCount();

        } catch(e) {
            participantsLoading.classList.add('hidden');
            participantsEmpty.classList.remove('hidden');
        }
    };

    // Konfirmasi submit
    document.getElementById('webinarCertForm').addEventListener('submit', function(e) {
        const count = participantsTableBody.querySelectorAll('.attendance-check:checked').length;
        if (!currentWebinarId) {
            e.preventDefault();
            alert('Pilih webinar terlebih dahulu.');
            return;
        }
        if (count === 0) {
            e.preventDefault();
            alert('Pilih minimal satu peserta.');
            return;
        }
        if (!confirm(`Buat sertifikat untuk ${count} peserta? Proses ini tidak dapat dibatalkan.`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@endsection
