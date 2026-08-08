<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    @page {
      size: 85.6mm 53.98mm;
      margin: 0;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html {
      width: 85.6mm;
      height: 53.98mm;
    }
    body {
      width: 85.6mm;
      height: 53.98mm;
      overflow: hidden;
      background: #1a3a2a;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card {
      width: 85.6mm;
      height: 53.98mm;
      background: #1a3a2a;
      position: relative;
      overflow: hidden;
      font-family: 'Georgia', 'Times New Roman', serif;
    }

    /* Brand di kanan atas — gold italic */
    .brand {
      position: absolute;
      top: 5mm;
      right: 5mm;
      font-size: 9pt;
      font-style: italic;
      font-weight: bold;
      color: #c9a84c;
      letter-spacing: 0.3mm;
      text-align: right;
    }

    /* Nama besar di tengah */
    .name-area {
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      transform: translateY(-60%);
      text-align: center;
      padding: 0 5mm;
    }

    .name {
      font-size: 16pt;
      font-weight: normal;
      color: #c9a84c;
      letter-spacing: 0.5mm;
      margin-bottom: 2.5mm;
    }

    /* Garis divider gold */
    .divider {
      width: 70%;
      height: 0.3mm;
      background: #c9a84c;
      margin: 0 auto;
    }

    /* Info pills area */
    .pills-area {
      position: absolute;
      bottom: 5mm;
      left: 5mm;
      right: 5mm;
      display: flex;
      flex-direction: column;
      gap: 1.5mm;
    }

    .pill-row {
      display: flex;
      gap: 2mm;
      flex-wrap: wrap;
    }

    .pill {
      background: #d4c06a;
      border-radius: 3mm;
      padding: 1mm 3mm;
      display: inline-flex;
      flex-direction: column;
    }

    .pill-label {
      font-size: 4.5pt;
      font-weight: bold;
      color: #1a3a2a;
      text-transform: uppercase;
      letter-spacing: 0.3mm;
      line-height: 1.2;
    }

    .pill-value {
      font-size: 6pt;
      color: #1a3a2a;
      line-height: 1.3;
    }
  </style>
</head>
<body>
<div class="card">

  {{-- Brand kanan atas --}}
  <div class="brand">{{ $brand ?? 'magangjogja.com' }}</div>

  {{-- Nama di tengah --}}
  <div class="name-area">
    <div class="name">{{ $name }}</div>
    <div class="divider"></div>
  </div>

  {{-- Pills bawah --}}
  <div class="pills-area">
    <div class="pill-row">
      <div class="pill">
        <span class="pill-label">ID:</span>
        <span class="pill-value">{{ $code }}</span>
      </div>
      <div class="pill">
        <span class="pill-label">Angkatan:</span>
        <span class="pill-value">{{ $angkatan ?? '-' }}</span>
      </div>
    </div>
    <div class="pill-row">
      <div class="pill">
        <span class="pill-label">Kampus/Sekolah:</span>
        <span class="pill-value">{{ $instansi ?? '-' }}</span>
      </div>
    </div>
  </div>

</div>
</body>
</html>
