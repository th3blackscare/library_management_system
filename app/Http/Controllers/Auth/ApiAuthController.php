<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    protected function getUserDataWithToken(User $user)
    {
        $token = $user->createToken('Bosta Password Grant Client');
        $user['token'] = $token->accessToken;
        return response($user,200);
    }
    public function Login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required',
        ]);

        if($validation->fails()) return response()->json(['message' => implode($validation->errors()->all())], 400);

        $user = User::where('email', "=", $request->email)->first();

        if(!$user) return response()->json(['message' => 'User not found'], 404);

        if(!Hash::check($request->password, $user->password)) return response()->json(['message' => 'Wrong password'], 401);

        return $this->getUserDataWithToken($user);

    }

    public function Register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required',
        ]);

        if($validation->fails()) return response()->json(['message' => implode($validation->errors()->all())], 400);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=> Hash::make($request->password)
        ]);

        return $this->getUserDataWithToken($user);

    }
}
