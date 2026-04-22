<?php
/**
 * Processamento de remoção de fotos da barbearia (galeria)
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

// Verifica se o ID da foto foi fornecido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_foto = (int) $_GET['id'];
    
    // Tenta remover a foto
    $sucesso = $perfil->removerFotoBarbearia($id_foto, $id_barbearia);
    
    if ($sucesso) {
        $_SESSION['galeria_mensagem'] = 'Foto removida com sucesso!';
        $_SESSION['galeria_status'] = 'success';
    } else {
        $_SESSION['galeria_mensagem'] = 'Erro ao remover a foto.';
        $_SESSION['galeria_status'] = 'error';
    }
} else {
    $_SESSION['galeria_mensagem'] = 'ID da foto não fornecido.';
    $_SESSION['galeria_status'] = 'error';
}

// Redireciona de volta para a página de perfil
header('Location: ../perfil_barbearia.php?tab=galeria');
exit;
?>