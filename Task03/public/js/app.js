let currentExpression = null;
let currentGameId = null;

// Показать секцию игры
function showGame() {
    document.getElementById('gameSection').classList.remove('hidden');
    document.getElementById('historySection').classList.add('hidden');
    document.getElementById('gameBtn').classList.add('active');
    document.getElementById('historyBtn').classList.remove('active');
    
    // Если нет текущего выражения — загружаем новое
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
    
    // Загрузить историю
    loadHistory();
}

// Загрузить новое выражение (только на клиенте, без сохранения в БД)
function loadNewExpression() {
    currentExpression = generateExpression();
    document.getElementById('expression').textContent = currentExpression;
    document.getElementById('answer').value = '';
    document.getElementById('result').classList.add('hidden');
    
    // Не создаём игру в БД пока игрок не ответил!
}

// Генерация случайного выражения (клиентская, для отображения)
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

// Сохранить игру на сервере
async function saveGameToServer(playerName, expression) {
    try {
        const response = await fetch('/games', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                player_name: playerName,
                expression: expression
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            currentGameId = data.id;
        } else {
            showError('Ошибка при создании игры: ' + (data.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        showError('Ошибка подключения к серверу: ' + error.message);
    }
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
        // СОЗДАЁМ ИГРУ В БД И СРАЗУ СОХРАНЯЕМ ОТВЕТ
        const response = await fetch('/games', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                player_name: playerName,
                expression: currentExpression,
                answer: answer  // Передаём ответ сразу
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Показываем результат
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = data.is_correct 
                ? '✅ Правильно!' 
                : `❌ Неправильно! Правильный ответ: ${data.correct_answer}`;
            resultDiv.className = data.is_correct ? 'result correct' : 'result incorrect';
            resultDiv.classList.remove('hidden');
            
            // Генерируем новое выражение (без сохранения в БД)
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
        const response = await fetch('/games');
        const games = await response.json();
        
        if (response.ok) {
            if (games.length === 0) {
                historyContent.innerHTML = '<p class="empty-history">Пока нет сыгранных игр</p>';
            } else {
                let html = '';
                games.forEach(game => {
                    const statusClass = game.is_correct === '1' || game.is_correct === 1 ? 'correct' : 'incorrect';
                    const statusText = game.is_correct === '1' || game.is_correct === 1 ? '✅ Правильно' : '❌ Неправильно';
                    
                    html += `
                        <div class="history-item ${statusClass}">
                            <h3>${game.player_name}</h3>
                            <p><strong>Выражение:</strong> ${game.expression}</p>
                            <p><strong>Ваш ответ:</strong> ${game.player_answer || '—'}</p>
                            <p><strong>Правильный ответ:</strong> ${game.correct_answer || '—'}</p>
                            <p><strong>Результат:</strong> ${statusText}</p>
                            <p><strong>Дата:</strong> ${game.played_at}</p>
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

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Устанавливаем имя по умолчанию
    const playerNameInput = document.getElementById('player_name');
    if (playerNameInput && !playerNameInput.value) {
        playerNameInput.value = 'Игрок';
    }
    
    // Загружаем новое выражение
    loadNewExpression();
});