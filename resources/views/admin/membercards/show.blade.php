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
                <div style="width:342px;height:216px;background:linear-gradient(135deg,#1a5c38 0%,#0d3d25 60%,#0a2e1c 100%);border-radius:16px;padding:20px 24px;position:relative;overflow:hidden;color:white;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
                    <div style="position:absolute;width:128px;height:128px;border-radius:50%;background:rgba(255,255,255,0.06);top:-40px;right:-32px;"></div>
                    <div style="position:absolute;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);bottom:-20px;left:120px;"></div>
                    <div style="position:absolute;width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,0.12);top:32px;right:80px;"></div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div style="font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.7);">{{ $download->brand ?? 'Seveninc' }}</div>
                        <div style="font-size:9px;color:rgba(255,255,255,0.5);text-align:right;text-transform:uppercase;letter-spacing:1px;line-height:1.4;">Intern<br>Member Card</div>
                    </div>
                    <div style="width:32px;height:1.5px;background:rgba(255,255,255,0.3);margin-bottom:8px;"></div>
                    <div style="font-size:18px;font-weight:bold;color:#fff;margin-bottom:4px;">{{ $download->name }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.6);margin-bottom:12px;">Pemagang</div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;position:absolute;bottom:20px;left:24px;right:24px;">
                        <div>
                            <div style="margin-bottom:8px;">
                                <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Angkatan</div>
                                <div style="font-size:11px;font-weight:bold;color:rgba(255,255,255,0.9);">{{ $download->angkatan ?? '-' }}</div>
                            </div>
                            <div>
                                <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Instansi</div>
                                <div style="font-size:11px;font-weight:bold;color:rgba(255,255,255,0.9);">{{ $download->instansi ?? '-' }}</div>
                            </div>
                        </div>
                        <div style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:6px 12px;text-align:center;">
                            <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Kode Member</div>
                            <div style="font-size:14px;font-weight:bold;font-family:monospace;letter-spacing:2px;color:#a8e6c3;">{{ $download->code }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex items-center gap-3 border-t border-[#DCE7E1] pt-5">
                <a href="{{ route('admin.membercards.edit', $download->code ?? '-') }}"
                    class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
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
