<?php
	define('DB_HOST', '127.127.126.50');
	define('DB_USER', 'root');
	define('DB_PASS', '');
	define('DB_NAME', 'Practica_GuestBD');

	define('ITEMS_PER_PAGE', 25);
	define('CAPTCHA_LENGTH', 6);
	
	session_start();

	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($db->connect_error){
		die("Ошибка подключения: " . $db->connect_error);
	}
	$db->set_charset("utf8");

	// Функция защиты от XSS
	function sanitize($data) {
			return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
	}
	// Функция проверки латинских символов и цифр
	function validateLatinNumeric($input) {
			return preg_match('/^[a-zA-Z0-9]+$/', $input);
	}
?>