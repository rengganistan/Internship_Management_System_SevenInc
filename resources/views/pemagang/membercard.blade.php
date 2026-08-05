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

  {{-- Preview Kartu — sesuai template baru --}}
  <div class="mb-5 flex justify-center">
    <div style="width:342px; height:216px; background:#1a3a2a; border-radius:12px; position:relative; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.35); font-family:Georgia, serif;">

      {{-- Brand kanan atas --}}
      <div style="position:absolute;top:16px;right:18px;font-size:13px;font-style:italic;font-weight:bold;color:#c9a84c;letter-spacing:0.5px;text-align:right;">
        {{ $membercard->brand ?? 'magangjogja.com' }}
      </div>

      {{-- Nama di tengah --}}
      <div style="position:absolute;top:50%;left:0;right:0;transform:translateY(-65%);text-align:center;padding:0 20px;">
        <div style="font-size:28px;font-weight:normal;color:#c9a84c;letter-spacing:1px;margin-bottom:10px;">
          {{ $membercard->name }}
        </div>
        <div style="width:70%;height:1px;background:#c9a84c;margin:0 auto;"></div>
      </div>

      {{-- Pills bawah --}}
      <div style="position:absolute;bottom:16px;left:18px;right:18px;display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;">
            <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">ID:</span>
            <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $membercard->code }}</span>
          </div>
          <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;">
            <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">Angkatan:</span>
            <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $membercard->angkatan ?? '-' }}</span>
          </div>
        </div>
        <div style="display:flex;gap:8px;">
          <div style="background:#d4c06a;border-radius:8px;padding:3px 10px;display:inline-block;max-width:280px;">
            <span style="font-size:8px;font-weight:bold;color:#1a3a2a;text-transform:uppercase;letter-spacing:0.5px;display:block;line-height:1.3;">Kampus/Sekolah:</span>
            <span style="font-size:10px;color:#1a3a2a;line-height:1.3;">{{ $membercard->instansi ?? '-' }}</span>
          </div>
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
