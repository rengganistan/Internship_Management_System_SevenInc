@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.membercards.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Member Card</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Detail Member Card</h1>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-[10px] border border-[#A5D6A7] bg-[#E8F5E9] px-4 py-3 text-sm font-semibold text-[#1F5F3F]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="max-w-2xl">
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-6 shadow-sm">

            {{-- Header card --}}
            <div class="mb-6 flex items-center gap-4 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                @php
                    $initials = collect(explode(' ', $download->name))->take(2)->map(fn($w)=>strtoupper($w[0]??''))->implode('');
                @endphp
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-sm font-bold text-[#1F5F3F]">
                    {{ $initials }}
                </div>
                <div>
                    <p class="font-bold text-[#1B3A34]">{{ $download->name }}</p>
                    <p class="text-[12px] text-[#4B5F5A]">Kode: <code class="font-mono">{{ $download->code ?? '-' }}</code></p>
                </div>
                @if($download->has_downloaded)
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[11px] font-semibold text-[#388E3C] border border-[#A5D6A7]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#388E3C]"></span>Sudah Diunduh
                </span>
                @else
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-[#F4F8F6] px-2.5 py-1 text-[11px] font-semibold text-[#4B5F5A] border border-[#DCE7E1]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#4B5F5A]"></span>Belum Diunduh
                </span>
                @endif
            </div>

            {{-- Data --}}
            <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-[13px]">
                @php
                    $rows = [
                        ['Angkatan', $download->angkatan],
                        ['Instansi', $download->instansi],
                        ['Brand', $download->brand],
                        ['Diunduh Pada', $download->downloaded_at ? \Carbon\Carbon::parse($download->downloaded_at)->format('d M Y, H:i') : '-'],
                    ];
                @endphp
                @foreach($rows as [$label, $val])
                <div>
                    <p class="text-[10.5px] font-semibold uppercase tracking-wide text-[#4B5F5A] mb-0.5">{{ $label }}</p>
                    <p class="font-medium text-[#1B3A34] break-all">{{ $val ?: '-' }}</p>
                </div>
                @endforeach
            </div>

            {{-- Preview Kartu --}}
            <div class="mt-6 border-t border-[#DCE7E1] pt-5">
                <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-[#4B5F5A]">Preview Membercard</p>
                <div style="width:342px;height:216px;background:#1a3a2a;border-radius:12px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.25);font-family:Georgia,serif;">

                    {{-- Brand kanan atas --}}
                    <div style="position:absolute;top:16px;right:18px;font-size:13px;font-style:italic;font-weight:bold;color:#c9a84c;letter-spacing:0.5px;text-align:right;">
                        {{ $download->brand ?? 'magangjogja.com' }}
                    </div>

                    {{-- Nama di tengah --}}
                    <div style="position:absolute;top:50%;left:0;right:0;transform:translateY(-65%);text-align:center;padding:0 20px;">
                        <div style="font-size:26px;font-weight:normal;color:#c9a84c;letter-spacing:1px;margin-bottom:10px;">
                            {{ $download->name }}
                        </div>
                        <div style="width:70%;height:1px;background:#c9a84c;margin:0 auto;"></div>
                    </div>

                    {{-- Pills bawah --}}
                    <div style="position:absolute;bottom:16px;left:18px;right:18px;display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;">
                                <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">ID:</span>
                                <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $download->code ?? '-' }}</span>
                            </div>
                            <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;">
                                <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">Angkatan:</span>
                                <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $download->angkatan ?? '-' }}</span>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;max-width:280px;">
                                <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">Kampus/Sekolah:</span>
                                <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $download->instansi ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex items-center gap-3 border-t border-[#DCE7E1] pt-5">
                {{-- Generate --}}
                <form action="{{ route('admin.membercards.generate.one', $download->code ?? '-') }}"
                      method="POST">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Generate ulang membercard untuk {{ addslashes($download->name) }}? Pastikan status pemagang sudah Selesai.')"
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
                        Generate Membercard
                    </button>
                </form>
                <a href="{{ route('admin.membercards.edit', $download->code ?? '-') }}"
                    class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] transition hover:bg-[#F4F8F6]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    Edit Data
                </a>
                <a href="{{ route('admin.membercards.index') }}"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
