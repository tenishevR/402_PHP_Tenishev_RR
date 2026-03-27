<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GameController extends Controller
{
    // GET /api/games — Все игры
    public function index()
    {
        return response()->json(Game::orderBy('created_at', 'desc')->get());
    }

    // GET /api/games/{id} — Игра по ID
    public function show($id)
    {
        $game = Game::find($id);
        
        if (!$game) {
            return response()->json(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }
        
        return response()->json($game);
    }

    // POST /api/games — Создание игры С ОТВЕТОМ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:255',
            'expression' => 'required|string',
            'answer' => 'required|integer',
        ]);

        $correctAnswer = Game::calculateExpression($validated['expression']);
        $isCorrect = ((int)$validated['answer'] === $correctAnswer);

        $game = Game::create([
            'player_name' => $validated['player_name'],
            'expression' => $validated['expression'],
            'player_answer' => $validated['answer'],
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect,
        ]);

        return response()->json([
            'id' => $game->id,
            'player_answer' => $game->player_answer,
            'correct_answer' => $game->correct_answer,
            'is_correct' => $game->is_correct,
            'message' => 'Game created successfully',
        ], Response::HTTP_CREATED);
    }
}