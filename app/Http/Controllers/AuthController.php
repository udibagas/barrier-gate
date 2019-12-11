<?php

namespace App\Http\Controllers;

use App\Notifications\UserAccess;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('status', 1)
            ->where('role', '!=', 0)
            ->where(function($q) use ($request) {
                return $q->where('name', $request->email)
                    ->orWhere('email', $request->email);
            })->first();

        if ($user && password_verify($request->password, $user->password)) {

            if ($this->systemUser) {
                $this->systemUser->notify(new UserAccess($user, 'User ' . $user->name . ' telah login'));
            }

            return response()->json([
                'success' => true,
                'token' => auth('api')->login($user),
                'user' => auth('api')->user()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate($request->token);

            if ($this->systemUser) {
                $this->systemUser->notify(new UserAccess($request->user(), 'User ' . $request->user()->name . ' telah logout'));
            }

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

    public function getAuthUser(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}
