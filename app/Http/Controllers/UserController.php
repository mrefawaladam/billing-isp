<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('roles')->select('users.*');

            return DataTables::of($query)
                ->addColumn('roles', function ($user) {
                    return $user->roles->map(function($role) {
                        return '<span class="badge bg-primary me-1">' . $role->name . '</span>';
                    })->join(' ');
                })
                ->addColumn('action', function ($user) {
                    return view('features.users.partials.action-buttons', compact('user'))->render();
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('Y-m-d');
                })
                ->rawColumns(['roles', 'action'])
                ->make(true);
        }

        $roles = Role::all();
        return view('features.users.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return response()->json([
            'html' => view('features.users.partials.form', [
                'roles' => $roles,
                'user' => null,
                'formAction' => route('users.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Create New User'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(UserService::getCreateRules());

            $this->userService->create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully.'
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        $roles = Role::all();
        $permissions = Permission::all();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.users.partials.show', compact('user', 'roles', 'permissions'))->render()
            ]);
        }

        // Redirect to index if not AJAX request
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        return response()->json([
            'html' => view('features.users.partials.form', [
                'roles' => $roles,
                'user' => $user,
                'formAction' => route('users.update', $user),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit User'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate(UserService::getUpdateRules($user));

            $this->userService->update($user, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully.'
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->delete($user);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Assign permission to user.
     */
    public function assignPermission(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $user->syncPermissions($request->permissions);

        return back()->with('success', 'Permissions assigned successfully.');
    }

    /**
     * Remove permission from user.
     */
    public function removePermission(User $user, Permission $permission)
    {
        $user->revokePermissionTo($permission);

        return back()->with('success', 'Permission removed successfully.');
    }
}
