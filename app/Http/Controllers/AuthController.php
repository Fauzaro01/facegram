<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index() {
        return "Hello world";
    }

    public function register(Request $req) {
        $validated = $req->validate([
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username',
            'password' => 'required|min:6',
            'is_private' => 'boolean',
        ]);

        $user =  User::create([
            'full_name' => $req->full_name,
            'username' => $req->username,
            'password' => Hash::make($req->password),
            'bio' => $req->bio,
            'is_private' => $req->is_private,
        ]);

        $token = $user->createToken($validated['password'])->plainTextToken;
        
        
        return response()->json([
            'message' => 'Register Success',
            'token' => $token,
            'user' => $user
        ], 201);
    }
    
    public function login(Request $req) {
        $validated = $req->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        $user = User::where('username', $validated['username'])->first();
        
        if (!$user) {
            return response()->json([
                'message' => "Wrong username or password"
            ]);
        }
        
        if(Auth::attempt($validated)) {
            $token = $user->createToken($validated['password'])->plainTextToken;
            
            return response()->json([
                'message'=> 'Login Success',
                'token'=> $token,
                'user' => $user
            ]);
        }
    }
    
    public function logout(Request $req) {
        $validated = $req->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $token = $req->user()->currentAccessToken()->delete();
            
        return response()->json([
            'message'=> 'Logout Success',
        ]);
        
    }
}
