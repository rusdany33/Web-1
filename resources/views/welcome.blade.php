<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlipPlay</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, system-ui, sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #09090b; color: #f4f4f5; }
        header { border-bottom: 1px solid #27272a; background: #111113; }
        .nav, main { width: min(1100px, calc(100% - 40px)); margin: auto; }
        .nav { display: flex; justify-content: space-between; padding: 22px 0; }
        .brand { color: #fb3b30; font-size: 25px; font-weight: 800; }
        .badge, .meta, .status, .hero p { color: #a1a1aa; }
        .hero { padding: 64px 0 34px; }
        h1 { max-width: 650px; margin: 0 0 14px; font-size: clamp(38px, 7vw, 70px); line-height: .98; letter-spacing: -.06em; }
        h1 span, .type { color: #fb3b30; }
        .hero p { max-width: 600px; font-size: 17px; line-height: 1.6; }
        .toolbar { display: flex; gap: 12px; margin: 28px 0; }
        input { flex: 1; border: 1px solid #3f3f46; border-radius: 10px; background: #18181b; color: white; padding: 13px 15px; outline: none; }
        button { border: 0; border-radius: 10px; background: #fb3b30; color: white; padding: 0 20px; font-weight: 700; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; padding-bottom: 60px; }
        .card { border: 1px solid #27272a; border-radius: 14px; background: #111113; padding: 20px; min-height: 170px; }
        .type { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        h2 { margin: 14px 0 8px; font-size: 21px; }
        .meta { font-size: 14px; }
        .empty { grid-column: 1 / -1; color: #a1a1aa; padding: 30px 0; }
    </style>
</head>
<body>
    <header><div class="nav"><div class="brand">FlipPlay</div><div class="badge">Media collection API</div></div></header>
    <main>
        <section class="hero">
            <h1>Temukan media favoritmu di <span>FlipPlay.</span></h1>
            <p>Kelola film, series, game, dan musik dalam satu koleksi. Data halaman ini diambil langsung dari Laravel REST API.</p>
            <div class="toolbar"><input id="search" placeholder="Cari judul media..."><button id="searchButton">Cari</button></div>
        </section>
        <div id="status" class="status">Memuat katalog...</div>
        <section id="mediaGrid" class="grid"></section>
    </main>
    <script>
        async function loadMedia() {
            const search = document.getElementById('search').value.trim();
            const status = document.getElementById('status');
            const grid = document.getElementById('mediaGrid');
            status.textContent = 'Memuat katalog...';
            try {
                const response = await fetch('/api/media' + (search ? '?search=' + encodeURIComponent(search) : ''));
                if (!response.ok) throw new Error('API error');
                const result = await response.json();
                const items = result.data ?? [];
                status.textContent = `${items.length} media ditemukan`;
                grid.innerHTML = items.length ? items.map(media => `<article class="card"><div class="type">${media.type}</div><h2>${media.title}</h2><div class="meta">${media.genre} · ${media.release_year ?? 'Tahun tidak tersedia'}</div></article>`).join('') : '<div class="empty">Belum ada media yang cocok.</div>';
            } catch (error) { status.textContent = 'Tidak dapat memuat katalog. Jalankan migration dan seeder.'; }
        }
        document.getElementById('searchButton').addEventListener('click', loadMedia);
        document.getElementById('search').addEventListener('keydown', event => { if (event.key === 'Enter') loadMedia(); });
        loadMedia();
    </script>
</body>
</html>
