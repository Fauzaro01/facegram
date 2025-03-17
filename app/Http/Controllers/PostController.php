<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function store(Request $req) {
        $validated = $req->validate([
            'caption' => 'required',
            'attachments.*' => 'required|array|mimes:png,jpeg,webp,jpg,gifmax:20000',
        ]);

        
    }
} 
