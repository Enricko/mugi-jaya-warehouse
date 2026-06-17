<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canManage = in_array($user->role, ['owner', 'kepala_gudang']);

        return view('settings.index', [
            'user' => $user,
            'canManage' => $canManage,
            'users' => $canManage ? User::with('creator')->orderByRaw("FIELD(role,'owner','kepala_gudang','mandor','driver','engineering')")->get() : collect(),
            'allowedRoles' => $this->allowedRoles($user->role),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (! Hash::check($request->current_password, $user->password_hash)) {
            throw ValidationException::withMessages(['current_password' => 'Password saat ini salah.']);
        }

        $user->update(['password_hash' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $allowed = $this->allowedRoles($request->user()->role);

        $data = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in($allowed)],
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'password_hash' => Hash::make($data['password']),
            'created_by' => $request->user()->id,
            'is_active' => true,
        ]);

        return back()->with('success', "Akun {$data['full_name']} berhasil dibuat.");
    }

    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun diperbarui.');
    }

    private function allowedRoles(string $role): array
    {
        return match ($role) {
            'owner' => ['kepala_gudang', 'mandor', 'driver', 'engineering'],
            'kepala_gudang' => ['mandor', 'driver', 'engineering'],
            default => [],
        };
    }
}
