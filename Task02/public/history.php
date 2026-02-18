<?php
require_once __DIR__ . '/../src/Game.php';

$game = new Game();
$history = $game->getGameHistory(50);

// Рендеринг шаблона
$title = 'История игр — Калькулятор';
$currentPage = 'history';
ob_start();
require __DIR__ . '/../views/history.php';
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';
