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
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Generate LOA</h1>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-2 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-[13px] font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {!! session('success') !!}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 flex items-center gap-2 rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-semibold text-[#D32F2F]">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('admin.loa.generate') }}" method="POST">
    @csrf
    {{-- Kirim intern_id sebagai hidden --}}
    <input type="hidden" name="intern_id" value="{{ $intern->id }}">

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
                <span class="inline-flex items-center rounded-full bg-[#E8F5E9] border border-[#A5D6A7] px-2.5 py-1 text-[11px] font-semibold text-[#1F5F3F]">
                    Diterima
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
                        @else -
                        @endif
                    </span>
                </div>
                <div><span class="font-semibold text-[#4B5F5A]">Mulai:</span> <span class="text-[#1B3A34]">{{ $intern->start_date ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Selesai:</span> <span class="text-[#1B3A34]">{{ $intern->end_date ?? '-' }}</span></div>
                <div><span class="font-semibold text-[#4B5F5A]">Kontak:</span> <span class="text-[#1B3A34]">{{ $intern->phone_number ?? '-' }}</span></div>
            </div>
        </div>

        {{-- ===== Data Perusahaan & Penandatangan ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Perusahaan & Penandatangan</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
                    <input type="text" name="company_name_display" value="{{ old('company_name_display', $companyName) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    @if($intern->brand)
                    <p class="mt-1 text-[11px] text-indigo-600">Otomatis dari brand: <strong>{{ $intern->brand }}</strong></p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Email Kontak Perusahaan</label>
                    <input type="text" name="contact_email" value="{{ old('contact_email', $loaSettings?->company_contact_email ?? '') }}"
                        placeholder="info@perusahaan.com"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan <span class="text-red-500">*</span></label>
                    <input type="text" name="signatory_name" value="{{ old('signatory_name', $signatoryName) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan Penandatangan <span class="text-red-500">*</span></label>
                    <input type="text" name="signatory_position" value="{{ old('signatory_position', $signatoryPosition) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

            </div>
        </div>

        {{-- ===== Teks LOA ===== --}}
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Isi Surat</p>
            <div class="space-y-4">

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Paragraf Pembuka</label>
                    <textarea name="openingGreeting" rows="3"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('openingGreeting', $openingGreeting) }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Paragraf Penutup</label>
                    <textarea name="closingGreeting" rows="3"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('closingGreeting', $closingGreeting) }}</textarea>
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
                Generate & Simpan LOA
            </button>
        </div>

    </div>
    </form>
</div>
@endsection
