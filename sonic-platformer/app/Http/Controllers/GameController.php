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
        $user->attempts += 1; 

        if (is_null($user->best_time) || $request->time < $user->best_time) {
            $user->best_time = $request->time;
        }
        
        $user->save();

        return response()->json(['success' => true, 'best_time' => $user->best_time]);
    }

    public function leaderboard(Request $request)
    {
        $sort = $request->query('sort', 'time');
        $query = \App\Models\User::whereNotNull('best_time');

        if ($sort === 'rings') {
            $query->orderBy('total_rings', 'desc');
        } elseif ($sort === 'attempts') {
            $query->orderBy('attempts', 'desc');
        } else {
            $query->orderBy('best_time', 'asc');
        }

        $leaders = $query->take(10)->get();
        
        return view('game.leaderboard', compact('leaders', 'sort'));
    }
}