<?php

namespace Tenis\Calculator;

use function cli\line;
use function cli\prompt;

class View
{
    public static function showWelcome(): void
    {
        line('Добро пожаловать в игру "Калькулятор"!');
        line('Вычислите результат арифметического выражения');
        line('Для выхода введите "q"');
        line('');
    }

    public static function askAnswer(string $expression): string
    {
        line("Вопрос: {$expression}");
        return prompt('Ваш ответ: ');
    }

    public static function showResult(bool $isCorrect, int $correctAnswer): void
    {
        if ($isCorrect) {
            line('✅ Правильно!');
        } else {
            line("❌ Неправильно! Правильный ответ: {$correctAnswer}");
        }
        line('');
    }

    public static function showStatistics(int $correctCount, int $totalCount): void
    {
        line("Статистика: {$correctCount}/{$totalCount} правильных ответов");
        line('');
    }
}
