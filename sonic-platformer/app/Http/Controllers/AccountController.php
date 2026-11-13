<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|string',
            'color' => 'required|string',
        ]);

        $user = Auth::user();
        $user->avatar = $request->avatar;
        $user->color = $request->color;
        $user->save();

        return back()->with('success', 'Профиль обновлен!');
    }

    public function deleteRecord()
    {
        $user = Auth::user();
        $user->best_time = null;
        $user->total_rings = 0;
        $user->save();

        return back()->with('success', 'Ваш рекорд удален.');
    }
}