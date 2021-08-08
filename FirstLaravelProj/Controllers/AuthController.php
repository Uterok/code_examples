<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated_data = $request->validate([
            'firstname' => 'nullable|max:55',
            'lastname' => 'nullable|max:55',
            'email' => 'email|required|unique:users,email',
            'password' => 'required|confirmed',
            'registration_flow' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! User::checkRegistrationFlow($value)) {
                        $fail('Wrong registration flow.');
                    }
                },
            ]
        ]);

        $validated_data['password'] = bcrypt($request->password);

        $user = User::create($validated_data);
        $user->assignRegistrationFlow($validated_data['registration_flow'] ?? null, true);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([ 'user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request): JsonResponse
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required',
        ]);

        if (! auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json(['user' => auth()->user(), 'access_token' => $accessToken]);
    }

    public function checkToken(Request $request): JsonResponse
    {
        $user = auth()->user();
        $token = $request->bearerToken();

        return response()->json(['user' => $user, 'access_token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => 1,
        ]);
    }
}
