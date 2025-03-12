<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genre = Genre::where('del_flg', 0)->get(); 
        return response()->json($genre, 200);
    }
    

    /**genre
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $genre = Genre::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm mới thể loại sách thành công',
            'genre' => $genre
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $genre = Genre::find($id);

        // dd($genre);

        if (!$genre) {
            return response()->json(['message' => "Không tìm thấy tên thể loại sách"], 404);
        }

        return response()->json($genre);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $genre = Genre::find($id);


        if (!$genre) {
            return response()->json(['message' => "Không tìm thấy tên thể loại sách"], 404);
        }


        // dd($request->all());
        $genre->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật tên thể loại sách thành công',
            'genre' => $genre
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $genre = Genre::find($id);
    
        if (!$genre) {
            return response()->json(['message' => 'Không tìm thấy thể loại'], 404);
        }
    
        $genre->update(['del_flg' => 1]);
    
        return response()->json(['message' => 'Thể loại đã bị ẩn'], 200);
    }
    
}
