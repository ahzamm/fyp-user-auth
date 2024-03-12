<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function register(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('mytoken')->plainTextToken;

            return response([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(),], 500);
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
            return response()->json(['message' => 'User not found'], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid Credentials']);
        }
        $token = $user->createToken('mytoken')->plainTextToken;

        return response([
            'user' => $user, 'token' => $token
        ]);
    }

    public function profile(Request $request)
    {
        $user =  $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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
            return response()->json(['message' => 'wrong old password']);
        }
        $request->user()->tokens()->delete();

        $user->password = Hash::make($request->new_password);
        $user->save();

        $token = $user->createToken('mytoken')->plainTextToken;

        return response()->json(['message' => 'Password changed successfully', 'token' => $token], 200);
    }
}
