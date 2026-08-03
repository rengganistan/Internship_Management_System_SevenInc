<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Magang di Seveninc — Listmagang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: { extend: {} }
    }
  </script>
  <style>
    * { font-family: 'Inter', sans-serif; }
    html { scroll-behavior: smooth; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
    .float { animation: float 5s ease-in-out infinite; }
    .card { transition: transform .2s, box-shadow .2s; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(26,92,56,.12); }
    input:focus { outline: none; }
    .nav { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
  </style>
</head>
<body style="background:#f8faf9; color:#1a1a1a; overflow-x:hidden; color-scheme:light;">

{{-- NAVBAR --}}
<nav class="nav" style="position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);border-bottom:1px solid #e5e7eb;height:60px;">

{{-- NOTIF SUKSES pendaftaran --}}
@if(session('success'))
<div style="position:fixed;top:70px;left:50%;transform:translateX(-50%);z-index:200;background:#fff;border:1.5px solid #bbf7d0;border-radius:14px;padding:16px 24px;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:520px;width:90%;display:flex;align-items:flex-start;gap:14px;">
  <div style="width:36px;height:36px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <svg style="width:18px;height:18px;color:#16a34a;" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <div style="flex:1;">
    <p style="font-weight:700;color:#15803d;font-size:14px;margin-bottom:4px;">Pendaftaran Berhasil! 🎉</p>
    <p style="color:#166534;font-size:13px;line-height:1.5;">{{ session('success') }}</p>
    <a href="#login" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#1a5c38;color:#fff;font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;">
      <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Login Sekarang
    </a>
  </div>
  <button onclick="this.parentNode.remove()" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;padding:0;line-height:1;">×</button>
</div>
@endif
  <div style="max-width:1100px;margin:0 auto;padding:0 20px;height:100%;display:flex;align-items:center;justify-content:space-between;">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:32px;height:32px;border-radius:8px;background:#1a5c38;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:14px;">S</div>
      <span style="font-weight:700;color:#1a1a1a;font-size:15px;">Listmagang</span>
      <span style="color:#9ca3af;font-size:12px;display:none;" class="sm:inline">· Seveninc</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <a href="#daftar" style="background:#1a5c38;color:#fff;font-size:13px;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;transition:.2s;"
         onmouseover="this.style.background='#145c30'" onmouseout="this.style.background='#1a5c38'">
        Daftar Magang
      </a>
      <a href="#login" style="color:#374151;font-size:13px;font-weight:500;padding:8px 16px;border-radius:8px;border:1px solid #d1d5db;text-decoration:none;transition:.2s;"
         onmouseover="this.style.borderColor='#1a5c38';this.style.color='#1a5c38'" onmouseout="this.style.borderColor='#d1d5db';this.style.color='#374151'">
        Login
      </a>
    </div>
  </div>
</nav>

{{-- HERO --}}
<section style="background:linear-gradient(135deg,#0a2e1c 0%,#1a5c38 55%,#0d3d25 100%);min-height:100vh;display:flex;align-items:center;padding-top:60px;">
  <div style="max-width:1100px;margin:0 auto;padding:80px 20px;display:grid;grid-template-columns:1fr;gap:48px;align-items:center;">
    <div style="text-align:center;">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:100px;padding:6px 16px;margin-bottom:24px;">
        <span style="width:8px;height:8px;border-radius:50%;background:#4ade80;animation:float 2s ease-in-out infinite;"></span>
        <span style="color:rgba(255,255,255,.8);font-size:13px;font-weight:500;">Pendaftaran Magang Dibuka</span>
      </div>
      <h1 style="color:#fff;font-size:clamp(32px,5vw,52px);font-weight:800;line-height:1.2;margin-bottom:16px;">
        Mulai Karir Impianmu<br>
        <span style="color:#86efac;">Bersama Seveninc</span>
      </h1>
      <p style="color:rgba(255,255,255,.7);font-size:17px;line-height:1.7;margin-bottom:32px;max-width:540px;margin-left:auto;margin-right:auto;">
        Program magang profesional di berbagai divisi. Dapatkan pengalaman nyata,
        mentoring langsung, dan dokumen resmi yang diakui industri.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="#daftar" style="display:inline-flex;align-items:center;gap:8px;background:#4ade80;color:#14532d;font-weight:700;font-size:14px;padding:14px 28px;border-radius:12px;text-decoration:none;box-shadow:0 4px 20px rgba(74,222,128,.3);">
          <i class="fas fa-rocket" style="font-size:12px;"></i> Daftar Sekarang
        </a>
        <a href="#ketentuan" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);font-weight:500;font-size:14px;padding:14px 24px;border-radius:12px;text-decoration:none;">
          <i class="fas fa-info-circle" style="font-size:12px;"></i> Pelajari Lebih
        </a>
      </div>
      {{-- Stats --}}
      <div style="display:flex;justify-content:center;gap:48px;margin-top:56px;flex-wrap:wrap;">
        @foreach([['10+','Divisi Magang'],['100+','Alumni Magang'],['100%','Dokumen Resmi']] as [$num,$label])
        <div style="text-align:center;">
          <div style="font-size:32px;font-weight:800;color:#fff;">{{ $num }}</div>
          <div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:2px;">{{ $label }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- KETENTUAN --}}
<section id="ketentuan" style="padding:80px 20px;background:#fff;">
  <div style="max-width:1100px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:48px;">
      <p style="color:#1a5c38;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">Ketentuan Program</p>
      <h2 style="font-size:32px;font-weight:800;color:#111;margin-bottom:8px;">Apa yang Perlu Kamu Tahu</h2>
      <p style="color:#6b7280;max-width:480px;margin:0 auto;line-height:1.6;">Baca ketentuan berikut sebelum mendaftar agar proses magang berjalan lancar.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
      @php
        $items = [
          ['fa-calendar-alt','#dcfce7','#15803d','Jadwal Magang','Senin–Sabtu. Shift Pagi (06.30–13.00), Siang (13.00–21.00), atau Middle (09.00–17.00 WIB).'],
          ['fa-clock','#dbeafe','#1d4ed8','Durasi','Minimal 1 bulan, maksimal 6 bulan. Disesuaikan dengan kebutuhan program studi.'],
          ['fa-wallet','#fef9c3','#92400e','Program Unpaid','Bersifat tidak berbayar (unpaid). Fokus pada pengalaman & pembelajaran langsung.'],
          ['fa-laptop','#f3e8ff','#7e22ce','Wajib Laptop','Peserta wajib membawa laptop sendiri beserta alat pendukung yang dibutuhkan.'],
          ['fa-file-alt','#fee2e2','#b91c1c','Dokumen Lengkap','CV, KTP/KTM, surat pengantar kampus dalam format PDF. Maks. 2MB per file.'],
          ['fa-award','#dcfce7','#15803d','Dokumen Resmi','Peserta yang selesai mendapat LOA, SKL, Sertifikat, Surat Penilaian & Membercard.'],
        ];
      @endphp
      @foreach($items as [$icon,$bg,$color,$title,$desc])
      <div class="card" style="background:#fff;border:1px solid #f3f4f6;border-radius:16px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="width:44px;height:44px;border-radius:10px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <i class="fas {{ $icon }}" style="color:{{ $color }};font-size:16px;"></i>
        </div>
        <h3 style="font-weight:700;color:#111;margin-bottom:8px;font-size:15px;">{{ $title }}</h3>
        <p style="font-size:13px;color:#6b7280;line-height:1.65;">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- DIVISI --}}
