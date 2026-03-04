<?php

require_once __DIR__ . '/Database.php';

class GameController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Генерация случайного выражения с 4 операндами
    public function generateExpression(): string
    {
        $operators = ['+', '-', '*'];
        $parts = [];

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

    // Вычисление результата выражения
    public function calculateExpression(string $expression): int
    {
        $safeExpression = preg_replace('/[^0-9+\-*]/', '', $expression);
        return $this->safeEvaluate($safeExpression);
    }

    // Безопасное вычисление с учётом приоритета операций
    private function safeEvaluate(string $expr): int
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

    // Получение всех игр
    public function getAllGames(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM games
            ORDER BY played_at DESC
        ");
        return $stmt->fetchAll();
    }

    // Получение игры по ID
    public function getGameById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM games WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Создание новой игры
    public function createGame(string $playerName, string $expression): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO games (player_name, expression)
            VALUES (:player_name, :expression)
        ");
        $stmt->execute([
            ':player_name' => $playerName,
            ':expression' => $expression,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Сохранение хода (ответа игрока)
    public function saveStep(int $id, int $playerAnswer, int $correctAnswer): bool
    {
        $isCorrect = ($playerAnswer === $correctAnswer);

        $stmt = $this->pdo->prepare("
            UPDATE games
            SET player_answer = :player_answer,
                correct_answer = :correct_answer,
                is_correct = :is_correct
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':player_answer' => $playerAnswer,
            ':correct_answer' => $correctAnswer,
            ':is_correct' => (int)$isCorrect,
        ]);
    }
}