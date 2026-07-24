@extends('pemagang.layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

@php
  $name     = auth()->user()->name ?? 'Pemagang';
  $firstName = explode(' ', $name)[0];
  $reg      = $registration;
  $divisi   = $reg?->internship_interest ?? null;
  $updatedAt = $reg?->updated_at ?? $reg?->created_at;
@endphp

{{-- ===== STATUS CARD ===== --}}
<div class="rounded-xl p-6 mb-6 text-white relative overflow-hidden"
     style="background-color: #1a5c38;">

  {{-- Decorative circles --}}
  <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full opacity-10 bg-white"></div>
  <div class="absolute -right-4 top-12 w-24 h-24 rounded-full opacity-10 bg-white"></div>

  <div class="relative flex items-start justify-between">
    <div>
      <p class="text-white/70 text-xs font-medium uppercase tracking-wider mb-1">Status Pendaftaran</p>
      <h1 class="text-2xl font-bold mb-1">
        Selamat datang, {{ $firstName }} 👋
      </h1>

      @if($reg)
        <p class="text-white/80 text-sm">
          @if($reg->is_draft)
            Form pendaftaran Anda masih dalam <strong>status draft</strong>. Lengkapi dan kirim segera.
          @elseif($reg->internship_status === 'waiting' || $reg->internship_status === 'pending')
            Pendaftaran magang Anda untuk divisi <strong>{{ $divisi }}</strong> sedang dalam
            tahap peninjauan oleh tim admin.
          @elseif($reg->internship_status === 'accepted')
            Selamat! Pendaftaran Anda untuk divisi <strong>{{ $divisi }}</strong> telah <strong>diterima</strong>.
          @elseif($reg->internship_status === 'active')
            Anda sedang aktif magang di divisi <strong>{{ $divisi }}</strong>. Semangat!
          @elseif($reg->internship_status === 'completed')
            Magang Anda telah <strong>selesai</strong>. Dokumen kelulusan sudah tersedia.
          @elseif($reg->internship_status === 'rejected')
            Maaf, pendaftaran Anda <strong>tidak diterima</strong> saat ini.
          @else
            Status pendaftaran: <strong>{{ ucfirst($reg->internship_status) }}</strong>
          @endif
        </p>
        @if($updatedAt)
          <p class="text-white/50 text-xs mt-1">Diperbarui terakhir: {{ $updatedAt->diffForHumans() }}</p>
        @endif
      @else
        <p class="text-white/80 text-sm">Anda belum mendaftar magang. Klik "Daftar Magang" untuk memulai.</p>
      @endif
    </div>

    {{-- Status badge --}}
    <div class="flex-shrink-0 ml-4">
      @if(!$reg || $reg->is_draft)
        <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
          ✏️ Draft
        </span>
      @elseif($statusColor === 'yellow')
        <span class="bg-yellow-400/90 text-yellow-900 text-xs font-semibold px-3 py-1.5 rounded-full">
          🔍 {{ $statusLabel }}
        </span>
      @elseif($statusColor === 'green')
        <span class="bg-green-400/90 text-green-900 text-xs font-semibold px-3 py-1.5 rounded-full">
          ✅ {{ $statusLabel }}
        </span>
      @elseif($statusColor === 'red')
        <span class="bg-red-400/90 text-red-900 text-xs font-semibold px-3 py-1.5 rounded-full">
          ❌ {{ $statusLabel }}
        </span>
      @else
        <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
          {{ $statusLabel }}
        </span>
      @endif
    </div>
  </div>
</div>

{{-- ===== PROGRESS STEPPER ===== --}}
@if($reg && !$reg->is_draft)
<div class="bg-white rounded-xl p-6 mb-6 border border-gray-100">
  <p class="text-sm font-medium text-gray-700 mb-4">Progres Pendaftaran</p>

  @php
    $steps = [
      ['label' => 'Submitted',         'sub' => $reg->created_at?->format('d M Y') ?? ''],
      ['label' => 'Under Review',      'sub' => 'Sedang diproses'],
      ['label' => 'Diterima / Ditolak','sub' => $reg->internship_status === 'rejected' ? 'Ditolak' : 'Menunggu'],
    ];

    // Map status ke step number
    $progressStep = match($registration?->internship_status) {
      'waiting', 'pending'                    => 2,
      'accepted', 'rejected', 'active',
      'completed', 'exited'                   => 3,
      default                                 => 1,
    };

    $isRejected = $reg->internship_status === 'rejected';
  @endphp

  <div class="flex items-start">
    @foreach($steps as $i => $step)
      {{-- Circle --}}
      <div class="flex flex-col items-center min-w-0" style="flex: 1">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0
          {{ $progressStep > $i + 1
              ? 'text-white'
              : ($progressStep === $i + 1
                  ? ($isRejected && $i === 2 ? 'bg-red-500 text-white' : 'text-white')
                  : 'bg-gray-200 text-gray-500') }}"
          style="{{ $progressStep > $i + 1
              ? 'background-color:#1a5c38'
              : ($progressStep === $i + 1 && !($isRejected && $i === 2)
                  ? 'background-color:#f59e0b'
                  : '') }}">
          @if($progressStep > $i + 1)
            ✓
          @elseif($isRejected && $i === 2 && $progressStep === 3)
            ✕
          @else
            {{ $i + 1 }}
          @endif
        </div>
        <div class="text-center mt-2 px-1">
          <div class="text-xs font-medium {{ $progressStep === $i + 1 ? 'text-gray-800' : 'text-gray-400' }}">
            {{ $step['label'] }}
          </div>
          <div class="text-xs text-gray-400">{{ $step['sub'] }}</div>
        </div>
      </div>

      {{-- Connector --}}
      @if(!$loop->last)
        <div class="h-0.5 mt-4 flex-1
          {{ $progressStep > $i + 1 ? '' : 'bg-gray-200' }}"
          style="{{ $progressStep > $i + 1 ? 'background-color:#1a5c38' : '' }}">
        </div>
      @endif
    @endforeach
  </div>
