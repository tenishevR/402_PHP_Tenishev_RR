<?php if ($resultMessage): ?>
    <div class="result <?= $isCorrect === true ? 'correct' : ($isCorrect === false ? 'incorrect' : 'warning') ?>">
        <?= htmlspecialchars($resultMessage) ?>
    </div>
<?php endif; ?>

<div class="game-box">
    <div class="expression"><?= htmlspecialchars($expression) ?></div>
    
    <form method="POST" action="/">
        <input type="hidden" name="expression" value="<?= htmlspecialchars($expression) ?>">
        
        <div class="form-group">
            <label for="player_name">Ваше имя:</label>
            <input type="text" id="player_name" name="player_name" 
                   value="<?= htmlspecialchars($playerName ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="answer">Ваш ответ:</label>
            <input type="text" id="answer" name="answer" required>
        </div>
        
        <button type="submit">Проверить ответ</button>
    </form>
</div>
