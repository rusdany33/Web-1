# FlipPlay REST API (Pemrograman Web Modul 4)

REST API Laravel 12: CRUD Media, relasi many-to-many Media <-> Pengguna (koleksi), Form Request, API Resource, custom action attach/detach.

## Cara pasang (di atas project Laravel 12 baru)
```bash
composer create-project laravel/laravel flipplay
cd flipplay
php artisan install:api          # membuat routes/api.php
# salin folder app/, bootstrap/, database/, routes/ dari paket ini, timpa file yang ada
# buat database MySQL kosong, lalu atur DB_* di .env
php artisan migrate:fresh --seed
php artisan route:list --path=api
php artisan serve
```
Di Postman, kirim header `Accept: application/json`.

## Endpoint
| Method | URL | Fungsi | Status |
|---|---|---|---|
| GET | /api/media?search=kata | Daftar media + cari judul + pagination | 200 |
| GET | /api/media/{id} | Detail media + pengguna pengoleksi | 200 / 404 |
| POST | /api/media | Tambah media | 201 / 422 |
| PATCH | /api/media/{id} | Ubah media | 200 / 404 / 422 |
| DELETE | /api/media/{id} | Hapus media | 200 / 404 |
| GET | /api/users | Daftar pengguna | 200 |
| GET | /api/users/{id} | Detail pengguna + koleksi | 200 / 404 |
| GET | /api/users/{id}/collection | Koleksi media pengguna | 200 |
| POST | /api/users/{id}/collection | Tambah media ke koleksi `{ "media_id": 1 }` | 201 / 409 / 422 |
| DELETE | /api/users/{id}/collection/{media} | Hapus media dari koleksi | 200 / 404 |

## Skenario uji Postman
Happy path: POST media valid (201) -> GET index (200) -> GET ?search= (200) -> GET detail (200) -> PATCH (200) -> POST collection (201) -> GET collection (200) -> DELETE collection (200) -> DELETE media (200).

Failure path: POST media `title` kosong (422) -> POST media judul duplikat (422) -> GET /api/media/9999 (404) -> POST collection `media_id` sama dua kali (409) -> POST collection `media_id` 9999 (422) -> DELETE collection media yang tidak ada di koleksi (404).
