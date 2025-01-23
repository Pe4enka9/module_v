<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function authorization(Request $request): JsonResponse
    {
        $validation = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($validation)) {
            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;
            return response()->json(['success' => true, 'message' => 'Success', 'token' => $token]);
        }

        return response()->json(['success' => false, 'message' => 'Login failed'], 401);
    }

    /**
     * @throws ValidationException
     */
    public function registration(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validated->errors()
            ], 422);
        }

        $data = $validated->validated();

        $user = User::query()->create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'token' => $token,
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();

            return response()->json(['success' => true, 'message' => 'Logout']);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}
