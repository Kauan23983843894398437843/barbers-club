<?php
/**
 * Processamento de remoção de barbeiro
 */

require_once '../includes/auth.php';
require_once '../includes/perfil.php';

// Inicializa as classes
$auth = new Auth();
$perfil = new Perfil();

// Verifica se o usuário está logado como barbearia
$auth->verificarBarbearia();

// Obtém o ID da barbearia logada
$id_barbearia = $auth->getIdUsuarioLogado();

// Verifica se o ID do barbeiro foi fornecido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_barbeiro = (int) $_GET['id'];
    
    // Tenta remover o barbeiro
    $sucesso = $perfil->removerBarbeiro($id_barbeiro, $id_barbearia);
    
    if ($sucesso) {
        $_SESSION['barbeiros_mensagem'] = 'Barbeiro removido com sucesso!';
        $_SESSION['barbeiros_status'] = 'success';
    } else {
        $_SESSION['barbeiros_mensagem'] = 'Erro ao remover o barbeiro.';
        $_SESSION['barbeiros_status'] = 'error';
    }
} else {
    $_SESSION['barbeiros_mensagem'] = 'ID do barbeiro não fornecido.';
    $_SESSION['barbeiros_status'] = 'error';
}

// Redireciona de volta para a página de perfil
header('Location: ../perfil_barbearia.php?tab=barbeiros');
exit;
?>