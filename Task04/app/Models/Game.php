<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_name',
        'expression',
        'player_answer',
        'correct_answer',
        'is_correct',
    ];

    // Безопасное вычисление выражения
    public static function calculateExpression(string $expression): int
    {
        $safeExpression = preg_replace('/[^0-9+\-*]/', '', $expression);
        return self::safeEvaluate($safeExpression);
    }

    private static function safeEvaluate(string $expr): int
    {
        preg_match_all('/\d+|[\+\-\*]/', $expr, $matches);
        $tokens = $matches[0];

        $values = [(int)$tokens[0]];
        $operators = [];

        for ($i = 1; $i < count($tokens); $i += 2) {
            $operator = $tokens[$i];
            $number = (int)$tokens[$i + 1];

            if ($operator === '*') {
                $lastIndex = count($values) - 1;
                $values[$lastIndex] = $values[$lastIndex] * $number;
            } else {
                $operators[] = $operator;
                $values[] = $number;
            }
        }

        $result = $values[0];
        for ($i = 0; $i < count($operators); $i++) {
            if ($operators[$i] === '+') {
                $result += $values[$i + 1];
            } else {
                $result -= $values[$i + 1];
            }
        }

        return $result;
    }
}