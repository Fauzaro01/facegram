<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostAttach;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function store(Request $req) {
        $validated = $req->validate([
            'caption' => 'required',
            'attachments' => 'required',
            'attachments.*' => 'required|mimes:png,jpeg,webp,jpg,gif|max:20000',
        ]);

        $post = Post::create([
            'caption' => $validated['caption'],
            'user_id' => $req->user()->id
        ]);

        if($req->hasFile('attachments')) {
            foreach($validated['attachments'] as $gambar) {
                $path = $gambar->store('gambar', 'public');
                
                PostAttach::create([
                    'storage_path' => $path,
                    'post_id' => $post->id
                ]);
            }
        }

        return response()->json([
            'message' => 'Create post success',
        ], 201);
        
    }

    public function delete(Request $req, $id) {
        $post = Post::findOrFail($id);
        
        if(!$post) {
            return response()->json([
                'message' => 'Post not found'
            , 404]);
        };
        
        if ($post->user_id !== $req->user()->id) {
            return response()->json([
                "message" => "Forbidden access"
            ], 403);   
        }

        $post->delete();

        return response([], 204);
    }

    public function index(Request $req) {
        return response()->json([
            'page' => $req->query('page'),
            'size' => $req->query('size'),
            'posts' => Post::paginate($req->query('size')),
        ]);
    }


}