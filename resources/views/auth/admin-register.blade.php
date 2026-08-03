<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Auto dark sesuai sistem (Tailwind darkMode: "class") --}}
  <script>
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark');
    }
  </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-primary-500
             bg-gradient-to-br from-primary-400 via-primary-500 to-primary-700
             dark:from-primary-900 dark:via-primary-950 dark:to-black">

  <div class="w-full max-w-md px-4">
    <div class="rounded-2xl shadow-xl ring-1 ring-black/5 dark:ring-white/10
                bg-white/90 dark:bg-gray-900/85 backdrop-blur p-6 sm:p-8">

      <h2 class="mb-6 text-2xl font-bold text-center text-gray-900 dark:text-gray-100">
        Register
      </h2>

      @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg border
                    bg-red-50 text-red-700 border-red-200
                    dark:bg-red-950/40 dark:text-red-200 dark:border-red-900/50">
          {{ session('error') }}
        </div>
      @endif

      <form action="{{ route('user.register.submit') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-800 dark:text-gray-200">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input type="text" id="name" name="name" required autocomplete="name"
                 value="{{ old('name') }}"
                 placeholder="Contoh: Budi Santoso"
                 class="mt-1 w-full px-4 py-2 rounded-lg border
                        bg-white text-gray-900 placeholder-gray-400
                        border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                        dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:border-gray-700 dark:focus:ring-primary-400 dark:focus:border-primary-400"/>
          <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
            ⚠️ Isi nama lengkap sesuai ijazah — nama ini akan digunakan untuk <strong>sertifikat</strong> dan tidak bisa diubah setelah mendaftar.
          </p>
          @error('name')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Email</label>
          <input type="email" id="email" name="email" required autocomplete="username"
                 placeholder="you@example.com"
                 class="mt-1 w-full px-4 py-2 rounded-lg border
                        bg-white text-gray-900 placeholder-gray-400
                        border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                        dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:border-gray-700 dark:focus:ring-primary-400 dark:focus:border-primary-400"/>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Password</label>
          <input type="password" id="password" name="password" required autocomplete="new-password"
                 placeholder="••••••••"
                 class="mt-1 w-full px-4 py-2 rounded-lg border
                        bg-white text-gray-900 placeholder-gray-400
                        border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                        dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:border-gray-700 dark:focus:ring-primary-400 dark:focus:border-primary-400"/>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Confirm Password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                 placeholder="••••••••"
                 class="mt-1 w-full px-4 py-2 rounded-lg border
                        bg-white text-gray-900 placeholder-gray-400
                        border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                        dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:border-gray-700 dark:focus:ring-primary-400 dark:focus:border-primary-400"/>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full inline-flex justify-center items-center gap-2
                       bg-primary-600 hover:bg-primary-700 text-white font-medium
                       py-2.5 px-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                       dark:focus:ring-offset-gray-900">
          Register
        </button>
      </form>

      <p class="mt-6 text-center text-xs text-primary-900/80 dark:text-primary-200/70">
        Secured area — authorized users only.
      </p>

      <!-- Button to Cancel Registration and go back to Login -->
      <div class="mt-4 text-center">
        <a href="{{ route('home') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Tidak jadi daftar? Kembali ke beranda</a>
      </div>
    </div>
  </div>
</body>
</html>
