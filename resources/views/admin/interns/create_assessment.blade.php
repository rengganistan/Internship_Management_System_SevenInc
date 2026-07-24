@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('interns.assessment.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Surat Penilaian</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Tambah Penilaian Magang</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('interns.assessment.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="space-y-5">

        {{-- ===== SEKSI 1: Data Pemagang ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Pemagang</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- Cari pemagang --}}
                <div class="relative sm:col-span-2">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Cari Nama Pemagang</label>
                    <div class="flex items-center gap-2 rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2">
                        <svg class="h-4 w-4 shrink-0 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="searchIntern" placeholder="Ketik nama pemagang..." autocomplete="off"
                            onkeyup="filterInternList()"
                            class="w-full border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A]">
                    </div>
                    <input type="hidden" name="fullname" id="fullname_hidden">
                    <div id="internDropdown"
                        class="absolute z-20 hidden mt-1 max-h-56 w-full overflow-y-auto rounded-[10px] border border-[#DCE7E1] bg-white shadow-lg">
                        @foreach($interns as $intern)
                        <div class="intern-option cursor-pointer px-4 py-2.5 text-[13px] text-[#1B3A34] hover:bg-[#F4F8F6]"
                            data-name="{{ $intern->fullname }}"
                            data-nim="{{ $intern->student_id }}"
                            data-prodi="{{ $intern->study_program }}">
                            {{ $intern->fullname }}
                            <span class="text-[11px] text-[#4B5F5A]">— {{ $intern->study_program }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">NIM / NIS</label>
                    <input type="text" id="nimField" name="nim_or_nis" value="{{ old('nim_or_nis') }}"
                        placeholder="Masukkan NIM/NIS"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Program Studi</label>
                    <input type="text" id="prodiField" name="study_program" value="{{ old('study_program') }}"
                        placeholder="Contoh: Teknik Informatika"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Divisi / Kompetensi Keahlian</label>
                    <select name="div" id="divisionSelect"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $div)
                        <option value="{{ $div }}" {{ old('div') === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 2: Data Perusahaan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Perusahaan & Penandatangan</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                    <input type="text" name="company_name"
                        value="{{ old('company_name', 'SEVEN INC.') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan</label>
                    <input type="text" name="signature_name" value="{{ old('signature_name') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
                    <textarea name="company_address" rows="2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', 'Jl. Raya Janti, Gang Arjuna No. 59, Karangjambe, Banguntapan, Bantul, Yogyakarta') }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan Penandatangan</label>
                    <input type="text" name="signature_position" value="{{ old('signature_position') }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo Perusahaan</label>
                    <select name="company_logo_select"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih Logo --</option>
                        @foreach($logos as $logo)
                        <option value="{{ $logo }}" {{ old('company_logo_select') === $logo ? 'selected' : '' }}>{{ basename($logo) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Atau upload baru:</p>
                    <input type="file" name="company_logo" accept="image/*"
                        class="mt-1 block w-full text-[12.5px] text-[#4B5F5A]">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan</label>
                    <select name="signature_image_select"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih Tanda Tangan --</option>
                        @foreach($signatures as $sig)
                        <option value="{{ $sig }}" {{ old('signature_image_select') === $sig ? 'selected' : '' }}>{{ basename($sig) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Atau upload baru:</p>
                    <input type="file" name="signature_image" accept="image/*"
                        class="mt-1 block w-full text-[12.5px] text-[#4B5F5A]">
                </div>
            </div>
        </div>

        {{-- ===== SEKSI 3: Aspek Penilaian ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aspek Penilaian</p>

            <div class="overflow-x-auto">
                <table id="tableAspek" class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12 text-center">No</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Aspek Penilaian</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-28 text-center">Nilai</th>
                            <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE7E1]" id="aspekTbody">
                        @foreach($aspects ?? [['aspek'=>'Kedisiplinan','nilai'=>95]] as $i => $item)
                        <tr class="hover:bg-[#F4F8F6]">
                            <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]">{{ $i + 1 }}</td>
                            <td class="px-3 py-2">
                                <input type="text" name="aspek[]" value="{{ $item['aspek'] }}"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="nilai[]" value="{{ $item['nilai'] }}" min="0" max="100"
                                    oninput="updateAvg()"
                                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" onclick="deleteRow(this)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-[#DCE7E1] bg-[#F4F8F6]">
                            <td colspan="2" class="px-4 py-3 text-right text-[13px] font-bold text-[#1B3A34]">Rata-rata</td>
                            <td class="px-4 py-3 text-center text-[15px] font-bold text-[#2D8659]" id="avg">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-[#DCE7E1] pt-4">
                <button type="button" onclick="addRow()"
                    class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Aspek
                </button>
                <p class="text-[11px] text-[#4B5F5A]">81–100: Amat Baik &nbsp;|&nbsp; 65–80: Baik &nbsp;|&nbsp; 50–64: Cukup &nbsp;|&nbsp; &lt;50: Kurang</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('interns.assessment.index') }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Batal
            </a>
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Simpan Penilaian
            </button>
        </div>

    </div>
    </form>
</div>

<script>
// ── Cari pemagang ────────────────────────────────────────────────────────────
const searchInput    = document.getElementById('searchIntern');
const dropdown       = document.getElementById('internDropdown');
const allOptions     = dropdown.querySelectorAll('.intern-option');
const fullnameHidden = document.getElementById('fullname_hidden');
const nimField       = document.getElementById('nimField');
const prodiField     = document.getElementById('prodiField');

searchInput.addEventListener('focus', () => dropdown.classList.remove('hidden'));
document.addEventListener('click', e => {
    if (!dropdown.contains(e.target) && e.target !== searchInput) dropdown.classList.add('hidden');
});

function filterInternList() {
    const q = searchInput.value.toLowerCase();
    allOptions.forEach(opt => {
        opt.style.display = opt.dataset.name.toLowerCase().includes(q) ? '' : 'none';
    });
    dropdown.classList.remove('hidden');
}

allOptions.forEach(opt => {
    opt.addEventListener('click', () => {
        searchInput.value      = opt.dataset.name;
        fullnameHidden.value   = opt.dataset.name;
        nimField.value         = opt.dataset.nim || '';
        prodiField.value       = opt.dataset.prodi || '';
        dropdown.classList.add('hidden');
    });
});

// ── Rata-rata ────────────────────────────────────────────────────────────────
function updateAvg() {
    const inputs = document.querySelectorAll('input[name="nilai[]"]');
    let total = 0, count = 0;
    inputs.forEach(i => { const v = parseFloat(i.value); if (!isNaN(v)) { total += v; count++; } });
    document.getElementById('avg').textContent = count ? (total / count).toFixed(2) : '0';
}

function updateNumbers() {
    document.querySelectorAll('#aspekTbody tr').forEach((r, i) => r.cells[0].textContent = i + 1);
}

// ── Tambah baris ─────────────────────────────────────────────────────────────
function addRow() {
    const tbody = document.getElementById('aspekTbody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-[#F4F8F6] divide-y divide-[#DCE7E1]';
    tr.innerHTML = `
        <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]"></td>
        <td class="px-3 py-2">
            <input type="text" name="aspek[]" value="Aspek Baru"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </td>
        <td class="px-3 py-2">
            <input type="number" name="nilai[]" value="0" min="0" max="100" oninput="updateAvg()"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </td>
        <td class="px-3 py-2 text-center">
            <button type="button" onclick="deleteRow(this)"
                class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </td>`;
    tbody.appendChild(tr);
    updateNumbers();
    updateAvg();
}

function deleteRow(btn) {
    btn.closest('tr').remove();
    updateNumbers();
    updateAvg();
}

// ── Load aspek berdasarkan divisi ─────────────────────────────────────────────
document.getElementById('divisionSelect').addEventListener('change', function() {
    if (!this.value) return;
    fetch("{{ route('ajax.aspek') }}?division=" + encodeURIComponent(this.value))
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('aspekTbody');
            tbody.innerHTML = '';
            (data.aspek || []).forEach((item, i) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-[#F4F8F6]';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]">${i+1}</td>
                    <td class="px-3 py-2"><input type="text" name="aspek[]" value="${item.aspek}" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
                    <td class="px-3 py-2"><input type="number" name="nilai[]" value="${item.nilai}" min="0" max="100" oninput="updateAvg()" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition"></td>
                    <td class="px-3 py-2 text-center"><button type="button" onclick="deleteRow(this)" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>`;
                tbody.appendChild(tr);
            });
            updateAvg();
        });
});

updateNumbers();
updateAvg();
</script>

@endsection
