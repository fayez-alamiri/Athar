<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the sign-in view.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && ! empty($user->locale)) {
            $request->session()->put('locale', $user->locale);
        }

        return redirect()->intended(route('home'))
            ->with('status', __('Welcome back! You are now signed in.'));
    }

    /**
     * Display the registration view.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $activeLocale = $request->session()->get('locale', config('brand.default_locale', 'ar'));

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'], // Hashed via User model cast
            'locale' => in_array($activeLocale, ['ar', 'en'], true) ? $activeLocale : 'ar',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('status', __('Your account has been created successfully.'));
    }

    /**
     * Destroy an authenticated session (CSRF-protected POST).
     */
    public function logout(Request $request): RedirectResponse
    {
        $currentLocale = $request->session()->get('locale', app()->getLocale());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $currentLocale);

        return redirect()->route('home')
            ->with('status', __('You have been signed out.'));
    }
}