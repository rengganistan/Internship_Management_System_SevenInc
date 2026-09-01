{{--
    Partial: Data Penandatangan
    Variables:
        $prefix      — 'single' or 'bulk'
        $signatory   — AssessmentSignatorySetting|null
        $brand       — string|null
        $logos       — array
        $signatures  — array
--}}
<div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Perusahaan & Penandatangan</p>
        <button type="button" id="{{ $prefix }}SaveSignatoryBtn"
            onclick="saveSignatoryNow('{{ $prefix }}')"
            class="flex items-center gap-1.5 rounded-[8px] border border-[#2D8659] bg-white px-3 py-1.5 text-[12px] font-semibold text-[#2D8659] hover:bg-[#F4F8F6] transition">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
            Simpan untuk Brand Ini
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Perusahaan</label>
            <input type="text" name="company_name" id="{{ $prefix }}CompanyName"
                value="{{ old('company_name', $signatory?->company_name ?? ($brand ?? 'SEVEN INC.')) }}"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Nama Penandatangan</label>
            <input type="text" name="signature_name" id="{{ $prefix }}SignatureName"
                value="{{ old('signature_name', $signatory?->signature_name ?? '') }}"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
            <textarea name="company_address" id="{{ $prefix }}CompanyAddress" rows="2"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('company_address', $signatory?->company_address ?? 'Jl. Raya Janti, Gang Arjuna No. 59, Karangjambe, Banguntapan, Bantul, Yogyakarta') }}</textarea>
        </div>

        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Jabatan Penandatangan</label>
            <input type="text" name="signature_position" id="{{ $prefix }}SignaturePosition"
                value="{{ old('signature_position', $signatory?->signature_position ?? '') }}"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>

        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Logo Perusahaan</label>
            <select name="company_logo_select" id="{{ $prefix }}LogoSelect"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                <option value="">-- Pilih Logo --</option>
                @foreach($logos as $logo)
                <option value="{{ $logo }}" {{ (old('company_logo_select', $signatory?->company_logo_path ?? '') === $logo) ? 'selected' : '' }}>{{ basename($logo) }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-[11px] text-[#4B5F5A]">Atau upload baru:</p>
            <input type="file" name="company_logo" accept="image/*"
                class="mt-1 block w-full text-[12.5px] text-[#4B5F5A]">
        </div>

        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Tanda Tangan</label>
            <select name="signature_image_select" id="{{ $prefix }}SigSelect"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                <option value="">-- Pilih Tanda Tangan --</option>
                @foreach($signatures as $sig)
                <option value="{{ $sig }}" {{ (old('signature_image_select', $signatory?->signature_image_path ?? '') === $sig) ? 'selected' : '' }}>{{ basename($sig) }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-[11px] text-[#4B5F5A]">Atau upload baru:</p>
            <input type="file" name="signature_image" accept="image/*"
                class="mt-1 block w-full text-[12.5px] text-[#4B5F5A]">
        </div>

        {{-- Checkbox simpan otomatis --}}
        <div class="sm:col-span-2">
            <label class="flex items-center gap-2 text-[12.5px] text-[#4B5F5A] cursor-pointer">
                <input type="checkbox" name="save_signatory" value="1"
                    class="h-4 w-4 rounded border-[#DCE7E1] accent-[#2D8659]">
                Simpan data penandatangan ini untuk brand terpilih agar bisa dipakai lagi
            </label>
        </div>

    </div>
</div>

{{-- Script saveSignatoryNow & showToast ada di create_assessment.blade.php (satu kali saja) --}}
