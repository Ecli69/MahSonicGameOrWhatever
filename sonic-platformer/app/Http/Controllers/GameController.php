<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return view('game.play');
    }

    public function saveScore(Request $request)
    {
        $request->validate([
            'time' => 'required|numeric',
            'rings' => 'required|integer',
        ]);

        $user = auth()->user();
        $user->total_rings += $request->rings;

        if (is_null($user->best_time) || $request->time < $user->best_time) {
            $user->best_time = $request->time;
        }
        
        $user->save();

        return response()->json([
            'success' => true, 
            'best_time' => $user->best_time,
        ]);
    }

    public function leaderboard()
    {
    $leaders = \App\Models\User::whereNotNull('best_time')
        ->orderBy('best_time', 'asc')
        ->take(10)
        ->get();
    return view('game.leaderboard', compact('leaders'));
    }
}