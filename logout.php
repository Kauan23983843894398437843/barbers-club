<?php
/**
 * Script para encerrar a sessão
 */

require_once 'includes/auth.php';

// Inicializa a classe de autenticação
$auth = new Auth();

// Encerra a sessão
$auth->logout();

// Redireciona para a página inicial
header('Location: index.html');
exit;
?>