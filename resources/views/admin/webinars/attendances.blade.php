@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('admin.webinars.index') }}"
         class="w-9 h-9 flex items-center justify-center rounded-lg border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#2D8659] transition">
        <i class="fas fa-arrow-left text-sm"></i>
      </a>
      <div>
        <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-0.5">Review Kehadiran</p>
        <h1 class="text-xl font-extrabold text-[#1B3A34]">{{ $webinar->title }}</h1>
        <p class="text-sm text-[#4B5F5A]">{{ $webinar->event_date->format('d M Y, H:i') }} WIB</p>
      </div>
    </div>

    {{-- Approve All + Generate Sertifikat --}}
    @php
      $pendingCount  = $attendances->where('status', 'pending')->count();
      $approvedCount = $attendances->where('status', 'approved')->count();
    @endphp
    <div class="flex items-center gap-2">
      @if($pendingCount > 0)
      <form method="POST" action="{{ route('admin.webinars.attendances.approve_all', $webinar) }}"
            onsubmit="return confirm('Setujui semua {{ $pendingCount }} bukti kehadiran yang masih pending?')">
        @csrf
        <button type="submit"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-lg"
                style="background-color:#2D8659;">
          <i class="fas fa-check-double text-xs"></i>
          Approve Semua ({{ $pendingCount }})
        </button>
      </form>
      @endif

      @if($approvedCount > 0)
      <a href="{{ route('admin.certificate.webinar.create', ['webinar_id' => $webinar->id]) }}"
         class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-lg"
         style="background-color:#1a5c38;">
        <i class="fas fa-award text-xs"></i>
        Generate Sertifikat ({{ $approvedCount }})
      </a>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {!! session('success') !!}
    </div>
  @endif
  @if(session('error'))
    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
      {{ session('error') }}
    </div>
  @endif

  {{-- Stats --}}
  <div class="grid grid-cols-3 gap-4 mb-6">
    @php
      $total    = $attendances->total();
      $pending  = $attendances->getCollection()->where('status','pending')->count();
      $approved = $attendances->getCollection()->where('status','approved')->count();
      $rejected = $attendances->getCollection()->where('status','rejected')->count();
    @endphp
    <div class="bg-white rounded-xl border border-[#DCE7E1] p-4 text-center shadow-sm">
      <p class="text-2xl font-extrabold text-[#1B3A34]">{{ $attendances->total() }}</p>
      <p class="text-sm text-[#4B5F5A]">Total Submit</p>
    </div>
    <div class="bg-white rounded-xl border border-amber-200 p-4 text-center shadow-sm">
      <p class="text-2xl font-extrabold text-amber-600">{{ $pending }}</p>
      <p class="text-sm text-[#4B5F5A]">Menunggu Review</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
      <p class="text-2xl font-extrabold text-green-600">{{ $approved }}</p>
      <p class="text-sm text-[#4B5F5A]">Disetujui</p>
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-[#DCE7E1] overflow-hidden shadow-sm">
    <table class="w-full">
      <thead class="bg-[#1B3A34]">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white">Pemagang</th>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white">Bukti Kehadiran</th>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white">Catatan</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white">Status</th>
          <th class="px-5 py-3 text-right text-xs font-bold uppercase text-white">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#DCE7E1]">
        @forelse($attendances as $att)
        <tr class="hover:bg-[#F4F8F6] transition">
          <td class="px-5 py-4">
            <p class="font-semibold text-[#1B3A34] text-sm">{{ $att->user->name }}</p>
            <p class="text-xs text-[#9ca3af]">{{ $att->user->email }}</p>
            <p class="text-xs text-[#9ca3af]">Submit: {{ $att->created_at->format('d M Y, H:i') }}</p>
          </td>
          <td class="px-5 py-4">
            @if($att->proof_file)
              <a href="{{ asset('storage/' . $att->proof_file) }}" target="_blank"
                 class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 hover:underline">
                <i class="fas fa-image text-xs"></i>
                Lihat Bukti
              </a>
            @else
              <span class="text-xs text-gray-400">Tidak ada file</span>
            @endif
          </td>
          <td class="px-5 py-4 text-sm text-[#4B5F5A] max-w-xs">
            {{ $att->proof_note ?: '—' }}
            @if($att->rejection_reason)
              <p class="text-xs text-red-500 mt-1">
                <i class="fas fa-times-circle mr-1"></i>{{ $att->rejection_reason }}
              </p>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($att->status === 'pending')
              <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Pending</span>
            @elseif($att->status === 'approved')
              <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">✓ Approved</span>
              @if($att->certificate_id)
                <a href="{{ route('admin.certificate.pdf', $att->certificate_id) }}"
                   class="block text-xs text-[#2D8659] hover:underline mt-1">
                  <i class="fas fa-file-pdf text-xs mr-1"></i>Sertifikat
                </a>
              @endif
            @else
              <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">✗ Ditolak</span>
            @endif
          </td>
          <td class="px-5 py-4">
            @if($att->status === 'pending')
            <div class="flex items-center justify-end gap-2">
              {{-- Approve --}}
              <form method="POST"
                    action="{{ route('admin.webinars.attendances.approve', [$webinar, $att]) }}">
                @csrf
                <button type="submit"
                        class="text-xs font-semibold text-white px-3 py-1.5 rounded-lg"
                        style="background-color:#2D8659;"
                        onclick="return confirm('Setujui dan generate sertifikat untuk {{ $att->user->name }}?')">
                  Approve
                </button>
              </form>

              {{-- Reject --}}
              <button type="button"
                      onclick="openRejectModal({{ $att->id }}, '{{ route('admin.webinars.attendances.reject', [$webinar, $att]) }}')"
                      class="text-xs text-red-600 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                Tolak
              </button>
            </div>
            @else
              <span class="text-xs text-gray-400 text-right block">
                {{ $att->reviewer?->name ?? '—' }}<br>
                {{ $att->reviewed_at?->format('d M Y') }}
              </span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
            Belum ada pemagang yang mengupload bukti kehadiran.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-[#DCE7E1]">{{ $attendances->links() }}</div>
  </div>
</div>

{{-- Modal Reject --}}
<div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
  <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Tolak Bukti Kehadiran</h3>
    <form id="form-reject" method="POST">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          Alasan Penolakan <span class="text-red-500">*</span>
        </label>
        <textarea name="rejection_reason" required rows="3"
          placeholder="Contoh: Foto tidak jelas, bukan screenshot saat webinar berlangsung, dll."
          class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
      </div>
      <div class="flex gap-3 justify-end">
        <button type="button" onclick="closeRejectModal()"
          class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
          Batal
        </button>
        <button type="submit"
          class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">
          Tolak
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openRejectModal(id, action) {
  document.getElementById('form-reject').action = action;
  document.getElementById('modal-reject').classList.remove('hidden');
}
function closeRejectModal() {
  document.getElementById('modal-reject').classList.add('hidden');
}
</script>
@endsection
