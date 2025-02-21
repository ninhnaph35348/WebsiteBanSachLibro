<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authors = Author::all();
        return response()->json($authors);
    }

    /**Author
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {



        $author = Author::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm mới tác giả thành công',
            'author' => $author
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $author = Author::find($id);

        // dd($author);

        if (!$author) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }

        return response()->json($author);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $author = Author::find($id);


        if (!$author) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }


        // dd($request->all());
        $author->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật tên tác giả thành công',
            'author' => $author
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $author = Author::find($id);


        if (!$author) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }

        $author->delete();

        return response()->json(['message' => 'Xóa tác giả thành công']);
    }
}
