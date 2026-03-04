<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/GameController.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Создаём приложение
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// CORS middleware (для SPA)
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Контроллер игры
$gameController = new GameController();

// Маршрут для обслуживания главной страницы (SPA)
$app->get('/', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/index.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

// ========== REST API маршруты ==========

// GET /games — Получение всех игр
$app->get('/games', function (Request $request, Response $response) use ($gameController) {
    $games = $gameController->getAllGames();
    $payload = json_encode($games, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /games/{id} — Получение игры по ID
$app->get('/games/{id}', function (Request $request, Response $response, $args) use ($gameController) {
    $id = (int)$args['id'];
    $game = $gameController->getGameById($id);

    if (!$game) {
        $response->getBody()->write(json_encode([
            'error' => 'Game not found'
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $payload = json_encode($game, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /games — Создание новой игры С ОТВЕТОМ
$app->post('/games', function (Request $request, Response $response) use ($gameController) {
    $body = $request->getBody()->getContents();
    $data = json_decode($body, true);

    // Проверяем обязательные поля
    if (!isset($data['player_name']) || !isset($data['expression']) || !isset($data['answer'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Missing required fields (player_name, expression, answer)',
            'received' => $data
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $playerName = trim($data['player_name']);
    $expression = trim($data['expression']);
    $playerAnswer = (int)$data['answer'];

    if (empty($playerName)) {
        $response->getBody()->write(json_encode([
            'error' => 'Player name cannot be empty'
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Вычисляем правильный ответ
    $correctAnswer = $gameController->calculateExpression($expression);
    $isCorrect = ($playerAnswer === $correctAnswer);

    // СОЗДАЁМ ИГРУ И СРАЗУ СОХРАНЯЕМ ОТВЕТ
    $gameId = $gameController->createGame($playerName, $expression);
    $gameController->saveStep($gameId, $playerAnswer, $correctAnswer);

    $payload = json_encode([
        'id' => $gameId,
        'player_answer' => $playerAnswer,
        'correct_answer' => $correctAnswer,
        'is_correct' => $isCorrect,
        'message' => 'Game created and step saved successfully'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $response->getBody()->write($payload);
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// POST /step/{id} — Сохранение хода (ответа игрока)
$app->post('/step/{id}', function (Request $request, Response $response, $args) use ($gameController) {
    $id = (int)$args['id'];
    $body = $request->getBody()->getContents();
    $data = json_decode($body, true);

    if (!isset($data['answer'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Missing answer field'
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $playerAnswer = (int)$data['answer'];
    $game = $gameController->getGameById($id);

    if (!$game) {
        $response->getBody()->write(json_encode([
            'error' => 'Game not found'
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $correctAnswer = $gameController->calculateExpression($game['expression']);
    $gameController->saveStep($id, $playerAnswer, $correctAnswer);

    $payload = json_encode([
        'id' => $id,
        'player_answer' => $playerAnswer,
        'correct_answer' => $correctAnswer,
        'is_correct' => ($playerAnswer === $correctAnswer),
        'message' => 'Step saved successfully'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Запуск приложения
$app->run();