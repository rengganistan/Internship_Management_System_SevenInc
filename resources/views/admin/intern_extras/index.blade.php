@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-1">Dokumen & Sertifikat</p>
    <h1 class="text-2xl font-extrabold text-[#1B3A34]">Alumni Terpilih</h1>
    <p class="text-sm text-[#4B5F5A] mt-1">Admin memilih pemagang yang sudah selesai untuk mendapatkan akses surat rekomendasi, grup alumni, dan info kerja. Tidak semua pemagang otomatis mendapat akses.</p>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {!! session('success') !!}
    </div>
  @endif

  <div class="mb-4 flex justify-end">
    <a href="{{ route('admin.rekomendasi.editor') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1F5F3F]">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
        <path d="M14 2v6h6"></path>
        <path d="M9 13h6M9 17h6"></path>
      </svg>
      Template Surat Rekomendasi
    </a>
  </div>

  <div class="bg-white rounded-xl border border-[#DCE7E1] overflow-hidden shadow-sm">
    <table class="w-full">
      <thead class="bg-[#1B3A34]">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white tracking-wider">Pemagang</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Rekomendasi</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Grup Alumni</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Info Kerja</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Status</th>
          <th class="px-5 py-3 text-right text-xs font-bold uppercase text-white tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#DCE7E1]">
        @forelse($interns as $intern)
        @php $extra = \App\Models\InternExtra::where('internship_registration_id', $intern->id)->first(); @endphp
        @php $isSelected = $extra && ($extra->rekomendasi_path || $extra->alumni_group_url || $extra->job_info_url); @endphp
        <tr class="hover:bg-[#F4F8F6] transition">
          <td class="px-5 py-4">
            <p class="font-semibold text-[#1B3A34] text-sm">{{ $intern->fullname }}</p>
            <p class="text-xs text-[#4B5F5A]">{{ $intern->internship_interest }} · {{ $intern->institution_name }}</p>
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->rekomendasi_path)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Ada
              </span>
            @else
              <span class="text-xs text-gray-400">Belum</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->alumni_group_url)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Ada
              </span>
            @else
              <span class="text-xs text-gray-400">Belum</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->job_info_url)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Ada
              </span>
            @else
              <span class="text-xs text-gray-400">Belum</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($isSelected)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Terpilih</span>
            @else
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Belum ditetapkan</span>
            @endif
          </td>
          <td class="px-5 py-4 text-right">
            <a href="{{ route('admin.intern_extras.edit', $intern->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded-lg"
               style="background-color:#2D8659;">
              <i class="fas fa-edit text-xs"></i> Kelola Akses
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
            Belum ada pemagang yang menyelesaikan magang.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-5 py-4 border-t border-[#DCE7E1]">
      {{ $interns->links() }}
    </div>
  </div>
</div>
@endsection
