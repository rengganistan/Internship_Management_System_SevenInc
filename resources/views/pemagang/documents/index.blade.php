@extends('pemagang.layouts.app')

@section('title', 'Dokumen Saya')
@section('breadcrumb', 'Dokumen')

@section('content')

@php
  $membercard = auth()->user()->downloads()->latest()->first() ?? null;
@endphp

<h2 class="text-lg font-semibold text-gray-800 mb-1">Dokumen Saya</h2>
<p class="text-sm text-gray-500 mb-6">Unduh surat dan berkas yang berkaitan dengan magang Anda</p>

{{-- ===== MEMBERCARD ===== --}}
@php $status = $registration?->internship_status; @endphp
<div class="bg-white rounded-xl border border-gray-100 p-5 mb-6">
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
         style="background-color:#1a5c38;">
      <i class="fas fa-id-card text-lg"></i>
    </div>
    <div>
      <p class="text-sm font-semibold text-gray-800">Membercard Digital</p>
      <p class="text-xs text-gray-400">Kartu anggota alumni magang Seveninc</p>
    </div>
  </div>

  @if($membercard && $membercard->model_url)
    {{-- Membercard sudah siap --}}
    <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
      <div>
        <p class="text-sm font-medium text-gray-700">{{ $membercard->name }}</p>
        <p class="text-xs text-gray-500">Kode: <span class="font-mono font-semibold">{{ $membercard->code }}</span></p>
        <p class="text-xs text-gray-500">Angkatan: {{ $membercard->angkatan }} · {{ $membercard->brand }}</p>
      </div>
      <a href="{{ route('admin.membercards.show', $membercard->code) }}"
         target="_blank"
         class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg"
         style="background-color:#1a5c38;">
        <i class="fas fa-eye text-xs"></i> Lihat
      </a>
    </div>
  @elseif($membercard && !$membercard->model_url)
    {{-- Record ada tapi model 3D belum diupload admin --}}
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
      <i class="fas fa-clock mr-2"></i>
      Membercard Anda sedang disiapkan oleh admin. Kode: 
      <span class="font-mono font-semibold">{{ $membercard->code }}</span>
    </div>
  @elseif(in_array($status, ['completed', 'active', 'accepted']))
    {{-- Status memenuhi syarat tapi record belum dibuat --}}
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
      <i class="fas fa-info-circle mr-2"></i>
      Membercard Anda sedang dibuat secara otomatis. Hubungi admin jika belum muncul dalam 1x24 jam.
    </div>
  @else
    {{-- Belum memenuhi syarat --}}
    <div class="p-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg text-sm text-gray-500">
      <i class="fas fa-lock mr-2"></i>
      Membercard tersedia setelah status magang menjadi <strong>Aktif</strong> atau <strong>Selesai</strong>.
    </div>
  @endif
</div>

{{-- ===== DOCUMENT CARDS ===== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

  @foreach($docs as $key => $doc)
  <div class="bg-white rounded-xl border border-gray-100 p-5 flex flex-col">

    {{-- Icon --}}
    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4
      {{ $doc['available'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">
      <i class="fas {{ $doc['icon'] }} text-xl"></i>
    </div>

    {{-- Label & description --}}
    <div class="flex-1">
      <p class="text-sm font-semibold text-gray-800 mb-1">{{ $doc['label'] }}</p>
      <p class="text-xs text-gray-400">{{ $doc['description'] }}</p>
      @if($doc['date'])
        <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($doc['date'])->format('d M Y') }}</p>
      @endif
    </div>

    {{-- Action button --}}
    <div class="mt-4">
      @if($doc['available'] && $key === 'loa')
        {{-- LOA butuh POST dengan intern_id --}}
        <form action="{{ route('user.loa.generate') }}" method="POST">
          @csrf
          <input type="hidden" name="intern_id" value="{{ $doc['intern_id'] ?? '' }}">
          <button type="submit"
            class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-white rounded-lg"
            style="background-color:#1a5c38;">
            <i class="fas fa-download text-xs"></i> Unduh PDF
          </button>
        </form>
      @elseif($doc['available'] && $doc['route'])
        <a href="{{ $doc['route'] }}"
           class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-white rounded-lg"
           style="background-color:#1a5c38;">
          <i class="fas fa-download text-xs"></i> Unduh PDF
        </a>
      @elseif($doc['available'] && $key === 'bukti_pendaftaran')
        {{-- Bukti pendaftaran: generate dari data registrasi --}}
        <a href="{{ route('user.loa.preview') }}"
           class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-white rounded-lg"
           style="background-color:#1a5c38;">
          <i class="fas fa-download text-xs"></i> Unduh PDF
        </a>
      @elseif($doc['available'])
        <span class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg cursor-not-allowed">
          <i class="fas fa-check text-xs text-green-600"></i> Tersedia
        </span>
      @else
        <span class="flex items-center justify-center w-full py-2 text-xs text-gray-400 bg-gray-50 rounded-lg border border-dashed border-gray-200">
          Belum tersedia
        </span>
      @endif
    </div>
  </div>
  @endforeach

</div>

{{-- ===== RIWAYAT DOWNLOAD ===== --}}
@if($downloadHistory->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-100 p-5">
  <p class="text-sm font-semibold text-gray-700 mb-4">Riwayat Unduhan</p>
  <div class="space-y-2">
    @foreach($downloadHistory as $dl)
    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-700">
          <i class="fas fa-file-pdf text-sm"></i>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-700">{{ strtoupper($dl->doc_type) }}</p>
          <p class="text-xs text-gray-400">{{ $dl->downloaded_at?->format('d M Y, H:i') }}</p>
        </div>
      </div>
      <span class="text-xs px-2 py-1 rounded-full
        {{ $dl->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
        {{ $dl->status === 'success' ? 'Berhasil' : 'Gagal' }}
      </span>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- Info kalau belum ada dokumen sama sekali --}}
@if($registration === null || $registration->is_draft)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-yellow-800">
  <i class="fas fa-info-circle mr-2"></i>
  Dokumen akan tersedia setelah Anda mengirim form pendaftaran. 
  <a href="{{ route('pemagang.registration.form') }}" class="font-semibold underline">Daftar sekarang</a>.
</div>
@endif

@endsection
