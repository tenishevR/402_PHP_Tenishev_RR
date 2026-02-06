<?php

namespace Tenis\Calculator;

use function cli\line;

class Controller
{
    public static function startGame(): void
    {
        View::showWelcome();

        $correctCount = 0;
        $questionCount = 0;

        while (true) {
            $expression = self::generateExpression();
            $correctAnswer = self::calculateExpression($expression);

            $answer = View::askAnswer($expression);

            // Проверка на выход
            if (strtolower(trim($answer)) === 'q') {
                break;
            }

            // Проверка ответа
            $isCorrect = (int)trim($answer) === $correctAnswer;
            if ($isCorrect) {
                $correctCount++;
            }

            View::showResult($isCorrect, $correctAnswer);
            $questionCount++;
        }

        // Итоговая статистика
        if ($questionCount > 0) {
            View::showStatistics($correctCount, $questionCount);
        }

        line('Спасибо за игру!');
    }

    private static function generateExpression(): string
    {
        $operators = ['+', '-', '*'];
        $parts = [];

        // Генерируем 4 операнда и 3 оператора
        for ($i = 0; $i < 4; $i++) {
            $operand = random_int(1, 50);
            $parts[] = $operand;

            if ($i < 3) {
                $operatorIndex = random_int(0, 2);
                $parts[] = $operators[$operatorIndex];
            }
        }

        return implode('', $parts);
    }

    private static function calculateExpression(string $expression): int
    {
        // Безопасная очистка выражения
        $safeExpression = preg_replace('/[^0-9+\-*]/', '', $expression);

        // Вычисление с учётом приоритета операций
        return self::safeEvaluate($safeExpression);
    }

    private static function safeEvaluate(string $expr): int
    {
    // Разбираем выражение на токены: числа и операторы
        preg_match_all('/\d+|[\+\-\*]/', $expr, $matches);
        $tokens = $matches[0];

    // Шаг 1: обрабатываем умножение (сохраняем значения и операторы +/–)
        $values = [(int)$tokens[0]];
        $operators = [];

        for ($i = 1; $i < count($tokens); $i += 2) {
            $operator = $tokens[$i];
            $number = (int)$tokens[$i + 1];

            if ($operator === '*') {
                // Умножаем последнее значение в стеке
                $lastIndex = count($values) - 1;
                $values[$lastIndex] = $values[$lastIndex] * $number;
            } else {
                // Сохраняем оператор +/– и следующее число
                $operators[] = $operator;
                $values[] = $number;
            }
        }

    // Шаг 2: обрабатываем сложение и вычитание слева направо
        $result = $values[0];
        for ($i = 0; $i < count($operators); $i++) {
            if ($operators[$i] === '+') {
                $result += $values[$i + 1];
            } else { // '-'
                $result -= $values[$i + 1];
            }
        }

        return $result;
    }
}