<section style="padding:72px 20px;background:#f0f7f3;">
  <div style="max-width:1100px;margin:0 auto;text-align:center;">
    <p style="color:#1a5c38;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">Divisi Tersedia</p>
    <h2 style="font-size:32px;font-weight:800;color:#111;margin-bottom:32px;">Pilih Bidangmu</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">
      @foreach(['Project Manager','Administration','Human Resources','UI/UX','Programmer','Photographer','Videographer','Graphic Designer','Social Media','Content Writer','Content Planner','Sales & Marketing','Public Relations','Digital Marketing','TikTok Creator','Welding','Customer Service'] as $div)
      <span style="background:#fff;border:1px solid #d1fae5;color:#065f46;font-size:13px;font-weight:500;padding:8px 18px;border-radius:100px;box-shadow:0 1px 3px rgba(0,0,0,.05);">{{ $div }}</span>
      @endforeach
    </div>
  </div>
</section>

{{-- FORM --}}
<section id="daftar" style="padding:80px 20px;background:#fff;">
  <div style="max-width:880px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:48px;">
      <p style="color:#1a5c38;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">Mulai Sekarang</p>
      <h2 style="font-size:32px;font-weight:800;color:#111;margin-bottom:8px;">Daftar atau Masuk</h2>
      <p style="color:#6b7280;">Buat akun baru untuk daftar magang, atau login jika sudah punya akun.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

      {{-- REGISTER --}}
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
          <div style="width:40px;height:40px;border-radius:10px;background:#1a5c38;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-user-plus" style="color:#fff;font-size:14px;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:#111;font-size:15px;">Daftar Baru</div>
            <div style="color:#9ca3af;font-size:12px;">Belum punya akun? Daftar di sini</div>
          </div>
        </div>

        @if(session('success'))
          <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
            {{ session('success') }}
          </div>
        @endif
        @if($errors->any())
          <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
            @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
          </div>
        @endif

        <form action="{{ route('user.register.submit') }}" method="POST">
          @csrf
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
              Nama Lengkap <span style="color:#ef4444;">*</span>
            </label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="Sesuai ijazah"
                   style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;color:#111;background:#fff;box-sizing:border-box;transition:.2s;"
                   onfocus="this.style.borderColor='#1a5c38'" onblur="this.style.borderColor='#e5e7eb'">
            <p style="font-size:11px;color:#d97706;margin-top:4px;">⚠️ Nama ini dipakai untuk sertifikat, tidak bisa diubah.</p>
          </div>
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Email <span style="color:#ef4444;">*</span></label>
            <input type="email" name="email" required value="{{ old('email') }}" placeholder="email@contoh.com"
                   style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;color:#111;background:#fff;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#1a5c38'" onblur="this.style.borderColor='#e5e7eb'">
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Password <span style="color:#ef4444;">*</span></label>
              <input type="password" name="password" required placeholder="Min. 8 karakter"
                     style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;box-sizing:border-box;"
                     onfocus="this.style.borderColor='#1a5c38'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            <div>
              <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Konfirmasi <span style="color:#ef4444;">*</span></label>
              <input type="password" name="password_confirmation" required placeholder="Ulangi"
                     style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;box-sizing:border-box;"
                     onfocus="this.style.borderColor='#1a5c38'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
          </div>
          <button type="submit"
                  style="width:100%;padding:13px;background:#1a5c38;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;"
                  onmouseover="this.style.background='#145c30'" onmouseout="this.style.background='#1a5c38'">
            <i class="fas fa-user-plus" style="margin-right:8px;font-size:12px;"></i> Buat Akun & Daftar Magang
          </button>
        </form>
      </div>

      {{-- LOGIN --}}
      <div id="login" style="background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
          <div style="width:40px;height:40px;border-radius:10px;background:#111827;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-sign-in-alt" style="color:#fff;font-size:14px;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:#111;font-size:15px;">Masuk</div>
            <div style="color:#9ca3af;font-size:12px;">Sudah punya akun? Login di sini</div>
          </div>
        </div>

        @if(session('error'))
          <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
            {{ session('error') }}
          </div>
        @endif

        <form action="{{ route('user.login.submit') }}" method="POST">
          @csrf
          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}" placeholder="email@contoh.com"
                   style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;color:#111;background:#fff;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#374151'" onblur="this.style.borderColor='#e5e7eb'">
          </div>
          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Password</label>
            <input type="password" name="password" required placeholder="Password"
                   style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#374151'" onblur="this.style.borderColor='#e5e7eb'">
          </div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
            <input type="checkbox" name="remember" id="remember" style="width:14px;height:14px;accent-color:#1a5c38;">
            <label for="remember" style="font-size:13px;color:#6b7280;cursor:pointer;">Ingat saya</label>
          </div>
          <button type="submit"
                  style="width:100%;padding:13px;background:#111827;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;"
                  onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
            <i class="fas fa-sign-in-alt" style="margin-right:8px;font-size:12px;"></i> Masuk ke Dashboard
          </button>
        </form>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f3f4f6;text-align:center;">
          <p style="font-size:12px;color:#9ca3af;">Belum punya akun?
            <a href="#daftar" style="color:#1a5c38;font-weight:600;text-decoration:none;">Daftar sekarang</a>
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- FOOTER --}}
<footer style="background:#fff;border-top:1px solid #f3f4f6;padding:28px 20px;">
  <div style="max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:8px;">
      <div style="width:24px;height:24px;border-radius:6px;background:#1a5c38;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:800;">S</div>
      <span style="font-size:13px;font-weight:600;color:#374151;">Listmagang · Seveninc</span>
    </div>
    <p style="font-size:12px;color:#9ca3af;">© {{ date('Y') }} Seveninc. Program Magang Profesional Yogyakarta.</p>
    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280;">
      <i class="fas fa-map-marker-alt" style="color:#1a5c38;"></i> Yogyakarta, Indonesia
    </div>
  </div>
</footer>

{{-- Responsive 1 kolom di mobile --}}
<style>
  @media (max-width: 640px) {
    #daftar > div > div:last-child { grid-template-columns: 1fr !important; }
  }
</style>

</body>
</html>
