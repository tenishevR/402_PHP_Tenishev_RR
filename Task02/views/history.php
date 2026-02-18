<?php if (empty($history)): ?>
    <div class="empty">
        <p>Пока нет сыгранных игр. Сыграйте в <a href="/">Калькулятор</a>!</p>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Игрок</th>
                <th>Выражение</th>
                <th>Ваш ответ</th>
                <th>Правильный ответ</th>
                <th>Результат</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $record): ?>
                <tr>
                    <td><?= htmlspecialchars($record['played_at']) ?></td>
                    <td><?= htmlspecialchars($record['player_name']) ?></td>
                    <td><?= htmlspecialchars($record['expression']) ?></td>
                    <td><?= htmlspecialchars($record['player_answer']) ?></td>
                    <td><?= htmlspecialchars($record['correct_answer']) ?></td>
                    <td class="<?= $record['is_correct'] ? 'correct' : 'incorrect' ?>">
                        <?= $record['is_correct'] ? '✅ Правильно' : '❌ Неправильно' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
