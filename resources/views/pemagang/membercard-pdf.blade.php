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
    html, body {
      width: 85.6mm;
      height: 53.98mm;
      overflow: hidden;
      background: transparent;
      font-family: 'Arial', sans-serif;
    }

    .card {
      width: 85.6mm;
      height: 53.98mm;
      background: linear-gradient(135deg, #1a5c38 0%, #0d3d25 60%, #0a2e1c 100%);
      position: relative;
      border-radius: 4mm;
      overflow: hidden;
      color: white;
      padding: 5mm 6mm;
    }

    /* Decorative circles */
    .circle-1 {
      position: absolute;
      width: 32mm;
      height: 32mm;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      top: -10mm;
      right: -8mm;
    }
    .circle-2 {
      position: absolute;
      width: 20mm;
      height: 20mm;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
      bottom: -5mm;
      left: 30mm;
    }
    .circle-3 {
      position: absolute;
      width: 12mm;
      height: 12mm;
      border-radius: 50%;
      border: 0.5mm solid rgba(255,255,255,0.12);
      top: 8mm;
      right: 20mm;
    }

    .top-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 3mm;
    }

    .brand {
      font-size: 7pt;
      font-weight: bold;
      letter-spacing: 0.5mm;
      text-transform: uppercase;
      color: rgba(255,255,255,0.7);
    }

    .card-type {
      font-size: 5.5pt;
      color: rgba(255,255,255,0.5);
      text-align: right;
      text-transform: uppercase;
      letter-spacing: 0.3mm;
    }

    .name {
      font-size: 11pt;
      font-weight: bold;
      letter-spacing: 0.2mm;
      margin-bottom: 1mm;
      color: #ffffff;
    }

    .divisi {
      font-size: 7pt;
      color: rgba(255,255,255,0.65);
      margin-bottom: 3mm;
    }

    .bottom-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      position: absolute;
      bottom: 5mm;
      left: 6mm;
      right: 6mm;
    }

    .info-group { }

    .info-label {
      font-size: 5pt;
      color: rgba(255,255,255,0.5);
      text-transform: uppercase;
      letter-spacing: 0.3mm;
      margin-bottom: 0.5mm;
    }

    .info-value {
      font-size: 7pt;
      font-weight: bold;
      color: rgba(255,255,255,0.9);
    }

    .code-box {
      background: rgba(255,255,255,0.12);
      border: 0.3mm solid rgba(255,255,255,0.2);
      border-radius: 1.5mm;
      padding: 1.5mm 3mm;
      text-align: center;
    }

    .code-label {
      font-size: 5pt;
      color: rgba(255,255,255,0.5);
      text-transform: uppercase;
      letter-spacing: 0.3mm;
    }

    .code-value {
      font-size: 9pt;
      font-weight: bold;
      font-family: 'Courier New', monospace;
      letter-spacing: 0.5mm;
      color: #a8e6c3;
    }

    .divider {
      width: 8mm;
      height: 0.4mm;
      background: rgba(255,255,255,0.3);
      margin: 1.5mm 0;
    }
  </style>
</head>
<body>
<div class="card">
  <div class="circle-1"></div>
  <div class="circle-2"></div>
  <div class="circle-3"></div>

  <div class="top-row">
    <div class="brand">{{ $brand ?? 'Seveninc' }}</div>
    <div class="card-type">Intern<br>Member Card</div>
  </div>

  <div class="divider"></div>

  <div class="name">{{ $name }}</div>
  <div class="divisi">{{ $divisi ?? 'Magang' }}</div>

  <div class="bottom-row">
    <div>
      <div class="info-group" style="margin-bottom: 2mm;">
        <div class="info-label">Angkatan</div>
        <div class="info-value">{{ $angkatan ?? '-' }}</div>
      </div>
      <div class="info-group">
        <div class="info-label">Instansi</div>
        <div class="info-value">{{ $instansi ?? '-' }}</div>
      </div>
    </div>

    <div class="code-box">
      <div class="code-label">Kode Member</div>
      <div class="code-value">{{ $code }}</div>
    </div>
  </div>
</div>
</body>
</html>
