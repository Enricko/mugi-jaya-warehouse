<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and issue a Sanctum token.
     *
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact your administrator.'],
            ]);
        }

        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'unknown');

        // Define abilities based on role
        $abilities = $this->getAbilitiesForRole($user->role);

        $token = $user->createToken($deviceName, $abilities);

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token->plainTextToken,
                'abilities' => $abilities,
            ],
        ]);
    }

    /**
     * Logout — revoke the current token.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Logout from all devices — revoke all tokens.
     *
     * POST /api/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Register a new user (Owner or Kepala Gudang only).
     *
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $authUser = $request->user();

        // Only owner and kepala_gudang can register users
        if (! in_array($authUser->role, ['owner', 'kepala_gudang'])) {
            return response()->json([
                'message' => 'You do not have permission to register users.',
            ], 403);
        }

        // Determine which roles this user can create
        $allowedRoles = $this->getAllowedRegistrationRoles($authUser->role);

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'created_by' => $authUser->id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_by' => $user->created_by,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    /**
     * Change password for the authenticated user.
     *
     * PUT /api/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password_hash' => Hash::make($request->password),
        ]);

        // Revoke all other tokens for security
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Get token abilities based on user role.
     */
    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'owner' => ['*'],
            'kepala_gudang' => [
                'warehouse:*',
                'material:*',
                'project:*',
                'supplier:*',
                'purchase-order:*',
                'shipment:*',
                'transaction:*',
                'report:view',
                'user:register',
                'dashboard:operational',
            ],
            'mandor' => [
                'warehouse:view',
                'material:view',
                'material:update-stock',
                'project:view',
                'transaction:create',
                'transaction:view',
                'shipment:view',
            ],
            'driver' => [
                'shipment:view',
                'shipment:update-status',
                'shipment:upload-proof',
                'shipment:update-gps',
            ],
            'engineering' => [
                'project:view',
                'material:view',
                'transaction:create',
                'transaction:view',
            ],
            default => [],
        };
    }

    /**
     * Get roles that a given role is allowed to register.
     */
    private function getAllowedRegistrationRoles(string $role): array
    {
        return match ($role) {
            'owner' => ['kepala_gudang', 'mandor', 'driver', 'engineering'],
            'kepala_gudang' => ['mandor', 'driver', 'engineering'],
            default => [],
        };
    }
}
