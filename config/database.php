<?php

define('DB_HOST', 'localhost');     
define('DB_NAME', 'barbersclub');   
define('DB_USER', 'root');          
define('DB_PASS', '');              

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mude para 1 quando usar HTTPS

// Configurações de erro (remova em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurações de upload
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/barbersclub/uploads/');
define('UPLOAD_URL', '/barbersclub/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
?>