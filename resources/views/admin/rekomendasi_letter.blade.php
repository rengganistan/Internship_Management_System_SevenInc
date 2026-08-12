<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  @page { size: A4; margin: 2.5cm 3cm 2.5cm 3cm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    color: #000;
    line-height: 1.8;
  }

  /* ── Kop Surat ── */
  .kop {
    border-bottom: 3px double #000;
    padding-bottom: 12px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
  }
  .kop-logo img {
    height: 72px;
    width: auto;
    object-fit: contain;
  }
  .kop-text .company-name {
    font-size: 16pt;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .kop-text .company-address {
    font-size: 9.5pt;
    line-height: 1.6;
    color: #222;
  }

  /* ── Judul ── */
  .letter-title {
    text-align: center;
    margin: 24px 0 6px;
    font-size: 14pt;
    font-weight: bold;
    text-decoration: underline;
    text-transform: uppercase;
    letter-spacing: 2px;
  }
  .letter-number {
    text-align: center;
    font-size: 11pt;
    margin-bottom: 28px;
    color: #333;
  }

  /* ── Paragraf intro ── */
  .intro {
    font-size: 12pt;
    margin-bottom: 12px;
  }

  /* ── Tabel info penandatangan & pemagang ── */
  table.info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 6px 0 18px 12px;
  }
  table.info-table td {
    padding: 4px 0;
    vertical-align: top;
    font-size: 11.5pt;
    line-height: 1.6;
  }
  table.info-table td:first-child {
    width: 170px;
    font-weight: normal;
  }
  table.info-table td:nth-child(2) {
    width: 20px;
    text-align: center;
  }
  table.info-table td:last-child {
    padding-left: 4px;
  }

  /* ── Separator ── */
  .section-gap { margin: 16px 0; }

  /* ── Body paragraf ── */
  .body-text {
    text-align: justify;
    margin-bottom: 14px;
    font-size: 12pt;
    line-height: 1.8;
    text-indent: 0;
  }

  /* ── TTD ── */
  .ttd-area {
    margin-top: 32px;
    display: flex;
    justify-content: flex-end;
  }
  .ttd-block {
    text-align: center;
    min-width: 220px;
  }
  .ttd-place-date {
    font-size: 12pt;
    margin-bottom: 4px;
  }
  .ttd-title {
    font-size: 11.5pt;
    margin-bottom: 70px;
    line-height: 1.6;
  }
  .ttd-img {
    height: 72px;
    margin: 0 auto 4px;
    display: block;
    object-fit: contain;
  }
  .ttd-name {
    font-weight: bold;
    font-size: 12pt;
    border-top: 1.5px solid #000;
    padding-top: 5px;
    margin-top: 4px;
  }
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
      {{ $companyAddress }}
      @if(!empty($companyPostalCode) || !empty($companyPhone))
        <br>Kode Pos: {{ $companyPostalCode ?? '-' }}&nbsp;&nbsp;|&nbsp;&nbsp;Telp: {{ $companyPhone ?? '-' }}
      @endif
    </div>
  </div>
</div>

{{-- JUDUL --}}
<div class="letter-title">Surat Rekomendasi</div>
<div class="letter-number">Nomor : {{ $letterNumber }}</div>

{{-- YANG BERTANDA TANGAN --}}
<p class="intro">Saya yang bertanda tangan di bawah ini :</p>

<table class="info-table">
  <tr>
    <td>Nama Lengkap</td>
    <td>:</td>
    <td><strong>{{ $leaderName }}</strong></td>
  </tr>
  <tr>
    <td>Alamat</td>
    <td>:</td>
    <td>{{ $companyAddress }}</td>
  </tr>
  <tr>
    <td>Jabatan</td>
    <td>:</td>
    <td>{{ $leaderTitle }}</td>
  </tr>
</table>

<p class="intro">Dengan ini menerangkan bahwa :</p>

{{-- DATA PEMAGANG --}}
<table class="info-table">
  <tr>
    <td>Nama Lengkap</td>
    <td>:</td>
    <td><strong>{{ $participantName }}</strong></td>
  </tr>
  <tr>
    <td>NIM</td>
    <td>:</td>
    <td>{{ $participantId }}</td>
  </tr>
  <tr>
    <td>Program Studi</td>
    <td>:</td>
    <td>{{ $participantMajor }}</td>
  </tr>
  <tr>
    <td>Asal Sekolah/Kampus</td>
    <td>:</td>
    <td>{{ $participantInstitute }}</td>
  </tr>
</table>

{{-- BODY SURAT --}}
<p class="body-text">{{ $bodyText }}</p>

{{-- PENUTUP --}}
<p class="body-text">Demikian surat rekomendasi ini dibuat dengan penuh kesadaran dan tanpa paksaan dari pihak manapun dan untuk dipergunakan sebagaimana mestinya.</p>

{{-- TTD --}}
<div class="ttd-area">
  <div class="ttd-block">
    <div class="ttd-place-date">{{ $companyCity }}, {{ $letterDateStr }}</div>
    <div class="ttd-title">
      {{ $leaderTitle }} {{ $companyName }}<br>
      <span style="font-size:10.5pt;font-weight:normal;">({{ $companyBrand }})</span>
    </div>
    @if(!empty($stampData))
    <img src="{{ $stampData }}" alt="TTD" class="ttd-img">
    @endif
    <div class="ttd-name">{{ $leaderName }}</div>
  </div>
</div>

</body>
</html>
