@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-1">Dokumen & Sertifikat</p>
    <h1 class="text-2xl font-extrabold text-[#1B3A34]">Informasi Alumni</h1>
    <p class="text-sm text-[#4B5F5A] mt-1">Kelola surat rekomendasi, link grup alumni, dan info kerja untuk pemagang yang sudah selesai magang.</p>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {!! session('success') !!}
    </div>
  @endif

  {{-- Filter Brand --}}
  <div class="bg-white rounded-xl border border-[#DCE7E1] p-4 shadow-sm mb-4">
    <form method="GET" action="{{ route('admin.intern_extras.index') }}" class="flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-[#4B5F5A] mb-1.5 uppercase tracking-wide">Filter Brand</label>
        <select name="brand" onchange="this.form.submit()"
          class="w-full px-3 py-2 text-sm border border-[#DCE7E1] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2D8659] bg-white">
          <option value="">— Semua Brand —</option>
          @foreach($brands as $brand)
            <option value="{{ $brand }}" {{ $selectedBrand === $brand ? 'selected' : '' }}>{{ $brand }}</option>
          @endforeach
        </select>
      </div>
      @if($selectedBrand)
        <a href="{{ route('admin.intern_extras.index') }}"
           class="px-4 py-2 text-sm font-semibold text-[#4B5F5A] border border-[#DCE7E1] rounded-lg bg-white hover:bg-[#F4F8F6] transition">
          Reset
        </a>
      @endif
    </form>
  </div>

  {{-- Tombol Kelola Semua (hanya jika ada brand terpilih) --}}
  @if($selectedBrand && $interns->total() > 0)
    @php
      // Ambil intern pertama dari brand ini untuk redirect ke edit dengan mode all_brand
      $firstIntern = $interns->first();
    @endphp
    <div class="mb-4 flex items-center gap-3 p-4 bg-[#EBF5EF] border border-[#BDE3CC] rounded-xl">
      <div class="flex-1">
        <p class="text-sm font-semibold text-[#1B3A34]">Brand: <span class="text-[#2D8659]">{{ $selectedBrand }}</span></p>
        <p class="text-xs text-[#4B5F5A] mt-0.5">{{ $interns->total() }} pemagang ditemukan. Anda dapat mengelola semua sekaligus.</p>
      </div>
      <a href="{{ route('admin.intern_extras.edit', $firstIntern->id) }}?mode=all_brand"
         class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg"
         style="background-color:#2D8659;">
        <i class="fas fa-users text-xs"></i>
        Kelola Semua ({{ $interns->total() }})
      </a>
    </div>
  @endif

  <div class="bg-white rounded-xl border border-[#DCE7E1] overflow-hidden shadow-sm">
    <table class="w-full">
      <thead class="bg-[#1B3A34]">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-bold uppercase text-white tracking-wider">Pemagang</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Rekomendasi</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Grup Alumni</th>
          <th class="px-5 py-3 text-center text-xs font-bold uppercase text-white tracking-wider">Info Kerja</th>
          <th class="px-5 py-3 text-right text-xs font-bold uppercase text-white tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#DCE7E1]">
        @forelse($interns as $intern)
        @php $extra = \App\Models\InternExtra::where('internship_registration_id', $intern->id)->first(); @endphp
        <tr class="hover:bg-[#F4F8F6] transition">
          <td class="px-5 py-4">
            <p class="font-semibold text-[#1B3A34] text-sm">{{ $intern->fullname }}</p>
            <p class="text-xs text-[#4B5F5A]">{{ $intern->internship_interest }} · {{ $intern->institution_name }}</p>
            @if($intern->brand)
              <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded-full bg-[#EBF5EF] text-[#2D8659] font-semibold">{{ $intern->brand }}</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->rekomendasi_path)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Terkirim
              </span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->alumni_group_url)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Ada
              </span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </td>
          <td class="px-5 py-4 text-center">
            @if($extra?->job_info_url)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                <i class="fas fa-check text-xs"></i> Ada
              </span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </td>
          <td class="px-5 py-4 text-right">
            <a href="{{ route('admin.intern_extras.edit', $intern->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded-lg"
               style="background-color:#2D8659;">
              <i class="fas fa-edit text-xs"></i> Kelola
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
            @if($selectedBrand)
              Tidak ada pemagang dengan brand <strong>{{ $selectedBrand }}</strong> yang menyelesaikan magang.
            @else
              Belum ada pemagang yang menyelesaikan magang.
            @endif
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
