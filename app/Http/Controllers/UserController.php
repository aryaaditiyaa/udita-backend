<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    null
                ],
                    'Login failed, wrong email or password', Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Successful');

        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'error' => $exception
            ], 'Authentication Failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|min:8|confirmed'
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'guest',
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            return ResponseFormatter::success([
                'user' => $user
            ], 'Register Successful');

        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'error' => $exception
            ], 'Register Failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return ResponseFormatter::success([
            'user' => $user
        ], 'Fetch successful');
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:13',
            'email' => 'sometimes|string|email|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            'profile_photo_path' => 'sometimes|image'
        ]);

        if ($request->file('profile_photo_path')) {
            $profile_photo_path = Storage::putFile(
                'public/users/' . date('FY'),
                $request->file('profile_photo_path')
            );
        }

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'profile_photo_path' => $request->file('profile_photo_path') ? substr($profile_photo_path, 7) : $user->profile_photo_path,
        ]);

        return ResponseFormatter::success([
            'user' => $user
        ], 'Update user successful');
    }

}
