<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Empresa;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    private function guard(): StatefulGuard
    {
        return Auth::guard('empresa');
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Empresa|null $empresa */
        $empresa = Empresa::where('email', $data['email'])->first();

        if ($empresa && Hash::check($data['password'], $empresa->senha)) {
            $this->guard()->login($empresa, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos. Tente novamente.',
        ])->withInput($request->except('password'));
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $empresa = Empresa::create([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'cnpj' => $data['cnpj'],
            'senha' => Hash::make($data['password']),
        ]);

        $this->guard()->login($empresa);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
