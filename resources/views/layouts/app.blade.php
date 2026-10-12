<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Produtos') · Cadastro</title>
    <style>
        :root { --bg:#f5f6f8; --card:#fff; --text:#1f2933; --muted:#6b7280; --line:#e5e7eb; --primary:#2563eb; --danger:#dc2626; --ok:#16a34a; --warn:#d97706; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--bg); color:var(--text); font-size:15px; }
        header { background:#111827; color:#fff; padding:14px 24px; display:flex; gap:24px; align-items:center; }
        header strong { font-size:17px; }
        header a { color:#d1d5db; text-decoration:none; }
        header a:hover { color:#fff; }
        main { max-width:1100px; margin:24px auto; padding:0 16px; }
        .card { background:var(--card); border:1px solid var(--line); border-radius:10px; padding:20px; margin-bottom:20px; }
        h1 { font-size:22px; margin:0 0 16px; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:10px 8px; border-bottom:1px solid var(--line); vertical-align:middle; }
        th { font-size:12px; text-transform:uppercase; color:var(--muted); letter-spacing:.04em; }
        .btn { display:inline-block; padding:8px 14px; border-radius:6px; border:1px solid var(--line); background:#fff; color:var(--text); text-decoration:none; cursor:pointer; font-size:14px; }
        .btn-primary { background:var(--primary); border-color:var(--primary); color:#fff; }
        .btn-danger { background:var(--danger); border-color:var(--danger); color:#fff; }
        .btn-sm { padding:4px 10px; font-size:13px; }
        .row { display:flex; gap:12px; flex-wrap:wrap; align-items:end; }
        .field { display:flex; flex-direction:column; gap:4px; margin-bottom:14px; flex:1; min-width:160px; }
        label { font-size:13px; color:var(--muted); }
        input, select, textarea { padding:8px 10px; border:1px solid var(--line); border-radius:6px; font:inherit; background:#fff; }
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; }
        .alert-success { background:#dcfce7; color:#166534; }
        .alert-error { background:#fee2e2; color:#991b1b; }
        .error { color:var(--danger); font-size:13px; }
        .badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; background:#eef2ff; color:#3730a3; }
        .badge-off { background:#f3f4f6; color:var(--muted); }
        .badge-warn { background:#fef3c7; color:#92400e; }
        .stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px; }
        .stat span { color:var(--muted); font-size:13px; } .stat b { display:block; font-size:24px; margin-top:4px; }
        .actions { display:flex; gap:6px; }
        .muted { color:var(--muted); }
        .pagination nav { margin-top:12px; }
    </style>
</head>
<body>
<header>
    <strong>📦 Cadastro de Produtos</strong>
    <a href="/products">Produtos</a>
    <a href="/categories">Categorias</a>
</header>
<main>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
