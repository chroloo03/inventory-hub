<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    /**
     * List all staff accounts.
     */
    public function index(): View
    {
        $staff = User::where('role', 'staff')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('staff.index', compact('staff'));
    }

    /**
     * Show create staff form.
     */
    public function create(): View
    {
        return view('staff.form');
    }

    /**
     * Store a new staff account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'email'    => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
        ]);

        User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'     => 'staff',
        ]);

        return redirect()->route('staff.index')
            ->with('success', "Staff account for \"{$request->input('name')}\" created successfully.");
    }

    /**
     * Show edit form for a staff account.
     */
    public function edit(User $staff): View
    {
        // Prevent editing admin accounts through this controller
        abort_if($staff->isAdmin(), 403, 'Cannot edit admin accounts here.');

        return view('staff.form', compact('staff'));
    }

    /**
     * Update a staff account.
     */
    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_if($staff->isAdmin(), 403);

        $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email,' . $staff->id],
        ], [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
        ]);

        $staff->update([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)->mixedCase()->numbers()],
            ]);

            $staff->update([
                'password' => Hash::make($request->input('password')),
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', "Staff account \"{$staff->name}\" updated.");
    }

    /**
     * Delete a staff account.
     */
    public function destroy(User $staff): RedirectResponse
    {
        abort_if($staff->isAdmin(), 403);
        abort_if($staff->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', "Staff account \"{$name}\" deleted.");
    }
}
