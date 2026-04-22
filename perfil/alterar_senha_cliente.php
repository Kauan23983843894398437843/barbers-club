<?php
/**
 * Processamento da alteração de senha do cliente
 */

require_once '../includes/auth.php';
require_once '../includes/perfil.php';

// Inicializa as classes
$auth = new Auth();
$perfil = new Perfil();

// Verifica se o usuário está logado como cliente
$auth->verificarCliente();

// Obtém o ID do cliente logado
$id_cliente = $auth->getIdUsuarioLogado();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Valida os dados
    $erros = [];
    
    if (empty($senha_atual)) {
        $erros[] = 'Senha atual é obrigatória';
    }
    
    if (empty($nova_senha)) {
        $erros[] = 'Nova senha é obrigatória';
    } elseif (strlen($nova_senha) < 6) {
        $erros[] = 'A nova senha deve ter pelo menos 6 caracteres';
    }
    
    if ($nova_senha !== $confirmar_senha) {
        $erros[] = 'As senhas não coincidem';
    }
    
    // Se não houver erros, tenta alterar a senha
    if (empty($erros)) {
        // Tenta alterar a senha
        $sucesso = $perfil->alterarSenhaCliente($id_cliente, $senha_atual, $nova_senha);
        
        if ($sucesso) {
            // Alteração bem-sucedida
            $_SESSION['senha_mensagem'] = 'Senha alterada com sucesso!';
            $_SESSION['senha_status'] = 'success';
        } else {
            // Alteração falhou
            $_SESSION['senha_mensagem'] = 'Erro ao alterar a senha. Verifique se a senha atual está correta.';
            $_SESSION['senha_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['senha_erros'] = $erros;
        $_SESSION['senha_status'] = 'error';
    }
    
    // Redireciona de volta para a página de perfil
    header('Location: ../perfil_cliente.php?tab=senha');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de perfil
    header('Location: ../perfil_cliente.php');
    exit;
}
?>