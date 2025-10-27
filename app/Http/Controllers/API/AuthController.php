<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /*
     * Register Users.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required|email|unique:users',
            'nowhatsapp'    => 'required',
            'password'      => 'required|min:6',
        ]);

        try {

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
            ]);

            UserProfile::create([
                'user_id'       => $user->id,
                'whatsapp'    => $request->nowhatsapp
            ]);

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            throw $e;
            return response()->json(['message' => 'Failed to create user'], 500);
        }
    }

    /*
     * Login Users.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // generate token sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // update data cart berdasarkan session
        Cart::where('session', $request->cookie('guest_token'))->update([
            'user_id' => $user->id
        ]);

        return response()->json([
            'id' => $user->id,
            'user' => $user->name,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /*
     * Logout Users.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successfully']);
    }

    /*
     * Google Login
     */
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response()->json([
            'url' => $url
        ]);
    }

    /*
     * Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Kalau belum ada, register otomatis
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(date(now())),
                ]);
            }

            // Buat token sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login with Google success',
                'user'    => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token'   => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
