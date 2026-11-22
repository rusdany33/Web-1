#!/usr/bin/env bash
# Jalankan DI DALAM folder project Laravel kamu (yang sudah terhubung ke repo GitHub).
# Pemakaian: bash push.sh
set -e
git checkout -b modul-4 2>/dev/null || git checkout modul-4
git add .
git commit -m "Modul 4: FlipPlay REST API (CRUD Media, relasi many-to-many, Form Request, API Resource)"
git push -u origin modul-4
