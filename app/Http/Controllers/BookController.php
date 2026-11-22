<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(): mixed
    {
        return BookResource::collection(Book::with('member')->paginate(10));
    }

    public function store(Request $request): mixed
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'author' => ['required', 'string', 'max:255']]);

        return (new BookResource(Book::create($data)))->response()->setStatusCode(201);
    }

    public function show(Book $book): BookResource
    {
        return new BookResource($book->load('member'));
    }

    public function update(Request $request, Book $book): BookResource
    {
        $book->update($request->validate(['title' => ['sometimes', 'string', 'max:255'], 'author' => ['sometimes', 'string', 'max:255']]));

        return new BookResource($book->fresh('member'));
    }

    public function destroy(Book $book): mixed
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
