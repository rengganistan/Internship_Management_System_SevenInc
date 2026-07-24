@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.membercards.show', $download->code) }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Member Card</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Edit Member Card</h1>
        </div>
    </div>

    <div class="max-w-lg">
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-6 shadow-sm">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-5 rounded-[9px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-[#D32F2F]">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.membercards.update', $download->code) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf @method('PUT')

                {{-- Nama --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                        Nama <span class="text-[#D32F2F]">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $download->name) }}" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                {{-- Angkatan --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Angkatan</label>
                    <input type="text" name="angkatan" value="{{ old('angkatan', $download->angkatan) }}"
                        placeholder="contoh: 2025"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                {{-- Instansi --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Instansi</label>
                    <input type="text" name="instansi" value="{{ old('instansi', $download->instansi) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                </div>

                {{-- Brand --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $download->brand) }}"
                        placeholder="contoh: magangjogja.com"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Perubahan brand akan memperbarui kode member card secara otomatis.</p>
                </div>

                {{-- Pilih Model GLB --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Model 3D (.glb)</label>
                    <select name="model_url"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-white px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                        <option value="">-- Pilih File GLB --</option>
                        @foreach($glbFiles as $file)
                        <option value="{{ 'storage/models/'.$file }}"
                            {{ old('model_url', $download->model_url) === 'storage/models/'.$file ? 'selected' : '' }}>
                            {{ $file }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Upload GLB --}}
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">Upload Model Baru (.glb)</label>
                    <input type="file" name="model_upload" accept=".glb"
                        class="block w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#4B5F5A] outline-none focus:border-[#2D8659]">
                    <p class="mt-1 text-[11px] text-[#4B5F5A]">Upload akan mengganti pilihan dropdown di atas.</p>
                    @error('model_upload')
                    <p class="mt-1 text-[12px] text-[#D32F2F]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 border-t border-[#DCE7E1] pt-5">
                    <button type="submit"
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Simpan
                    </button>
                    <a href="{{ route('admin.membercards.show', $download->code) }}"
                        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
