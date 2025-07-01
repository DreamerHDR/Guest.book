<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Параметры сортировки
$allowedSorts = ['username', 'email', 'created_at'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

// Пагинация
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Получение сообщений
$query = "SELECT id, username, email, homepage, message, created_at 
          FROM messages 
          ORDER BY $sort $order 
          LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
$limit = ITEMS_PER_PAGE; 
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Общее количество сообщений
$total = $db->query("SELECT COUNT(*) FROM messages")->fetch_row()[0];
$totalPages = ceil($total / ITEMS_PER_PAGE);
?>

<h1>Гостевая книга</h1>

<a href="add_message.php" class="btn">Добавить сообщение</a>

<table>
    <thead>
        <tr>
            <th onclick="sortTable('username')">
                Имя пользователя 
                <?= $sort === 'username' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
            </th>
            <th onclick="sortTable('email')">
                Email 
                <?= $sort === 'email' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
            </th>
            <th>Домашняя страница</th>
            <th>Сообщение</th>
            <th onclick="sortTable('created_at')">
                Дата 
                <?= $sort === 'created_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($messages)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">Нет сообщений</td>
            </tr>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= sanitize($message['username']) ?></td>
                    <td><?= sanitize($message['email']) ?></td>
                    <td>
                        <?php if (!empty($message['homepage'])): ?>
                            <a href="<?= sanitize($message['homepage']) ?>" target="_blank">Ссылка</a>
                        <?php endif; ?>
                    </td>
                    <td><?= nl2br(sanitize($message['message'])) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?sort=<?= $sort ?>&order=<?= $order ?>&page=1">Первая</a>
            <a href="?sort=<?= $sort ?>&order=<?= $order ?>&page=<?= $page - 1 ?>">Предыдущая</a>
        <?php endif; ?>

        <?php 
        $start = max(1, $page - 2);
        $end = min($start + 4, $totalPages);
        for ($i = $start; $i <= $end; $i++): ?>
            <a href="?sort=<?= $sort ?>&order=<?= $order ?>&page=<?= $i ?>" <?= $i === $page ? 'class="active"' : '' ?>>
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?sort=<?= $sort ?>&order=<?= $order ?>&page=<?= $page + 1 ?>">Следующая</a>
            <a href="?sort=<?= $sort ?>&order=<?= $order ?>&page=<?= $totalPages ?>">Последняя</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function sortTable(column) {
    const url = new URL(window.location.href);
    const sort = url.searchParams.get('sort');
    const order = url.searchParams.get('order');
    
    if (sort === column) {
        url.searchParams.set('order', order === 'asc' ? 'desc' : 'asc');
    } else {
        url.searchParams.set('sort', column);
        url.searchParams.set('order', 'asc');
    }
    
    window.location.href = url.toString();
}
</script>

