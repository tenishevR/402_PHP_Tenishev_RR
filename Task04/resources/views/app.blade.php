<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">  {{-- ← Добавь эту строку --}}
    <title>Калькулятор — Laravel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        .nav-btn {
            background: #f0f0f0;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .nav-btn:hover { background: #e0e0e0; }
        .nav-btn.active { background: #667eea; color: white; }
        .game-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .expression {
            font-size: 2rem;
            font-weight: bold;
            color: #444;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .form-group { margin: 15px 0; text-align: left; width: 100%; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1.2rem;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .result {
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.3rem;
            font-weight: bold;
            text-align: center;
        }
        .result.correct { background: #d4edda; color: #155724; }
        .result.incorrect { background: #f8d7da; color: #721c24; }
        .result.error { background: #f8d7da; color: #721c24; }
        .hidden { display: none; }
        #historySection { padding: 20px 0; }
        .history-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        .history-item.correct { border-left-color: #28a745; }
        .history-item.incorrect { border-left-color: #dc3545; }
        .empty-history {
            text-align: center;
            padding: 40px;
            color: #777;
            font-style: italic;
            font-size: 1.2rem;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #667eea;
            font-size: 1.2rem;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🎮 Калькулятор</h1>
            <nav>
                <button onclick="showGame()" class="nav-btn active" id="gameBtn">Играть</button>
                <button onclick="showHistory()" class="nav-btn" id="historyBtn">История</button>
            </nav>
        </header>

        <main id="content">
            <!-- Игра -->
            <div id="gameSection">
                <div class="game-box">
                    <div class="expression" id="expression">Загрузка...</div>
                    <div id="result" class="result hidden"></div>
                    <form id="gameForm">
                        <div class="form-group">
                            <label for="player_name">Ваше имя:</label>
                            <input type="text" id="player_name" name="player_name" value="Игрок" required>
                        </div>
                        <div class="form-group">
                            <label for="answer">Ваш ответ:</label>
                            <input type="number" id="answer" name="answer" required>
                        </div>
                        <button type="submit">Проверить ответ</button>
                    </form>
                </div>
            </div>

            <!-- История -->
            <div id="historySection" class="hidden">
                <h2>📊 История игр</h2>
                <div id="historyContent">
                    <p class="loading">Загрузка истории...</p>
                </div>
            </div>
        </main>

        <footer>
            <p>Лабораторная работа 4 &copy; {{ date('Y') }} Roman Tenishev</p>
        </footer>
    </div>

    <script>
        let currentExpression = null;

        // Генерация случайного выражения (клиентская)
        function generateExpression() {
            const operators = ['+', '-', '*'];
            let parts = [];
            
            for (let i = 0; i < 4; i++) {
                const operand = Math.floor(Math.random() * 50) + 1;
                parts.push(operand);
                
                if (i < 3) {
                    const operatorIndex = Math.floor(Math.random() * 3);
                    parts.push(operators[operatorIndex]);
                }
            }
            
            return parts.join('');
        }

        // Показать секцию игры
        function showGame() {
            document.getElementById('gameSection').classList.remove('hidden');
            document.getElementById('historySection').classList.add('hidden');
            document.getElementById('gameBtn').classList.add('active');
            document.getElementById('historyBtn').classList.remove('active');
            
            if (!currentExpression) {
                loadNewExpression();
            }
        }

        // Показать секцию истории
        function showHistory() {
            document.getElementById('gameSection').classList.add('hidden');
            document.getElementById('historySection').classList.remove('hidden');
            document.getElementById('gameBtn').classList.remove('active');
            document.getElementById('historyBtn').classList.add('active');
            
            loadHistory();
        }

        // Загрузить новое выражение
        function loadNewExpression() {
            currentExpression = generateExpression();
            document.getElementById('expression').textContent = currentExpression;
            document.getElementById('answer').value = '';
            document.getElementById('result').classList.add('hidden');
        }

        // Отправить ответ на сервер
        document.getElementById('gameForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const playerName = document.getElementById('player_name').value.trim() || 'Игрок';
            const answer = parseInt(document.getElementById('answer').value);
            
            if (isNaN(answer)) {
                showError('Введите корректное число!');
                return;
            }
            
            if (!currentExpression) {
                showError('Ошибка: выражение не загружено');
                return;
            }
            
            try {
                const response = await fetch('/api/games', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        player_name: playerName,
                        expression: currentExpression,
                        answer: answer
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    const resultDiv = document.getElementById('result');
                    resultDiv.textContent = data.is_correct 
                        ? '✅ Правильно!' 
                        : `❌ Неправильно! Правильный ответ: ${data.correct_answer}`;
                    resultDiv.className = data.is_correct ? 'result correct' : 'result incorrect';
                    resultDiv.classList.remove('hidden');
                    
                    setTimeout(() => {
                        loadNewExpression();
                    }, 1500);
                } else {
                    showError('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                }
            } catch (error) {
                showError('Ошибка подключения к серверу: ' + error.message);
            }
        });

        // Загрузить историю игр
        async function loadHistory() {
            const historyContent = document.getElementById('historyContent');
            historyContent.innerHTML = '<p class="loading">Загрузка истории...</p>';
            
            try {
                const response = await fetch('/api/games');
                const games = await response.json();
                
                if (response.ok) {
                    if (games.length === 0) {
                        historyContent.innerHTML = '<p class="empty-history">Пока нет сыгранных игр</p>';
                    } else {
                        let html = '';
                        games.forEach(game => {
                            const statusClass = game.is_correct ? 'correct' : 'incorrect';
                            const statusText = game.is_correct ? '✅ Правильно' : '❌ Неправильно';
                            
                            html += `
                                <div class="history-item ${statusClass}">
                                    <h3>${game.player_name}</h3>
                                    <p><strong>Выражение:</strong> ${game.expression}</p>
                                    <p><strong>Ваш ответ:</strong> ${game.player_answer || '—'}</p>
                                    <p><strong>Правильный ответ:</strong> ${game.correct_answer || '—'}</p>
                                    <p><strong>Результат:</strong> ${statusText}</p>
                                    <p><strong>Дата:</strong> ${new Date(game.created_at).toLocaleString()}</p>
                                </div>
                            `;
                        });
                        historyContent.innerHTML = html;
                    }
                } else {
                    historyContent.innerHTML = '<p class="error">Ошибка при загрузке истории</p>';
                }
            } catch (error) {
                historyContent.innerHTML = `<p class="error">Ошибка подключения: ${error.message}</p>`;
            }
        }

        // Показать ошибку
        function showError(message) {
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = message;
            resultDiv.className = 'result error';
            resultDiv.classList.remove('hidden');
        }

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            loadNewExpression();
        });
    </script>
</body>
</html>