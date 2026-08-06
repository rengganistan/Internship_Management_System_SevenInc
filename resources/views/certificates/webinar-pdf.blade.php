<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $pdfTitle ?? 'Sertifikat Webinar' }}</title>
  @php
    use Carbon\Carbon;

    // Helper: relative path → data URI (embed gambar langsung ke HTML)
    $dataUri = function ($relPath) {
        if (empty($relPath)) return '';
        $abs = storage_path('app/public/' . $relPath);
        if (!file_exists($abs)) return '';
        $finfo  = finfo_open(FILEINFO_MIME_TYPE);
        $mime   = finfo_file($finfo, $abs) ?: 'image/png';
        finfo_close($finfo);
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
    };

    $bgUrl    = $dataUri($certificate->background_image ?? '');
    $logo1Url = $dataUri($certificate->logo1 ?? '');
    $logo2Url = $dataUri($certificate->logo2 ?? '');
    $ttd1Url  = $dataUri($certificate->signature_image1 ?? '');
    $ttd2Url  = $dataUri($certificate->signature_image2 ?? '');

    $hasRightSig = $ttd2Url || !empty($certificate->name_signatory2) || !empty($certificate->role2);

    // Judul webinar disimpan di kolom 'division' saat generate — tapi kita bisa
    // coba ambil dari webinar_attendances via serial_number kalau ada.
    // Untuk sekarang: tampilkan teks generik "Webinar" + tanggal.
    $eventDate = Carbon::parse($certificate->start_date)->locale('id')->translatedFormat('j F Y');

    // Coba ambil judul webinar dari webinar_attendances
    $webinarTitle = null;
    try {
        $attendance = \App\Models\WebinarAttendance::where('certificate_id', $certificate->id)->first();
        $webinarTitle = $attendance?->webinar?->title;
    } catch (\Throwable $e) { /* fallback */ }
  @endphp

  <style>
    @page { size: 1123px 794px landscape; margin: 0; }
    html, body { height: 100%; margin: 0; padding: 0; }
    * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    :root {
      --page-w: 1123px;
      --page-h: 794px;
      --green: #1a5c38;
      --dark:  #1B3A34;
      --text:  #111;
      --serif: "Times New Roman", Times, serif;
      --script: "Edwardian Script ITC", "Segoe Script", "Brush Script MT", "Lucida Handwriting", cursive, serif;
    }
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background: #e8e8e8;
    }
    .page {
      position: relative;
      width: var(--page-w);
      height: var(--page-h);
      overflow: hidden;
      background: {{ $bgUrl ? "url('" . $bgUrl . "') center/cover no-repeat" : 'linear-gradient(135deg,#0a2e1c 0%,#1a5c38 55%,#0d3d25 100%)' }};
    }
    /* Overlay semi-transparan supaya teks tetap terbaca meski background gelap */
    .overlay {
      position: absolute;
      inset: 0;
      background: rgba(255,255,255,0.62);
    }
    .content {
      position: absolute;
      inset: 0;
      padding: 48px 72px 36px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* ── LOGOS ── */
    .logos {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: {{ $logo2Url ? 'space-between' : 'center' }};
      margin-bottom: 6px;
    }
    .logos img { max-height: 80px; max-width: 200px; object-fit: contain; }

    /* ── HEADING ── */
    .title {
      font: italic 700 68px var(--script);
      color: var(--dark);
      line-height: 1;
      margin: 0 0 4px;
      text-align: center;
    }
    .subtitle {
      font: 400 17px var(--serif);
      color: #444;
      text-align: center;
      letter-spacing: .6px;
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    /* ── SERIAL ── */
    .serial {
      font: 600 14px var(--serif);
      color: var(--dark);
      background: rgba(255,255,255,0.78);
      border: 1px solid rgba(0,0,0,0.12);
      border-radius: 6px;
      padding: 4px 14px;
      letter-spacing: .2px;
      margin-bottom: 10px;
    }

    /* ── RECIPIENT ── */
    .given-to {
      font: 400 19px var(--serif);
      color: #555;
      margin-bottom: 2px;
    }
    .name {
      font: italic 68px var(--script);
      color: var(--text);
      line-height: 1.1;
      text-align: center;
    }
    .name-line {
      width: 72%;
      max-width: 700px;
      height: 2px;
      background: #111;
      margin: 2px auto 10px;
    }

    /* ── BODY TEXT ── */
    .body {
      font: 400 19px var(--serif);
      color: var(--text);
      text-align: center;
      line-height: 1.65;
    }
    .body .webinar-title {
      font-weight: 700;
      color: var(--dark);
      font-size: 20px;
    }

    /* ── SIGNATURE AREA ── */
    .signatures {
      width: 100%;
      margin-top: auto;
      padding-top: 10px;
      display: flex;
      justify-content: {{ $hasRightSig ? 'space-between' : 'center' }};
      align-items: flex-end;
      padding-bottom: 0;
    }
    .sig {
      width: 260px;
      text-align: center;
      font: 400 17px var(--serif);
      color: var(--text);
      position: relative;
    }
    .sig .role  { margin-bottom: 56px; color: #444; }
    .sig .ttd-wrap {
      position: absolute;
      bottom: 42px;
      left: 50%;
      transform: translateX(-50%);
      width: 200px;
      height: 80px;
      pointer-events: none;
    }
    .sig .ttd-wrap img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      opacity: .95;
    }
    .sig .line { height: 2px; background: #111; margin: 0 0 5px; }
    .sig .sig-name { font-weight: 700; font-size: 17px; }

    @media print { body { background: none; } .page { box-shadow: none; } }
  </style>
</head>
<body>
<div class="page">
  <div class="overlay"></div>
  <div class="content">

    {{-- LOGOS --}}
    <div class="logos">
      @if($logo1Url)
        <img src="{{ $logo1Url }}" alt="Logo">
      @endif
      @if($logo2Url)
        <img src="{{ $logo2Url }}" alt="Logo 2">
      @endif
    </div>

    {{-- JUDUL --}}
    <div class="title">Sertifikat Kehadiran</div>
    <div class="subtitle">Certificate of Attendance</div>

    {{-- NOMOR SERTIFIKAT --}}
    <div class="serial">NO: {{ $certificate->serial_number }}</div>

    {{-- PENERIMA --}}
    <div class="given-to">Diberikan kepada:</div>
    <div class="name">{{ $certificate->name }}</div>
    <div class="name-line"></div>

    {{-- BODY --}}
    <div class="body">
      <div>Telah berpartisipasi sebagai peserta dalam webinar</div>
      @if($webinarTitle)
        <div class="webinar-title">"{{ $webinarTitle }}"</div>
      @endif
      <div>
        yang diselenggarakan oleh <strong>{{ $certificate->company }}</strong>
      </div>
      <div>
        pada <strong>{{ $eventDate }}</strong>
        &nbsp;·&nbsp; <strong>{{ $certificate->city }}</strong>
      </div>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="signatures">

      {{-- Kiri (wajib) --}}
      <div class="sig">
        @if(!empty($certificate->role1))
          <div class="role">{{ $certificate->role1 }}</div>
        @endif
        @if($ttd1Url)
          <div class="ttd-wrap">
            <img src="{{ $ttd1Url }}" alt="TTD 1">
          </div>
        @endif
        <div class="line"></div>
        <div class="sig-name">{{ $certificate->name_signatory1 }}</div>
      </div>

      {{-- Kanan (opsional) --}}
      @if($hasRightSig)
      <div class="sig">
        @if(!empty($certificate->role2))
          <div class="role">{{ $certificate->role2 }}</div>
        @endif
        @if($ttd2Url)
          <div class="ttd-wrap">
            <img src="{{ $ttd2Url }}" alt="TTD 2">
          </div>
        @endif
        @if(!empty($certificate->name_signatory2))
          <div class="line"></div>
          <div class="sig-name">{{ $certificate->name_signatory2 }}</div>
        @endif
      </div>
      @endif

    </div>

  </div>
</div>
</body>
</html>
