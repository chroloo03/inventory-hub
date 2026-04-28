<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile / account settings page.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Update name and email.
     */
    public function updateInfo(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $request->validateWithBag('updateInfo', [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\pL\s\-\'\.]+$/u', // letters, spaces, hyphens, apostrophes, dots only
            ],
            'email' => [
                'required',
                'email:rfc,dns',             // validates format AND checks DNS records
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ], [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $user->update([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        return back()->with('success', 'Account information updated.');
    }

    /**
     * Update password — rate limited to 5 attempts per minute per user.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // ── Rate limiting ─────────────────────────────────────
        $key = 'update-password:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors(
                ['current_password' => "Too many attempts. Try again in {$seconds} seconds."],
                'updatePassword'
            );
        }

        RateLimiter::hit($key, decaySeconds: 60);

        // ── Validation ────────────────────────────────────────
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()      // requires upper + lowercase
                    ->numbers()        // requires at least one digit
                    ->uncompromised(), // checks against known breach databases
            ],
        ], [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed'                => 'New password confirmation does not match.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Clear the rate limiter on success so the user isn't penalised
        RateLimiter::clear($key);

        return back()->with('success', 'Password updated successfully.');
    }
}
