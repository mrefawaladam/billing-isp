<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign roles if provided
        if (isset($data['roles']) && is_array($data['roles']) && count($data['roles']) > 0) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->syncRoles($roles);
        }

        return $user;
    }

    /**
     * Update user
     */
    public function update(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        // Update roles
        if (isset($data['roles'])) {
            if (is_array($data['roles']) && count($data['roles']) > 0) {
                $roles = Role::whereIn('id', $data['roles'])->get();
                $user->syncRoles($roles);
            } else {
                $user->roles()->detach();
            }
        }

        return $user->fresh();
    }

    /**
     * Delete user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => 'nullable|array',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(User $user): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => 'nullable|array',
        ];
    }
}

