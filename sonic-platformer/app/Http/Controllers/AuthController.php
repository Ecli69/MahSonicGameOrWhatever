<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }

    public function register(Request $request) {
        $request->validate([
            'login' => 'required|unique:users|max:255',
            'password' => 'required|min:4|confirmed',
        ]);

        $user = User::create([
            'login' => $request->login,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        return redirect()->route('game.index');
    }

    public function showLogin() { return view('auth.login'); }

    public function login(Request $request) {
        $credentials = $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('game.index');
        }

        return back()->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}