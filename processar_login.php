<?php
/**
 * Processamento do formulário de login
 */

require_once 'includes/auth.php';

// Inicializa a classe de autenticação
$auth = new Auth();

// Verifica se o usuário já está logado
if ($auth->estaLogado()) {
    // Redireciona para a página apropriada
    if ($auth->ehCliente()) {
        header('Location: index_cliente.html');
    } else {
        header('Location: index_barbearia.html');
    }
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['password'] ?? '';
    $tipo = $_POST['tipo_usuario'] ?? 'cliente';
    
    // Valida os dados
    $erros = [];
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido';
    }
    
    if (empty($senha)) {
        $erros[] = 'Senha é obrigatória';
    }
    
    // Se não houver erros, tenta fazer login
    if (empty($erros)) {
        $logado = false;
        
        if ($tipo === 'cliente') {
            $logado = $auth->loginCliente($email, $senha);
            $redirect = 'index_cliente.html';
        } else {
            $logado = $auth->loginBarbearia($email, $senha);
            $redirect = 'index_barbearia.html';
        }
        
        if ($logado) {
            // Login bem-sucedido, redireciona
            header("Location: $redirect");
            exit;
        } else {
            // Login falhou
            $erros[] = 'Email ou senha incorretos';
        }
    }
    
    // Se chegou aqui, houve erros
    if (!empty($erros)) {
        // Armazena os erros na sessão
        session_start();
        $_SESSION['login_erros'] = $erros;
        $_SESSION['login_email'] = $email;
        $_SESSION['login_tipo'] = $tipo;
        
        // Redireciona de volta para o formulário
        header('Location: login.html');
        exit;
    }
} else {
    // Se não for uma requisição POST, redireciona para a página de login
    header('Location: login.html');
    exit;
}
?>