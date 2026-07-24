@extends('pemagang.layouts.app')

@section('title', 'Pengaturan Akun')
@section('breadcrumb', 'Pengaturan')

@section('content')

<div class="max-w-2xl mx-auto">
  <h2 class="text-lg font-semibold text-gray-800 mb-1">Pengaturan Akun</h2>
  <p class="text-sm text-gray-500 mb-6">Kelola informasi profil dan keamanan akun Anda</p>

  <form action="{{ route('pemagang.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ===== FOTO PROFIL ===== --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
      <p class="text-sm font-semibold text-gray-700 mb-4">Foto Profil</p>

      <div class="flex items-center gap-5">
        {{-- Preview foto --}}
        <div class="w-20 h-20 rounded-full overflow-hidden flex-shrink-0 border-2 border-gray-200 bg-gray-100 flex items-center justify-center"
             style="width:80px;height:80px;min-width:80px;min-height:80px;"
             id="photo-preview-container">
          @if($user->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_picture))
            <img id="photo-preview"
                 src="{{ asset('storage/' . $user->profile_picture) }}"
                 alt="foto profil"
                 class="w-full h-full object-cover">
          @else
            <div id="photo-initials"
                 class="w-full h-full flex items-center justify-center text-white text-2xl font-bold"
                 style="background-color:#1a5c38;">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
          @endif
        </div>

        <div>
          <label for="profile_picture"
                 class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            <i class="fas fa-upload text-xs"></i> Unggah Foto
          </label>
          <input type="file" id="profile_picture" name="profile_picture"
                 accept="image/jpg,image/jpeg,image/png" class="hidden"
                 onchange="previewPhoto(this)">
          <p class="text-xs text-gray-400 mt-1.5">JPG atau PNG, maks. 2MB</p>
          @error('profile_picture')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </div>

    {{-- ===== DATA DIRI ===== --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
      <p class="text-sm font-semibold text-gray-700 mb-4">Data Diri</p>

      <div class="space-y-4">
        <div>
          <label class="block mb-1.5 text-sm font-medium text-gray-700">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input type="text" name="name" required
            value="{{ old('name', $user->name) }}"
            class="block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
          @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1.5 text-sm font-medium text-gray-700">
              Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" required
              value="{{ old('email', $user->email) }}"
              class="block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
            @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block mb-1.5 text-sm font-medium text-gray-700">No. HP</label>
            <input type="text" name="phone_number"
              value="{{ old('phone_number', $user->phone_number) }}"
              placeholder="08xxxxxxxxxx"
              class="block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
          </div>
        </div>
      </div>
    </div>

    {{-- ===== GANTI PASSWORD ===== --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-6">
      <p class="text-sm font-semibold text-gray-700 mb-1">Ganti Password</p>
      <p class="text-xs text-gray-400 mb-4">Kosongkan jika tidak ingin mengganti password</p>

      <div class="space-y-4">
        <div>
          <label class="block mb-1.5 text-sm font-medium text-gray-700">Password Baru</label>
          <input type="password" name="password"
            placeholder="Min. 8 karakter"
            class="block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
          @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block mb-1.5 text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
          <input type="password" name="password_confirmation"
            placeholder="Ulangi password baru"
            class="block w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit"
        class="px-6 py-2.5 text-sm font-semibold text-white rounded-lg"
        style="background-color:#1a5c38;">
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
  function previewPhoto(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const container = document.getElementById('photo-preview-container');
        container.innerHTML = `<img src="${e.target.result}"
          alt="preview"
          style="width:100%;height:100%;object-fit:cover;border-radius:9999px;">`;
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
@endpush

@endsection
