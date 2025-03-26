<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;

class FollowController extends Controller
{
    public function index(Request $req) {
        $authUserId = $req->user()->id;

        $users = User::whereNotIn('id', function ($query) use ($authUserId) {
                        $query->select('following_id')
                            ->from('follows')
                            ->where('follower_id', $authUserId);
                    })
                    ->where('id', '!=', $authUserId)
                    ->get(['id', 'full_name', 'username', 'bio', 'is_private', 'created_at', 'updated_at']);

        return response()->json([
            "users" => $users
        ], 200);
    }

    public function followUser($username, Request $req) {
        $user = User::where('username', $username)->first();
    
        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        if ($user->id == Auth::id()) {
            return response()->json([
                "message" => "You are not allowed to follow yourself"
            ], 422);
        }

        $followerId = $req->user()->id;
        $followingId = $user->id;

        $existingFollow = Follow::where('follower_id', $followerId)
                                ->where('following_id', $followingId)
                                ->first();
        
        if ($existingFollow) {
            return response()->json([
                "message" => "You are already followed",
                "status" => $existingFollow->is_accepted ? "following" : "requested"
            ], 422);
        }

        $pengikut = Follow::where('follower_id', $followingId)
                        ->where('following_id', $followerId)
                        ->first();

        if ($pengikut) {
            Follow::create([
                'follower_id' => $followerId,
                'following_id' => $followingId,
                'is_accepted' => true
            ]);

            $pengikut->update(['is_accepted' => true]);

            return response()->json([
                "message" => "Follow success",
                "status" => "following"
            ], 200);
        }

        Follow::create([
            'follower_id' => $followerId,
            'following_id' => $followingId,
            'is_accepted' => false
        ]);

        return response()->json([
            "message" => "Follow success",
            "status" => "requested"
        ], 200);
            
    }
    
    public function unFollowUser($username, Request $req) {
        if (!Auth::check()) {
            return response()->json([
                "message" => "Unauthenticated."
            ], 401);
        }

        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $followed = Follow::where('follower_id', $req->user()->id)
                        ->where('following_id', $user->id)
                        ->first();
        
        if (!$followed) {
            return response()->json([
                "message" => "You are not following the user"
            ], 422);
        }

        $followed->delete();

        return response()->noContent(); // Sesuai dengan HTTP 204
    }


    public function acceptUser($username, Request $req) {
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $pengikut = Follow::where('following_id', $req->user()->id)
                        ->where('follower_id', $user->id)
                        ->first();

        if (!$pengikut) {
            return response()->json([
                "message" => "The user is not following you"
            ], 422);
        }

        if ($pengikut->is_accepted) {
            return response()->json([
                "message" => "Follow request is already accepted"
            ], 422);
        }

        $pengikut->update(['is_accepted' => 1]);

        return response()->json([
            "message" => "Follow request accepted"
        ], 200);
    }

    public function getFollowers($username, Request $req) {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $followers = Follow::where('following_id', $user->id)
                        ->with('follower:id,full_name,username,bio,is_private,created_at')
                        ->get()
                        ->map(function ($follow) {
                            return [
                                "id" => $follow->follower->id,
                                "full_name" => $follow->follower->full_name,
                                "username" => $follow->follower->username,
                                "bio" => $follow->follower->bio,
                                "is_private" => $follow->follower->is_private,
                                "created_at" => $follow->follower->created_at,
                                "is_requested" => !$follow->is_accepted
                            ];
                        });

        return response()->json([
            'followers' => $followers
        ], 200);
    }

    public function getUnFollowerUser(Request $req) {
        $userId = $req->user()->id;

        $followedUserIds = Follow::where('follower_id', $userId)
                                ->pluck('following_id')
                                ->toArray();

        $users = User::whereNotIn('id', $followedUserIds)
                    ->where('id', '!=', $userId)
                    ->get(['id', 'full_name', 'username', 'bio', 'is_private', 'created_at', 'updated_at']);

        return response()->json([
            'users' => $users
        ], 200);
    }

    public function getDetailUser($username, Request $req) {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $authUserId = $req->user()->id;

        $isYourAccount = $authUserId == $user->id;

        $follow = Follow::where('follower_id', $authUserId)
                        ->where('following_id', $user->id)
                        ->first();

        if ($follow) {
            $followingStatus = $follow->is_accepted ? 'following' : 'requested';
        } else {
            $followingStatus = 'not-following';
        }

        $followersCount = Follow::where('following_id', $user->id)->count();
        $followingCount = Follow::where('follower_id', $user->id)->count();
        
        $postsCount = Post::where('user_id', $user->id)->count();

        $posts = [];
        if (!$user->is_private || $followingStatus == 'following' || $isYourAccount) {
            $posts = Post::where('user_id', $user->id)
                        ->with('attachments')
                        ->get(['id', 'caption', 'created_at', 'deleted_at'])
                        ->map(function ($post) {
                            return [
                                "id" => $post->id,
                                "caption" => $post->caption,
                                "created_at" => $post->created_at,
                                "deleted_at" => $post->deleted_at,
                                "attachments" => $post->attachments->map(function ($attachment) {
                                    return [
                                        "id" => $attachment->id,
                                        "storage_path" => $attachment->storage_path
                                    ];
                                }),
                            ];
                        });
        }

        return response()->json([
            "id" => $user->id,
            "full_name" => $user->full_name,
            "username" => $user->username,
            "bio" => $user->bio,
            "is_private" => $user->is_private,
            "created_at" => $user->created_at,
            "is_your_account" => $isYourAccount,
            "following_status" => $followingStatus,
            "posts_count" => $postsCount,
            "followers_count" => $followersCount,
            "following_count" => $followingCount,
            "posts" => $posts
        ], 200);
    }
    

}
