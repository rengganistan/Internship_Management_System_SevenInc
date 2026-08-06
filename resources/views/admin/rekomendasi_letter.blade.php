<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  @page { size: A4; margin: 2cm 2.5cm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    color: #000;
    line-height: 1.6;
  }

  /* ── Kop Surat ── */
  .kop {
    border-bottom: 3px solid #000;
    padding-bottom: 10px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .kop-logo img {
    height: 70px;
    width: auto;
    object-fit: contain;
  }
  .kop-text .company-name {
    font-size: 16pt;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
  }
  .kop-text .company-address {
    font-size: 9pt;
    line-height: 1.5;
    color: #333;
    margin-top: 2px;
  }

  /* ── Judul ── */
  .letter-title {
    text-align: center;
    margin: 20px 0 4px;
    font-size: 13pt;
    font-weight: bold;
    text-decoration: underline;
    text-transform: uppercase;
  }
  .letter-number {
    text-align: center;
    font-size: 11pt;
    margin-bottom: 20px;
  }

  /* ── Penandatangan ── */
  .signatory {
    margin-bottom: 14px;
  }
  table.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
  }
  table.info-table td {
    padding: 2px 0;
    vertical-align: top;
    font-size: 11.5pt;
  }
  table.info-table td:first-child { width: 160px; }
  table.info-table td:nth-child(2) { width: 16px; }

  /* ── Separator ── */
  .section-divider { margin: 14px 0; }

  /* ── Body paragraf ── */
  .body-text {
    text-align: justify;
    margin-bottom: 12px;
    font-size: 11.5pt;
  }

  .closing {
    margin-top: 20px;
    margin-bottom: 8px;
    font-size: 11.5pt;
  }

  /* ── TTD ── */
  .ttd-area {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
  }
  .ttd-block {
    text-align: center;
    min-width: 200px;
  }
  .ttd-place-date {
    font-size: 11.5pt;
    margin-bottom: 4px;
  }
  .ttd-title { font-size: 11pt; margin-bottom: 60px; }
  .ttd-img { height: 70px; margin: 0 auto 4px; display: block; }
  .ttd-name { font-weight: bold; font-size: 11.5pt; border-top: 1px solid #000; padding-top: 4px; }
</style>
</head>
<body>

{{-- KOP SURAT --}}
<div class="kop">
  @if(!empty($logoData))
  <div class="kop-logo">
    <img src="{{ $logoData }}" alt="Logo">
  </div>
  @endif
  <div class="kop-text">
    <div class="company-name">{{ $companyName }}</div>
    <div class="company-address">
      {{ $companyAddress }}<br>
      @if(!empty($companyPostalCode) || !empty($companyPhone))
        Kode Pos: {{ $companyPostalCode ?? '-' }} | Telp: {{ $companyPhone ?? '-' }}
      @endif
    </div>
  </div>
</div>

{{-- JUDUL --}}
<div class="letter-title">Surat Rekomendasi</div>
<div class="letter-number">Nomor : {{ $letterNumber }}</div>

{{-- YANG BERTANDA TANGAN --}}
<p class="body-text">Saya yang bertanda tangan di bawah ini :</p>
<div class="signatory">
  <table class="info-table">
    <tr>
      <td>Nama Lengkap</td><td>:</td>
      <td><strong>{{ $leaderName }}</strong></td>
    </tr>
    <tr>
      <td>Alamat</td><td>:</td>
      <td>{{ $companyAddress }}</td>
    </tr>
    <tr>
      <td>Jabatan</td><td>:</td>
      <td>{{ $leaderTitle }}</td>
    </tr>
  </table>
</div>

<p class="body-text">Dengan ini menerangkan bahwa :</p>

{{-- DATA PEMAGANG --}}
<div class="signatory">
  <table class="info-table">
    <tr>
      <td>Nama Lengkap</td><td>:</td>
      <td><strong>{{ $participantName }}</strong></td>
    </tr>
    <tr>
      <td>NIM</td><td>:</td>
      <td>{{ $participantId }}</td>
    </tr>
    <tr>
      <td>Program Studi</td><td>:</td>
      <td>{{ $participantMajor }}</td>
    </tr>
    <tr>
      <td>Asal Sekolah/Kampus</td><td>:</td>
      <td>{{ $participantInstitute }}</td>
    </tr>
  </table>
</div>

{{-- BODY SURAT --}}
<p class="body-text">{{ $bodyText }}</p>

{{-- PENUTUP --}}
<p class="body-text">Demikian surat rekomendasi ini dibuat dengan penuh kesadaran dan tanpa paksaan dari pihak manapun dan untuk dipergunakan sebagaimana mestinya.</p>

{{-- TTD --}}
<div class="ttd-area">
  <div class="ttd-block">
    <div class="ttd-place-date">{{ $companyCity }}, {{ $letterDateStr }}</div>
    <div class="ttd-title">{{ $leaderTitle }} {{ $companyName }}<br><small style="font-size:10pt;font-weight:normal;">({{ $companyBrand }})</small></div>
    @if(!empty($stampData))
    <img src="{{ $stampData }}" alt="TTD" class="ttd-img">
    @endif
    <div class="ttd-name">{{ $leaderName }}</div>
  </div>
</div>

</body>
</html>
