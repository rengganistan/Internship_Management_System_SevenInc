@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

  <div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.webinars.index') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg border border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#2D8659] transition">
      <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#2D8659] mb-0.5">Webinar</p>
      <h1 class="text-xl font-extrabold text-[#1B3A34]">Buat Webinar Baru</h1>
    </div>
  </div>

  @if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
      @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
  @endif

  <form action="{{ route('admin.webinars.store') }}" method="POST">
    @csrf
    @include('admin.webinars._form', ['webinar' => null])
    <div class="mt-5 flex gap-3">
      <button type="submit"
              class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl"
              style="background-color:#2D8659;">
        <i class="fas fa-rocket text-xs mr-2"></i> Buat & Publikasikan
      </button>
      <a href="{{ route('admin.webinars.index') }}"
         class="px-6 py-2.5 text-sm font-semibold text-[#4B5F5A] border border-[#DCE7E1] bg-white rounded-xl hover:bg-[#F4F8F6] transition">
        Batal
      </a>
    </div>
  </form>
</div>
@endsection
