<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{


    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
                'avatar' => 'required'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'avatar' => $request->avatar
            ]);

            $token = $user->createToken('mytoken')->plainTextToken;

            return response([
                'success' => true,
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(),], 500);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid Credentials']);
        }
        $token = $user->createToken('mytoken')->plainTextToken;

        return response([
            'success' => true, 'user' => $user, 'token' => $token
        ]);
    }


    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }


    public function logoutFromAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function profile(Request $request)
    {
        $user =  $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar
            ]
        ]);
    }

    public function changePassword(Request $request)
    {

        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'wrong old password']);
        }
        $request->user()->tokens()->delete();

        $user->password = Hash::make($request->new_password);
        $user->save();

        $token = $user->createToken('mytoken')->plainTextToken;

        return response()->json(['success' => true, 'message' => 'Password changed successfully', 'token' => $token], 200);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function editUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'avatar' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->name = $request->name;
        $user->avatar = $request->avatar;
        $user->save();

        $token = $user->createToken('mytoken')->plainTextToken;

        return response([
            'success' => true,
            'user' => $user,
            'token' => $token
        ], 200);
    }
}
