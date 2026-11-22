<?php

namespace App\Http\Controllers;

use App\Http\Requests\BorrowBookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\MemberResource;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(): mixed
    {
        return MemberResource::collection(Member::with('borrowedBooks')->paginate(10));
    }

    public function store(Request $request): mixed
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'member_id' => ['required', 'string', 'max:50', 'unique:members,member_id']]);

        return (new MemberResource(Member::create($data)))->response()->setStatusCode(201);
    }

    public function show(Member $member): MemberResource
    {
        return new MemberResource($member->load('borrowedBooks'));
    }

    public function update(Request $request, Member $member): MemberResource
    {
        $member->update($request->validate(['name' => ['sometimes', 'string', 'max:255'], 'member_id' => ['sometimes', 'string', 'max:50', 'unique:members,member_id,'.$member->id]]));

        return new MemberResource($member->fresh('borrowedBooks'));
    }

    public function destroy(Member $member): mixed
    {
        $member->delete();

        return response()->json(['message' => 'Anggota berhasil dihapus']);
    }

    public function borrowBook(BorrowBookRequest $request, Member $member): JsonResponse
    {
        $book = DB::transaction(function () use ($request, $member): Book {
            $book = Book::query()->lockForUpdate()->findOrFail($request->integer('book_id'));
            if (! $book->is_available) {
                abort(409, 'Buku sedang dipinjam');
            } $book->update(['is_available' => false, 'member_id' => $member->id]);

            return $book;
        });

        return response()->json(['data' => new BookResource($book->load('member'))], 201);
    }

    public function returnBook(Member $member, Book $book): JsonResponse
    {
        if ($book->member_id !== $member->id) {
            return response()->json(['message' => 'Buku tidak sedang dipinjam anggota ini'], 404);
        }
        DB::transaction(fn () => $book->update(['is_available' => true, 'member_id' => null]));

        return response()->json(['data' => new BookResource($book->fresh('member'))]);
    }
}