</div>
@endif

{{-- ===== GRID BAWAH ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  {{-- Akses Cepat --}}
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <p class="text-sm font-semibold text-gray-700 mb-1">Akses Cepat</p>
    <p class="text-xs text-gray-400 mb-4">Hal yang mungkin ingin Anda lakukan</p>

    <div class="space-y-2">
      {{-- Lihat / Isi Form --}}
      <a href="{{ route('pemagang.registration.form') }}"
         class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-100 hover:border-green-200 hover:bg-green-50 transition group">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-green-50 group-hover:bg-green-100 flex items-center justify-center text-green-700">
            <i class="fas fa-file-alt text-sm"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700">Lihat Form Pendaftaran</div>
            <div class="text-xs text-gray-400">Cek kembali data yang Anda kirim</div>
          </div>
        </div>
        <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-green-600"></i>
      </a>

      {{-- Syarat & Ketentuan --}}
      <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-100 hover:border-green-200 hover:bg-green-50 transition group cursor-pointer"
           onclick="document.getElementById('modal-syarat').classList.remove('hidden')">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-green-50 group-hover:bg-green-100 flex items-center justify-center text-green-700">
            <i class="fas fa-file-contract text-sm"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700">Syarat & Ketentuan</div>
            <div class="text-xs text-gray-400">Ketentuan program magang Seveninc</div>
          </div>
        </div>
        <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-green-600"></i>
      </div>

      {{-- Unduh Dokumen --}}
      <a href="{{ route('pemagang.documents') }}"
         class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-100 hover:border-green-200 hover:bg-green-50 transition group">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-green-50 group-hover:bg-green-100 flex items-center justify-center text-green-700">
            <i class="fas fa-download text-sm"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700">Unduh Dokumen</div>
            <div class="text-xs text-gray-400">Surat & berkas penting lainnya</div>
          </div>
        </div>
        <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-green-600"></i>
      </a>
    </div>
  </div>

  {{-- Informasi Penting --}}
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <p class="text-sm font-semibold text-gray-700 mb-1">Informasi Penting</p>
    <p class="text-xs text-gray-400 mb-4">Ringkasan periode & ketentuan magang</p>

    <div class="space-y-3">
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-700 flex-shrink-0 mt-0.5">
          <i class="fas fa-calendar-alt text-sm"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-700">Periode Magang</div>
          @if($reg?->start_date && $reg?->end_date)
            <div class="text-xs text-gray-500">
              {{ \Carbon\Carbon::parse($reg->start_date)->format('d M Y') }}
              – {{ \Carbon\Carbon::parse($reg->end_date)->format('d M Y') }}
            </div>
          @else
            <div class="text-xs text-gray-500">Belum ditentukan</div>
          @endif
        </div>
      </div>

      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600 flex-shrink-0 mt-0.5">
          <i class="fas fa-clock text-sm"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-700">Estimasi Review</div>
          <div class="text-xs text-gray-500">3–5 hari kerja sejak pendaftaran dikirim</div>
        </div>
      </div>

      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0 mt-0.5">
          <i class="fas fa-lightbulb text-sm"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-700">Tips</div>
          <div class="text-xs text-gray-500">Pastikan CV dan surat pengantar dalam format PDF, maks. 2MB</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ===== FEEDBACK SECTION ===== --}}
<div class="bg-white rounded-xl border border-gray-100 p-5 mt-6">
  <p class="text-sm font-semibold text-gray-700 mb-1">Feedback</p>
  <p class="text-xs text-gray-400 mb-4">Sampaikan saran, masukan, atau keluhan Anda kepada admin</p>

  <form action="{{ route('user.feedback.submit') }}" method="POST">
    @csrf
    <textarea name="feedback" rows="3"
      placeholder="Tulis feedback Anda di sini..."
      class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent resize-none"
    ></textarea>
    <div class="flex justify-end mt-2">
      <button type="submit"
        class="px-4 py-2 text-sm font-medium text-white rounded-lg"
        style="background-color:#1a5c38;">
        Kirim Feedback
      </button>
    </div>
  </form>
</div>

{{-- ===== MODAL SYARAT & KETENTUAN ===== --}}
<div id="modal-syarat" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
  <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-semibold text-gray-800">Syarat & Ketentuan Magang</h3>
      <button onclick="document.getElementById('modal-syarat').classList.add('hidden')"
        class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
      <li>Program magang bersifat <strong>unpaid / tidak bergaji</strong></li>
      <li>Hari magang: <strong>Senin – Sabtu</strong></li>
      <li>Durasi minimal 1 bulan, maksimal 6 bulan</li>
      <li>Wajib hadir minimal 90% dari total hari kerja</li>
      <li>Membawa laptop dan perlengkapan kerja sendiri</li>
      <li>Mematuhi peraturan dan budaya kerja Seveninc</li>
      <li>Sertifikat diberikan setelah masa magang selesai</li>
    </ul>
    <div class="mt-5 flex justify-end">
      <button onclick="document.getElementById('modal-syarat').classList.add('hidden')"
        class="px-4 py-2 text-sm font-medium text-white rounded-lg"
        style="background-color:#1a5c38;">
        Mengerti
      </button>
    </div>
  </div>
</div>

@endsection
