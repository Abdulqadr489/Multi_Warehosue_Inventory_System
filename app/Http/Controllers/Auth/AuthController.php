<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Repositories\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            return $this->success($user, 'User registered successfully.', 201);
        } catch (\Throwable $e) {
            Log::error('Register error', ['error' => $e->getMessage()]);
            return $this->error('Failed to register user.', 500, $e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = auth('api')->attempt($credentials);
        if(!$token){
            return $this->error('Invalid credentials', 401);
        }

        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ], 'User logged in successfully.');
    }
}
