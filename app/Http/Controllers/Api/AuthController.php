<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = (string) $request->validated('identifier');
        $password = rtrim((string) $request->validated('password'));
        $deviceName = $request->validated('device_name') ?: 'flutter';

        $user = User::findByLoginIdentifier($identifier);

        if (! $user || ! Hash::check($password, $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        if ($user->status !== UserStatus::Active) {
            return ApiResponse::error('Your account is not active. Contact your church administrator.', 403);
        }

        if (! $user->isChurchPortalUser()) {
            return ApiResponse::error('This login is for church members and staff.', 403);
        }

        $church = $user->church;

        if (! $church) {
            return ApiResponse::error('Your account is not linked to a church.', 403);
        }

        if ($church->status->value === 'suspended') {
            return ApiResponse::error('This church has been suspended. Contact platform support.', 403);
        }

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;
        $user->update(['last_login_at' => now()]);
        $user->load(['roles', 'permissions', 'church:id,name,slug,status']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return ApiResponse::success(message: 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions', 'church:id,name,slug,status']);

        return ApiResponse::success((new UserResource($user))->resolve());
    }
}
