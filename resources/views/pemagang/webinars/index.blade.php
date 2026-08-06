@extends('pemagang.layouts.app')

@section('title', 'Webinar')
@section('breadcrumb', 'Webinar')

@section('content')

<h2 class="text-lg font-semibold text-gray-800 mb-1">Webinar</h2>
<p class="text-sm text-gray-500 mb-6">Ikuti webinar dan dapatkan sertifikat kehadiran.</p>

@if($webinars->isEmpty())
  <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
    <i class="fas fa-video text-4xl text-gray-300 mb-4"></i>
    <p class="text-gray-500 font-medium">Belum ada webinar yang tersedia.</p>
    <p class="text-gray-400 text-sm mt-1">Cek kembali nanti untuk info webinar terbaru.</p>
  </div>
@else
<div class="space-y-4">
  @foreach($webinars as $webinar)
  @php $att = $webinar->my_attendance; @endphp
  <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xs px-2 py-0.5 rounded-full font-semibold
            {{ $webinar->event_date->isPast() ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-700' }}">
            {{ $webinar->event_date->isPast() ? 'Selesai' : 'Akan Datang' }}
          </span>
          <span class="text-xs text-gray-400">{{ $webinar->platform }}</span>
        </div>
        <h3 class="font-semibold text-gray-800 text-base mb-1">{{ $webinar->title }}</h3>
        @if($webinar->description)
          <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ $webinar->description }}</p>
        @endif
        <div class="flex items-center gap-4 text-xs text-gray-500">
          <span><i class="fas fa-calendar-alt mr-1 text-green-600"></i>{{ $webinar->event_date->format('d M Y, H:i') }} WIB</span>
          @if($webinar->zoom_link)
            <a href="{{ $webinar->zoom_link }}" target="_blank"
               class="text-blue-600 hover:text-blue-800">
              <i class="fas fa-video mr-1"></i>Buka Link Meeting
            </a>
          @endif
        </div>
      </div>

      <div class="flex flex-col items-end gap-2 flex-shrink-0">
        {{-- Status kehadiran --}}
        @if($att)
          @if($att->isApproved())
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
              ✓ Sertifikat Tersedia
            </span>
          @elseif($att->isPending())
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
              ⏳ Menunggu Review
            </span>
          @else
            <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
              ✗ Ditolak
            </span>
          @endif
        @else
          <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs">
            Belum submit
          </span>
        @endif

        <a href="{{ route('pemagang.webinar.show', $webinar) }}"
           class="text-sm font-medium text-white px-4 py-2 rounded-lg"
           style="background-color:#1a5c38;">
          Detail <i class="fas fa-arrow-right text-xs ml-1"></i>
        </a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

@endsection
