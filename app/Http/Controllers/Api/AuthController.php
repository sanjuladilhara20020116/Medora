<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate a hospital system user and issue a JWT.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->toString();
        $password = $request->string('password')->toString();

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $user = User::query()
            ->with('role')
            ->where($field, $login)
            ->first();

        /*
         * Return the same response for an unknown user and
         * an incorrect password. This avoids revealing whether
         * a particular account exists.
         */
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username/email or password.',
            ], 401);
        }

        /*
         * Prevent disabled accounts from accessing the HMS.
         */
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive. Please contact the system administrator.',
            ], 403);
        }

        /*
         * Prevent login if the assigned role has been disabled.
         */
        if (!$user->role || !$user->role->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account does not have an active system role.',
            ], 403);
        }

        $credentials = [
            $field => $login,
            'password' => $password,
            'is_active' => true,
        ];

        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username/email or password.',
            ], 401);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',

            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,

                    'role' => [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                        'slug' => $user->role->slug,
                    ],

                    'last_login_at' => $user->last_login_at,
                ],

                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                ],
            ],
        ]);
    }
}