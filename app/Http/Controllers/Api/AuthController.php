<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    /**
     * User login and generate token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create a token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [  // Return only essential user data
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * User logout (Invalidate the token)
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }

    /**
     * Get authenticated user details
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Refresh the token (if using refresh token logic)
     * This is optional and depends on your setup.
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Invalidate old tokens
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
        ]);
    }
}
