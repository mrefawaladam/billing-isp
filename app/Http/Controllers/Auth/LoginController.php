<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     * With throttle protection (max 5 attempts per minute).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Throttle login attempts (max 5 attempts per minute)
        $maxAttempts = 5;
        $decayMinutes = 1;

        if (method_exists($this, 'hasTooManyLoginAttempts')) {
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }
        } else {
            // Simple throttle check
            $key = $this->throttleKey($request);
            $attempts = cache()->get($key, 0);

            if ($attempts >= $maxAttempts) {
                $seconds = cache()->get($key . ':timer', $decayMinutes * 60);
                throw ValidationException::withMessages([
                    'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
                ]);
            }
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Clear throttle on successful login
            cache()->forget($this->throttleKey($request));
            cache()->forget($this->throttleKey($request) . ':timer');

            return redirect()->intended(route('dashboard'));
        }

        // Increment throttle on failed attempt
        $key = $this->throttleKey($request);
        $attempts = cache()->get($key, 0) + 1;
        cache()->put($key, $attempts, now()->addMinutes($decayMinutes));
        cache()->put($key . ':timer', $decayMinutes * 60, now()->addMinutes($decayMinutes));

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'login.' . $request->ip() . '.' . $request->email;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
