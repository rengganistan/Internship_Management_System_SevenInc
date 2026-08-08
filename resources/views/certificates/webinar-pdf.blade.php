<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>{{ $pdfTitle ?? 'Sertifikat Webinar' }}</title>
  @php
    use Carbon\Carbon;

    // Helper: relative path → data URI
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

    Carbon::setLocale('id');
    $eventDate = Carbon::parse($certificate->start_date)->isoFormat('DD MMMM YYYY');

    // Ambil judul webinar dari webinar_attendances
    $webinarTitle = null;
    try {
        $attendance = \App\Models\WebinarAttendance::where('certificate_id', $certificate->id)->first();
        $webinarTitle = $attendance?->webinar?->title;
    } catch (\Throwable $e) {}

    $company = $certificate->company ?? 'Seven Inc';
    $city    = $certificate->city    ?? 'Yogyakarta';

    // Handle format "JudulWebinar||Company" untuk generate manual
    $webinarTitleFromCompany = null;
    if (str_contains($company, '||')) {
        [$webinarTitleFromCompany, $company] = explode('||', $company, 2);
    }

    // Final judul webinar: dari attendance (alur bukti kehadiran) atau dari company field (generate manual)
    $finalWebinarTitle = $webinarTitle ?? $webinarTitleFromCompany;
  @endphp

  <style>
    @page {
      size: A4 landscape;
      margin: 0;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    html, body {
      width: 297mm;
      height: 210mm;
      overflow: hidden;
      font-family: 'Arial', 'Helvetica Neue', Helvetica, sans-serif;
      background: #fff;
    }

    /* ── PAGE WRAPPER ── */
    .page {
      position: relative;
      width: 297mm;
      height: 210mm;
      overflow: hidden;
      background: #ffffff;
    }

    /* Overlay putih ringan supaya teks mudah dibaca */
    .overlay {
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, 0.72);
      z-index: 1;
    }

    /* ── CONTENT WRAPPER ── */
    .content {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 12mm 25mm;
      text-align: center;
      gap: 0;
      z-index: 2;
    }

    /* ── LOGO ── */
    .logo-wrap {
      margin-bottom: 5mm;
    }
    .logo-wrap img {
      max-height: 18mm;
      max-width: 50mm;
      object-fit: contain;
    }

    /* ── JUDUL ── */
    .cert-title {
      font-size: 28pt;
      font-weight: 900;
      letter-spacing: 4px;
      color: #1a1a1a;
      text-transform: uppercase;
      margin-bottom: 2mm;
      line-height: 1;
    }

    /* ── NOMOR ── */
    .cert-number {
      font-size: 9pt;
      color: #444;
      margin-bottom: 5mm;
      letter-spacing: 0.3px;
    }

    /* ── DIBERIKAN KEPADA ── */
    .given-to {
      font-size: 10pt;
      color: #555;
      margin-bottom: 2mm;
    }

    /* ── NAMA PENERIMA ── */
    .recipient-name {
      font-size: 26pt;
      font-weight: 900;
      color: #1a1a1a;
      letter-spacing: 3px;
      text-transform: uppercase;
      line-height: 1.1;
      margin-bottom: 1mm;
    }
    .name-underline {
      width: 75%;
      height: 1.5px;
      background: #1a1a1a;
      margin: 1mm auto 4mm;
    }

    /* ── SEBAGAI PESERTA ── */
    .as-label {
      font-size: 10pt;
      color: #555;
      margin-bottom: 1mm;
    }
    .role-label {
      font-size: 18pt;
      font-weight: 900;
      letter-spacing: 3px;
      color: #1a1a1a;
      text-transform: uppercase;
      margin-bottom: 4mm;
    }

    /* ── DESKRIPSI WEBINAR ── */
    .webinar-desc {
      font-size: 10pt;
      color: #333;
      line-height: 1.55;
      max-width: 160mm;
      margin-bottom: 4mm;
    }
    .webinar-desc strong {
      color: #1a1a1a;
    }

    /* ── LOKASI & TANGGAL ── */
    .location-date {
      font-size: 10pt;
      color: #555;
      margin-bottom: 6mm;
    }

    /* ── TANDA TANGAN ── */
    .signatures {
      display: flex;
      justify-content: {{ $hasRightSig ? 'space-around' : 'center' }};
      width: 100%;
      gap: 20mm;
    }
    .sig {
      display: flex;
      flex-direction: column;
      align-items: center;
      min-width: 45mm;
    }
    .sig-role {
      font-size: 9pt;
      color: #555;
      margin-bottom: 10mm;
    }
    .sig-ttd {
      width: 40mm;
      height: 14mm;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1mm;
    }
    .sig-ttd img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }
    .sig-line {
      width: 45mm;
      height: 1px;
      background: #1a1a1a;
      margin-bottom: 2mm;
    }
    .sig-name {
      font-size: 10pt;
      font-weight: 700;
      color: #1a1a1a;
    }

    @media print { body { background: none; } .page { box-shadow: none; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }
  </style>
</head>
<body>
<div class="page">
  @if($bgUrl)
  {{-- Background sebagai img tag (lebih reliable di Browsershot daripada CSS background) --}}
  <img src="{{ $bgUrl }}" alt=""
       style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:0;display:block;">
  <div class="overlay"></div>
  @endif

  <div class="content">

    {{-- LOGO --}}
    @if($logo1Url)
    <div class="logo-wrap">
      <img src="{{ $logo1Url }}" alt="Logo">
    </div>
    @endif

    {{-- JUDUL --}}
    <div class="cert-title">Sertifikat</div>

    {{-- NOMOR --}}
    <div class="cert-number">Nomor: {{ $certificate->serial_number }}</div>

    {{-- DIBERIKAN KEPADA --}}
    <div class="given-to">Diberikan kepada:</div>

    {{-- NAMA PESERTA --}}
    <div class="recipient-name">{{ $certificate->name }}</div>
    <div class="name-underline"></div>

    {{-- SEBAGAI PESERTA --}}
    <div class="as-label">Sebagai</div>
    <div class="role-label">Peserta</div>

    {{-- DESKRIPSI --}}
    <div class="webinar-desc">
      Dalam Kegiatan
      @if($finalWebinarTitle)
        <strong>"{{ $finalWebinarTitle }}"</strong>
      @else
        <strong>"Webinar"</strong>
      @endif
      yang diselenggarakan oleh <strong>{{ $company }}</strong>
      pada tanggal <strong>{{ $eventDate }}</strong>
    </div>

    {{-- LOKASI & TANGGAL --}}
    <div class="location-date">{{ $city }}, {{ $eventDate }}</div>

    {{-- TANDA TANGAN --}}
    <div class="signatures">
      {{-- Penandatangan 1 (wajib) --}}
      <div class="sig">
        @if(!empty($certificate->role1))
          <div class="sig-role">{{ $certificate->role1 }}</div>
        @else
          <div class="sig-role">&nbsp;</div>
        @endif
        <div class="sig-ttd">
          @if($ttd1Url)
            <img src="{{ $ttd1Url }}" alt="TTD">
          @endif
        </div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $certificate->name_signatory1 }}</div>
      </div>

      {{-- Penandatangan 2 (opsional) --}}
      @if($hasRightSig)
      <div class="sig">
        @if(!empty($certificate->role2))
          <div class="sig-role">{{ $certificate->role2 }}</div>
        @else
          <div class="sig-role">&nbsp;</div>
        @endif
        <div class="sig-ttd">
          @if($ttd2Url)
            <img src="{{ $ttd2Url }}" alt="TTD 2">
          @endif
        </div>
        @if(!empty($certificate->name_signatory2))
          <div class="sig-line"></div>
          <div class="sig-name">{{ $certificate->name_signatory2 }}</div>
        @endif
      </div>
      @endif
    </div>

  </div>
</div>
</body>
</html>
