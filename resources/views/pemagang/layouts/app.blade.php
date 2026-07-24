<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — Listmagang</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

  <style>
    body { font-family: 'Inter', sans-serif; }

    /* Sidebar active state */
    .sidebar-link.active {
      background-color: rgba(255,255,255,0.15);
      font-weight: 600;
    }
    .sidebar-link:hover {
      background-color: rgba(255,255,255,0.10);
    }
  </style>
</head>

<body class="bg-gray-50 overflow-x-hidden">

  <div class="flex min-h-screen">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-56 flex-shrink-0 flex flex-col" style="background-color: #1a5c38;">

      {{-- Brand --}}
      <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-sm">
          {{ strtoupper(substr(config('app.name', 'L'), 0, 1)) }}
        </div>
        <div class="text-white">
          <div class="text-sm font-semibold leading-tight">Listmagang</div>
          <div class="text-xs text-white/60 leading-tight">Seveninc Internship</div>
        </div>
      </div>

      {{-- Navigation --}}
      <nav class="flex-1 px-3 py-4 space-y-1">
        <a href="{{ route('pemagang.dashboard') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/90 text-sm transition {{ request()->routeIs('pemagang.dashboard') ? 'active' : '' }}">
          <i class="fas fa-home w-4 text-center text-white/70"></i>
          Dashboard
        </a>

        <a href="{{ route('pemagang.registration.form') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/90 text-sm transition {{ request()->routeIs('pemagang.registration.*') ? 'active' : '' }}">
          <i class="fas fa-file-alt w-4 text-center text-white/70"></i>
          Daftar Magang
        </a>

        <a href="{{ route('pemagang.documents') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/90 text-sm transition {{ request()->routeIs('pemagang.documents') ? 'active' : '' }}">
          <i class="fas fa-folder-open w-4 text-center text-white/70"></i>
          Dokumen Saya
        </a>

        <a href="{{ route('pemagang.settings') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/90 text-sm transition {{ request()->routeIs('pemagang.settings') ? 'active' : '' }}">
          <i class="fas fa-cog w-4 text-center text-white/70"></i>
          Pengaturan
        </a>
      </nav>

      {{-- Logout --}}
      <div class="px-3 py-4 border-t border-white/10">
        <form method="POST" action="{{ route('user.logout') }}">
          @csrf
          <button type="submit"
            class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-white/70 text-sm hover:text-white hover:bg-white/10 transition">
            <i class="fas fa-sign-out-alt w-4 text-center"></i>
            Logout
          </button>
        </form>
      </div>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div class="flex-1 flex flex-col min-w-0">

      {{-- ===== TOPBAR ===== --}}
      <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
        {{-- Breadcrumb --}}
        <div class="text-sm text-gray-500">
          <span class="font-medium text-gray-700">Listmagang</span>
          @hasSection('breadcrumb')
            <span class="mx-1">/</span>
            @yield('breadcrumb')
          @endif
        </div>

        {{-- User info --}}
        <div class="flex items-center gap-3">
          {{-- Notif bell (opsional) --}}
          <button class="relative text-gray-400 hover:text-gray-600">
            <i class="fas fa-bell text-base"></i>
          </button>

          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold overflow-hidden flex-shrink-0"
                 style="background-color: #1a5c38;">
              @php $authUser = auth()->user(); @endphp
              @if($authUser->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($authUser->profile_picture))
                <img src="{{ asset('storage/' . $authUser->profile_picture) }}" alt="foto" class="w-full h-full object-cover">
              @else
                {{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}
              @endif
            </div>
            <div class="text-right hidden sm:block">
              <div class="text-sm font-medium text-gray-700 leading-tight">{{ auth()->user()->name }}</div>
              <div class="text-xs text-gray-400 leading-tight">
                @php
                  $reg = auth()->user()->internshipRegistration;
                  $isAccepted = in_array($reg?->internship_status, ['accepted', 'active', 'completed']);
                @endphp
                @if($isAccepted && $reg?->internship_interest)
                  Pemagang · {{ $reg->internship_interest }}
                @else
                  Pemagang
                @endif
              </div>
            </div>
            {{-- Dropdown pengaturan --}}
            <div class="relative" x-data="{ open: false }">
              <button @click="open = !open" class="text-gray-400 hover:text-gray-600 ml-1">
                <i class="fas fa-chevron-down text-xs"></i>
              </button>
              <div x-show="open" @click.outside="open = false"
                   class="absolute right-0 mt-2 w-40 bg-white border border-gray-100 rounded-lg shadow-lg z-50 py-1"
                   style="top: 100%;">
                <a href="{{ route('pemagang.settings') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  <i class="fas fa-cog w-4 text-center text-gray-400"></i> Pengaturan
                </a>
                <hr class="my-1 border-gray-100">
                <form method="POST" action="{{ route('user.logout') }}">
                  @csrf
                  <button type="submit"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>

      {{-- ===== CONTENT ===== --}}
      <main class="flex-1 p-6 overflow-y-auto">

        {{-- Flash messages --}}
        @if(session('success'))
          <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
          </div>
        @endif

        @yield('content')
      </main>

    </div>
  </div>

  @stack('scripts')
</body>
</html>
