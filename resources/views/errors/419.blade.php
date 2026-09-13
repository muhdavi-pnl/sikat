<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Sesi Kedaluwarsa</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-pnl.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Nunito', Arial, sans-serif;
            background: #f4f6f9;
            color: #34395e;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(52, 57, 94, 0.12);
            padding: 28px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 9999px;
            background: #ffe8a1;
            color: #8a6d1d;
            font-weight: 700;
            margin-bottom: 12px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }
        p {
            margin: 0;
            color: #6c757d;
            line-height: 1.6;
        }
        .actions {
            margin-top: 24px;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
        }
        .btn-primary {
            background: #6777ef;
            color: #fff;
        }
        .btn-primary:hover {
            background: #5566de;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">Error 419</div>
    <h1>Sesi Anda Kedaluwarsa</h1>
    <p>Permintaan tidak dapat diproses karena token keamanan sudah tidak berlaku. Silakan kembali ke halaman utama dan coba lagi.</p>
    <div class="actions">
        <a class="btn btn-primary" href="{{ route('landing') }}">Kembali ke Home</a>
    </div>
</div>
</body>
</html>

