@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6 flex items-center justify-between">
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-1">Dokumen & Sertifikat</p>
      <h1 class="text-2xl font-extrabold text-[#1B3A34]">Manajemen Webinar</h1>
      <p class="text-sm text-[#4B5F5A] mt-1">Kelola webinar dan sertifikat kehadiran untuk pemagang.</p>
    </div>
    <a href="{{ route('admin.webinars.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-lg"
       style="background-color:#2D8659;">
      <i class="fas fa-plus text-xs"></i> Buat Webinar
    </a>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {!! session('success') !!}
    </div>
  @endif

  <div class="bg-white rounded-xl border border-[#DCE7E1] overflow-hidden shadow-sm">
    <table class="w-full">
      <thead class="bg-[#1B3A34]">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white">Webinar</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white">Tanggal</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white">Pending</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white">Approved</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white">Status</th>
          <th class="px-5 py-3 text-right text-xs font-bold uppercase text-white">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#DCE7E1]">
        @forelse($webinars as $webinar)
        <tr class="hover:bg-[#F4F8F6] transition">
          <td class="px-5 py-4">
            <p class="font-semibold text-[#1B3A34] text-sm">{{ $webinar->title }}</p>
            @if($webinar->zoom_link)
              <a href="{{ $webinar->zoom_link }}" target="_blank"
                 class="text-xs text-blue-600 hover:underline">
                <i class="fas fa-video text-xs mr-1"></i>{{ $webinar->platform }}
              </a>
            @endif
          </td>
          <td class="px-5 py-4 text-center text-sm text-[#4B5F5A]">
            {{ $webinar->event_date->format('d M Y') }}<br>
            <span class="text-xs text-[#9ca3af]">{{ $webinar->event_date->format('H:i') }} WIB</span>
          </td>
          <td class="px-5 py-4 text-center">
            @if($webinar->pending_attendances_count > 0)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                {{ $webinar->pending_attendances_count }} pending
              </span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            <span class="text-sm font-semibold text-green-700">{{ $webinar->approved_attendances_count }}</span>
            <span class="text-xs text-gray-400"> / {{ $webinar->attendances_count }}</span>
          </td>
          <td class="px-5 py-4 text-center">
            @if($webinar->is_active)
              <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Aktif</span>
            @else
              <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Draft</span>
            @endif
          </td>
          <td class="px-5 py-4">
            <div class="flex items-center justify-end gap-2">

              <a href="{{ route('admin.webinars.attendances', $webinar) }}"
                 class="text-xs font-semibold text-white px-3 py-1.5 rounded-lg"
                 style="background-color:#2D8659;">
                Review
              </a>
              <a href="{{ route('admin.webinars.edit', $webinar) }}"
                 class="text-xs font-medium text-[#4B5F5A] border border-[#DCE7E1] px-3 py-1.5 rounded-lg hover:border-[#2D8659] hover:text-[#2D8659] transition">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.webinars.destroy', $webinar) }}"
                    onsubmit="return confirm('Hapus webinar ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-600 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
            Belum ada webinar. <a href="{{ route('admin.webinars.create') }}" class="text-[#2D8659] font-semibold">Buat sekarang</a>.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-[#DCE7E1]">{{ $webinars->links() }}</div>
  </div>
</div>
@endsection
