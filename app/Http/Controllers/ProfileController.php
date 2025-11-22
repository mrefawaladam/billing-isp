<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        $user = Auth::user();
        $user->load('roles');
        
        return view('features.profile.index', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Check current password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
            }
        }

        // Update user
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.'
            ]);
        }

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }
}
