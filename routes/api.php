<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Semua route di file ini otomatis berawalan /api (diatur di bootstrap/app.php).

// CRUD standar Media (5 route sekaligus: index, show, store, update, destroy).
// ->parameters(['media' => 'media']) wajib: tanpa ini Laravel menyingularkan "media" jadi
// "medium" sehingga Route Model Binding `Media $media` gagal.
Route::apiResource('media', MediaController::class)->parameters(['media' => 'media']);

// Pengguna: hanya baca (data dibuat lewat seeder).
Route::apiResource('users', UserController::class)->only(['index', 'show']);

// Custom action di luar CRUD standar: mengubah RELASI (tabel pivot media_user).
Route::get('/users/{user}/collection', [UserController::class, 'collection']);
Route::post('/users/{user}/collection', [UserController::class, 'addToCollection']);
Route::delete('/users/{user}/collection/{media}', [UserController::class, 'removeFromCollection']);
Route::apiResource('books', BookController::class);
Route::apiResource('members', MemberController::class);
Route::post('/members/{member}/borrow-book', [MemberController::class, 'borrowBook']);
Route::patch('/members/{member}/return-book/{book}', [MemberController::class, 'returnBook']);
