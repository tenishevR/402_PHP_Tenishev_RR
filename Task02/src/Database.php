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
        
        // Создаём таблицу при первом запуске
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_name TEXT NOT NULL,
                expression TEXT NOT NULL,
                player_answer INTEGER NOT NULL,
                correct_answer INTEGER NOT NULL,
                is_correct BOOLEAN NOT NULL,
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
