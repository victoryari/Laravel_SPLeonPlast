<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $this->ensureIsNotRateLimited($request);

        $credentials = [
            'nombre_usuario' => $request->input('nombre_usuario'),
            'password' => $request->input('password'),
        ];

        if (Auth::guard('web')->attempt(array_merge($credentials, ['activo' => 1]), $request->filled('remember'))) {
            RateLimiter::clear($this->throttleKey($request));

            $request->session()->regenerate();
            
            $user = Auth::user();
            $user->ultimo_login = Carbon::now();
            $user->save();

            return $this->authenticated($request, $user);
        }

        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'nombre_usuario' => ['Las credenciales son incorrectas o la cuenta está inactiva.'],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // Redirección profesional basada en nombres de rutas
        if ($user->rol === 'Administrador') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->rol === 'Supervisor') {
            return redirect()->route('supervisor.dashboard');
        } elseif ($user->rol === 'Especialista') {
            return redirect()->route('especialista.dashboard');
        } elseif ($user->rol === 'ALMACEN') {
            return redirect()->route('almacen.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'nombre_usuario' => ['Demasiados intentos. Intente en ' . ceil($seconds / 60) . ' minutos.'],
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input('nombre_usuario')).'|'.$request->ip());
    }
}