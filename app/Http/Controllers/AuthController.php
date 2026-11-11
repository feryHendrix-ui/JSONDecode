<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:128',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email atau kata sandi tidak sesuai.'], 401);
        }

        $token = $user->createToken('katalog-browser',
            $user->is_admin ? ['books:write'] : [], now()->addHours(2));

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer', 'expires_in' => 7200,
            'user' => ['name' => $user->name, 'email' => $user->email, 'is_admin' => $user->is_admin],
        ])->header('Cache-Control', 'no-store');
    }

    public function me(Request $request)
    {
        return response()->json(['data' => [
            'name' => $request->user()->name, 'email' => $request->user()->email,
            'is_admin' => $request->user()->is_admin,
        ]])->header('Cache-Control', 'no-store');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Anda sudah keluar.']);
    }
}
