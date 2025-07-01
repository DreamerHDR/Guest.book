<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'homepage' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных формы
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'homepage' => trim($_POST['homepage'] ?? ''),
        'message' => trim($_POST['message'] ?? '')
    ];
    $captcha = trim($_POST['captcha'] ?? '');

    // Валидация
    if (empty($formData['username'])) {
        $errors['username'] = 'Имя пользователя обязательно';
    } elseif (!validateLatinNumeric($formData['username'])) {
        $errors['username'] = 'Только латинские буквы и цифры';
    }

    if (empty($formData['email'])) {
        $errors['email'] = 'Email обязателен';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Неверный формат email';
    }

    if (!empty($formData['homepage']) && !filter_var($formData['homepage'], FILTER_VALIDATE_URL)) {
        $errors['homepage'] = 'Неверный формат URL';
    }

    if (empty($formData['message'])) {
        $errors['message'] = 'Сообщение обязательно';
    }

    if (empty($captcha)) {
        $errors['captcha'] = 'Введите CAPTCHA';
    } elseif (!isset($_SESSION['captcha']) || $_SESSION['captcha'] !== $captcha) {
        $errors['captcha'] = 'Неверная CAPTCHA';
    }

    if (empty($errors)) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
				$query = "INSERT INTO messages (username, email, homepage, message, ip_address, user_agent, created_at) 
									VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssssss", 
            $formData['username'],
            $formData['email'],
            $formData['homepage'],
            $formData['message'],
            $ip,
            $userAgent
        );
        
					if ($stmt->execute()) {
							header("Location: index.php");
							exit();
					} else {
							$errors['db'] = 'Ошибка при сохранении: ' . $stmt->error;
					}
    }
}
?>

<h1>Добавить сообщение</h1>

<?php if (isset($errors['db'])): ?>
    <div class="error"><?= sanitize($errors['db']) ?></div>
<?php endif; ?>

<form method="post" action="add_message.php">
    <div class="form-group">
        <label for="username" class="required">Имя пользователя:</label>
        <input type="text" id="username" name="username" 
               value="<?= sanitize($formData['username']) ?>">
        <?php if (isset($errors['username'])): ?>
            <div class="error"><?= sanitize($errors['username']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email" class="required">Email:</label>
        <input type="email" id="email" name="email" 
               value="<?= sanitize($formData['email']) ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="error"><?= sanitize($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="homepage">Домашняя страница:</label>
        <input type="text" id="homepage" name="homepage" 
               value="<?= sanitize($formData['homepage']) ?>">
        <?php if (isset($errors['homepage'])): ?>
            <div class="error"><?= sanitize($errors['homepage']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="message" class="required">Сообщение:</label>
        <textarea id="message" name="message"><?= sanitize($formData['message']) ?></textarea>
        <?php if (isset($errors['message'])): ?>
            <div class="error"><?= sanitize($errors['message']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="captcha" class="required">CAPTCHA:</label>
        <div class="captcha-container">
            <input type="text" id="captcha" name="captcha">
            <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
        </div>
        <?php if (isset($errors['captcha'])): ?>
            <div class="error"><?= sanitize($errors['captcha']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn">Отправить</button>
    <a href="index.php" class="btn">Отмена</a>
</form>