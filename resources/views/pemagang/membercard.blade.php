@extends('pemagang.layouts.app')

@section('title', 'Membercard Digital')
@section('breadcrumb', 'Membercard')

@section('content')

<div class="max-w-lg mx-auto">

  {{-- Header --}}
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('pemagang.documents') }}"
       class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-green-400 hover:text-green-700 transition">
      <i class="fas fa-arrow-left text-xs"></i>
    </a>
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Membercard Digital</h2>
      <p class="text-sm text-gray-500">Kartu anggota alumni magang Seveninc</p>
    </div>
  </div>

  {{-- Preview Kartu --}}
  <div class="mb-5 flex justify-center">
    <div style="width:342px; height:216px; background:linear-gradient(135deg,#1a5c38 0%,#0d3d25 60%,#0a2e1c 100%);
                border-radius:16px; padding:20px 24px; position:relative; overflow:hidden; color:white; box-shadow:0 8px 32px rgba(0,0,0,0.25);">

      {{-- Decorative circles --}}
      <div style="position:absolute;width:128px;height:128px;border-radius:50%;background:rgba(255,255,255,0.06);top:-40px;right:-32px;"></div>
      <div style="position:absolute;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);bottom:-20px;left:120px;"></div>
      <div style="position:absolute;width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,0.12);top:32px;right:80px;"></div>

      {{-- Top row --}}
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
        <div style="font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.7);">
          {{ $membercard->brand ?? 'Seveninc' }}
        </div>
        <div style="font-size:9px;color:rgba(255,255,255,0.5);text-align:right;text-transform:uppercase;letter-spacing:1px;line-height:1.4;">
          Intern<br>Member Card
        </div>
      </div>

      {{-- Divider --}}
      <div style="width:32px;height:1.5px;background:rgba(255,255,255,0.3);margin-bottom:8px;"></div>

      {{-- Name --}}
      <div style="font-size:18px;font-weight:bold;letter-spacing:0.5px;color:#fff;margin-bottom:4px;">
        {{ $membercard->name }}
      </div>
      <div style="font-size:11px;color:rgba(255,255,255,0.6);margin-bottom:12px;">
        {{ $reg?->internship_interest ?? 'Magang' }}
      </div>

      {{-- Bottom --}}
      <div style="display:flex;justify-content:space-between;align-items:flex-end;position:absolute;bottom:20px;left:24px;right:24px;">
        <div>
          <div style="margin-bottom:8px;">
            <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Angkatan</div>
            <div style="font-size:11px;font-weight:bold;color:rgba(255,255,255,0.9);">{{ $membercard->angkatan ?? '-' }}</div>
          </div>
          <div>
            <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Instansi</div>
            <div style="font-size:11px;font-weight:bold;color:rgba(255,255,255,0.9);">{{ $membercard->instansi ?? '-' }}</div>
          </div>
        </div>

        <div style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:6px 12px;text-align:center;">
          <div style="font-size:8px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Kode Member</div>
          <div style="font-size:14px;font-weight:bold;font-family:monospace;letter-spacing:2px;color:#a8e6c3;">{{ $membercard->code }}</div>
        </div>
      </div>

    </div>
  </div>

  {{-- Info & Tombol Download --}}
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-sm font-semibold text-gray-800">{{ $membercard->name }}</p>
        <p class="text-xs text-gray-500 mt-0.5">
          Kode: <span class="font-mono font-semibold text-green-700">{{ $membercard->code }}</span>
        </p>
      </div>
      <span class="text-xs px-2.5 py-1 rounded-full font-semibold
        {{ $membercard->has_downloaded ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
        {{ $membercard->has_downloaded ? '✓ Sudah Diunduh' : 'Belum Diunduh' }}
      </span>
    </div>

    <a href="{{ route('pemagang.membercard.download') }}"
       class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold text-white rounded-lg transition"
       style="background-color:#1a5c38;">
      <i class="fas fa-download text-xs"></i>
      Unduh Membercard (PDF)
    </a>
  </div>

</div>

@endsection
