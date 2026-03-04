<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dbPath = __DIR__ . '/../db/game.db';
        $this->pdo = new PDO("sqlite:{$dbPath}");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Создаём таблицу игр
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_name TEXT NOT NULL,
                expression TEXT NOT NULL,
                player_answer INTEGER,
                correct_answer INTEGER,
                is_correct BOOLEAN,
                played_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}