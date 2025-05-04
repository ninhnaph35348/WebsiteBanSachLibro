<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::get();

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'text' => 'nullable|string',
            'long_text' => 'nullable|string',
        ]);

        $post = Post::create([
            'name' => $request->name,
            'text' => $request->text,
            'long_text' => $request->long_text,
            'del_flg' => 0,
        ]);

        return response()->json([
            'message' => 'Thêm mới bài viết thành công',
            'post' => $post
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::find($id);

        if (!$post || $post->del_flg == 1) {
            return response()->json(['message' => "Không tìm thấy bài viết"], 404);
        }

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post || $post->del_flg == 1) {
            return response()->json(['message' => "Không tìm thấy bài viết"], 404);
        }

        $post->update($request->only('name', 'text', 'long_text'));

        return response()->json([
            'message' => 'Cập nhật bài viết thành công',
            'post' => $post
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post || $post->del_flg == 1) {
            return response()->json(['message' => "Không tìm thấy bài viết"], 404);
        }

        $post->update(['del_flg' => 1]);

        return response()->json(['message' => 'Bài viết đã được ẩn']);
    }
}
