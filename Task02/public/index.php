<?php
require_once __DIR__ . '/../src/Game.php';

$game = new Game();
$expression = $game->generateExpression();
$playerName = '';
$playerAnswer = '';
$correctAnswer = null;
$resultMessage = '';
$isCorrect = null;

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerName = trim($_POST['player_name'] ?? '');
    $expression = trim($_POST['expression'] ?? '');
    $playerAnswer = trim($_POST['answer'] ?? '');
    
    if ($playerName && $expression && $playerAnswer !== '') {
        $correctAnswer = $game->calculateExpression($expression);
        $isCorrect = ((int)$playerAnswer === $correctAnswer);
        
        // Сохраняем в БД
        $game->saveGame($playerName, $expression, (int)$playerAnswer, $correctAnswer);
        
        // Генерируем новое выражение для следующего раунда
        $expression = $game->generateExpression();
        
        $resultMessage = $isCorrect 
            ? '✅ Правильно!' 
            : "❌ Неправильно! Правильный ответ: {$correctAnswer}";
    } else {
        $resultMessage = '⚠️ Заполните все поля!';
        $isCorrect = null;
    }
}

// Рендеринг шаблона
$title = 'Калькулятор — Игра';
$currentPage = 'game';
ob_start();
require __DIR__ . '/../views/game.php';
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';
