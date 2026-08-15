@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ url()->previous() }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Dokumen & Sertifikat</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Generate SKL</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3">
        <ul class="space-y-1 text-[13px] text-[#D32F2F]">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.skl.generate.download', $intern) }}" method="POST">
    @csrf
    <div class="space-y-5">

        {{-- ===== Kartu Info Pemagang ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Pemagang</p>
            <div class="flex items-center gap-4 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-base font-bold text-[#1F5F3F]">
                    {{ strtoupper(substr($intern->fullname ?? 'P', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-[#1B3A34]">{{ $intern->fullname }}</p>
                    <p class="text-[12px] text-[#4B5F5A]">{{ $intern->email }} · {{ $intern->institution_name }}</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                    Selesai
                </span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1.5 text-[12.5px] sm:grid-cols-4">
                <div><span class="font-semibold text-[#4B5F5A]">NIM/NIS:</span> <span class="text-[#1B3A34]">{{ $intern->student_id ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Prodi:</span> <span class="text-[#1B3A34]">{{ $intern->study_program ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Divisi:</span> <span class="text-[#1B3A34]">{{ $intern->internship_interest ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Brand:</span>
                    <span class="text-[#1B3A34]">
                        @if($intern->brand)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">{{ $intern->brand }}</span>
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div><span class="font-semibold text-[#4B5F5A]">Mulai:</span> <span class="text-[#1B3A34]">{{ $intern->start_date ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Selesai:</span> <span class="text-[#1B3A34]">{{ $intern->end_date ?? '-' }}</span></div>
            </div>
        </div>

        {{-- ===== Data Perusahaan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Perusahaan & Penandatangan</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    @if($intern->brand)
                    <p class="mt-1 text-[11px] text-indigo-600">Otomatis dari brand: <strong>{{ $intern->brand }}</strong></p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Kota <span class="text-red-500">*</span></label>
                    <input type="text" name="company_city" value="{{ old('company_city', $companyCity) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Alamat Perusahaan <span class="text-red-500">*</span></label>
                    <textarea name="company_address" rows="2" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', $companyAddress) }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan <span class="text-red-500">*</span></label>
                    <input type="text" name="leader_name" value="{{ old('leader_name', $leaderName) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan Penandatangan <span class="text-red-500">*</span></label>
                    <input type="text" name="leader_title" value="{{ old('leader_title', $leaderTitle) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

            </div>
        </div>

        {{-- ===== Aset Visual ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aset Visual</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo Perusahaan</label>
                    <select name="logo_select"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">— Gunakan default (logo_seveninc.png) —</option>
                        @foreach($logoFiles as $f)
                        <option value="{{ $f }}" {{ old('logo_select') === $f ? 'selected' : '' }}>{{ basename($f) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan / Stempel</label>
                    <select name="stamp_select"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">— Gunakan default (ttd_arisetiahusbana.png) —</option>
                        @foreach($stampFiles as $f)
                        <option value="{{ $f }}" {{ old('stamp_select') === $f ? 'selected' : '' }}>{{ basename($f) }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ===== Teks Isi SKL ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Isi Surat</p>
            <div class="space-y-4">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Deskripsi Aktivitas</label>
                    <textarea name="activity_description" rows="4"
                        placeholder="Selama magang, yang bersangkutan menunjukkan sikap profesional..."
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('activity_description', $activityDescription) }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Pencapaian Peserta</label>
                    <textarea name="participant_achievement" rows="4"
                        placeholder="Peserta magang juga menunjukkan kemajuan signifikan..."
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('participant_achievement', $participantAchievement) }}</textarea>
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-4">
            <a href="{{ url()->previous() }}"
                class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                Batal
            </a>
            <button type="submit"
                class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Generate & Download SKL
            </button>
        </div>

    </div>
    </form>
</div>
@endsection
