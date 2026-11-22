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
        .panel { border: 1px solid #27272a; border-radius: 14px; background: #111113; padding: 20px; margin: 24px 0; }
        .panel h2 { margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .form-grid input { min-width: 0; }
        .actions { display: flex; gap: 8px; margin-top: 14px; }
        .secondary { background: #27272a; }
        @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }
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
        <section class="panel">
            <h2 id="formTitle">Tambah media</h2>
            <form id="mediaForm">
                <div class="form-grid"><input id="title" placeholder="Judul" required><input id="type" placeholder="Jenis: film/series" required><input id="genre" placeholder="Genre" required></div>
                <div class="actions"><button type="submit">Simpan media</button><button type="button" class="secondary" id="cancelEdit" hidden>Batal edit</button></div>
            </form>
        </section>
        <section class="panel">
            <h2>Koleksi pengguna</h2>
            <div class="toolbar"><input id="userId" type="number" min="1" placeholder="ID pengguna"><button id="collectionButton">Lihat koleksi</button></div>
            <div id="collectionStatus" class="status"></div>
            <div id="collectionGrid" class="grid"></div>
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
                grid.innerHTML = items.length ? items.map(media => `<article class="card"><div class="type">${media.type}</div><h2>${media.title}</h2><div class="meta">${media.genre}</div><div class="actions"><button onclick="editMedia(${media.id}, '${media.title.replace(/'/g, "\\'")}', '${media.type}', '${media.genre}')">Edit</button><button class="secondary" onclick="deleteMedia(${media.id})">Hapus</button><button class="secondary" onclick="addToCollection(${media.id})">+ Koleksi</button></div></article>`).join('') : '<div class="empty">Belum ada media yang cocok.</div>';
            } catch (error) { status.textContent = 'Tidak dapat memuat katalog. Jalankan migration dan seeder.'; }
        }
        document.getElementById('searchButton').addEventListener('click', loadMedia);
        document.getElementById('search').addEventListener('keydown', event => { if (event.key === 'Enter') loadMedia(); });
        let editingId = null;
        document.getElementById('mediaForm').addEventListener('submit', async event => {
            event.preventDefault();
            const payload = { title: title.value, type: type.value, genre: genre.value };
            const response = await fetch(editingId ? `/api/media/${editingId}` : '/api/media', { method: editingId ? 'PATCH' : 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(payload) });
            if (!response.ok) { alert('Data tidak valid atau judul sudah digunakan.'); return; }
            editingId = null; event.target.reset(); formTitle.textContent = 'Tambah media'; cancelEdit.hidden = true; loadMedia();
        });
        window.editMedia = (id, currentTitle, currentType, currentGenre) => { editingId = id; title.value = currentTitle; type.value = currentType; genre.value = currentGenre; formTitle.textContent = 'Edit media'; cancelEdit.hidden = false; window.scrollTo({top: 0, behavior: 'smooth'}); };
        window.deleteMedia = async id => { if (!confirm('Hapus media ini?')) return; await fetch(`/api/media/${id}`, {method: 'DELETE', headers: {'Accept': 'application/json'}}); loadMedia(); };
        window.addToCollection = async mediaId => { const id = userId.value; if (!id) { alert('Isi ID pengguna terlebih dahulu.'); return; } const response = await fetch(`/api/users/${id}/collection`, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify({media_id: mediaId})}); alert(response.status === 409 ? 'Media sudah ada di koleksi.' : response.ok ? 'Media ditambahkan ke koleksi.' : 'User atau media tidak ditemukan.'); };
        cancelEdit.addEventListener('click', () => { editingId = null; mediaForm.reset(); formTitle.textContent = 'Tambah media'; cancelEdit.hidden = true; });
        collectionButton.addEventListener('click', async () => { const id = userId.value; if (!id) return; const response = await fetch(`/api/users/${id}/collection`); const result = await response.json(); collectionStatus.textContent = response.ok ? `${result.data.length} media dalam koleksi` : 'User tidak ditemukan'; collectionGrid.innerHTML = response.ok ? result.data.map(media => `<article class="card"><div class="type">${media.type}</div><h2>${media.title}</h2><div class="meta">${media.genre}</div></article>`).join('') : ''; });
        loadMedia();
    </script>
</body>
</html>
