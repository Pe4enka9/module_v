<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function authorization(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(["success" => false, "message" => "Login failed"], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(["success" => true, "message" => "Success", "token" => $token]);
    }

    /**
     * @throws ValidationException
     */
    public function registration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "errors" => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $user = User::query()->create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(["success" => true, "message" => "Success", "token" => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(["success" => true, "message" => "Logout"]);
    }

    public function files(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|max:2048|mimes:doc,pdf,docx,zip,jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "errors" => $validator->errors()], 422);
        }

        $user = $request->user();
        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $existingFile = UserFile::query()->where('user_id', $user->id)
                ->where('name', 'LIKE', "$originalName%.$extension")
                ->count();

            $newName = $existingFile > 0 ? "$originalName ($existingFile).$extension" : "$originalName.$extension";
            $filePath = $file->storeAs('uploads', $newName);
            $fileId = Str::random(10);

            if ($filePath) {
                UserFile::query()->create([
                    'user_id' => $user->id,
                    'file_id' => $fileId,
                    'name' => $newName,
                    'path' => $filePath,
                ]);

                $uploadedFiles[] = [
                    'success' => true,
                    'message' => 'Success',
                    'name' => $newName,
                    'url' => url("/files/$fileId"),
                    'file_id' => $fileId,
                ];
            } else {
                $uploadedFiles[] = [
                    'success' => false,
                    'message' => 'File not loaded',
                    'name' => $file->getClientOriginalName(),
                ];
            }
        }

        return response()->json($uploadedFiles);
    }
}
