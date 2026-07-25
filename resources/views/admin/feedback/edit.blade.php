@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.feedback.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-[9px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Feedback</p>
            <h1 class="text-xl font-extrabold tracking-tight text-[#1B3A34]">Edit Feedback</h1>
        </div>
    </div>

    <div class="max-w-xl">
        <div class="rounded-[12px] border border-[#DCE7E1] bg-white p-6 shadow-sm">

            {{-- Info pengirim --}}
            @if($feedback->name)
            <div class="mb-5 flex items-center gap-3 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                @php $ini = collect(explode(' ', $feedback->name))->take(2)->map(fn($w)=>strtoupper($w[0]??''))->implode(''); @endphp
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-[12px] font-bold text-[#1F5F3F]">
                    {{ $ini }}
                </div>
                <div>
                    <p class="font-semibold text-[#1B3A34]">{{ $feedback->name }}</p>
                    <p class="text-[11.5px] text-[#4B5F5A]">{{ $feedback->created_at?->format('d M Y, H:i') }}</p>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 rounded-[9px] border border-red-200 bg-red-50 px-4 py-3">
                <ul class="space-y-1 text-[13px] text-[#D32F2F]">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.feedback.update', $feedback->id) }}" method="POST">
                @csrf @method('POST')

                <div class="mb-5">
                    <label class="mb-1.5 block text-[12.5px] font-semibold text-[#1B3A34]">
                        Isi Feedback <span class="text-[#D32F2F]">*</span>
                    </label>
                    <textarea name="feedback" rows="6" required
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2.5 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition resize-none">{{ old('feedback', $feedback->feedback) }}</textarea>
                </div>

                <div class="flex items-center gap-3 border-t border-[#DCE7E1] pt-5">
                    <button type="submit"
                        class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.feedback.index') }}"
                        class="rounded-[9px] border border-[#DCE7E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#4B5F5A] transition hover:bg-[#F4F8F6]">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
