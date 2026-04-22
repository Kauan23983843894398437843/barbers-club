<?php
/**
 * Processamento de remoção de dia fechado
 */

require_once '../includes/auth.php';
require_once '../includes/horarios.php';

// Inicializa as classes
$auth = new Auth();
$horarios = new Horarios();

// Verifica se o usuário está logado como barbearia
$auth->verificarBarbearia();

// Obtém o ID da barbearia logada
$id_barbearia = $auth->getIdUsuarioLogado();

// Verifica se o ID do dia fechado foi fornecido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_dia_fechado = (int) $_GET['id'];
    
    // Tenta remover o dia fechado
    $sucesso = $horarios->removerDiaFechado($id_dia_fechado, $id_barbearia);
    
    if ($sucesso) {
        $_SESSION['dias_fechados_mensagem'] = 'Dia fechado removido com sucesso!';
        $_SESSION['dias_fechados_status'] = 'success';
    } else {
        $_SESSION['dias_fechados_mensagem'] = 'Erro ao remover o dia fechado.';
        $_SESSION['dias_fechados_status'] = 'error';
    }
} else {
    $_SESSION['dias_fechados_mensagem'] = 'ID do dia fechado não fornecido.';
    $_SESSION['dias_fechados_status'] = 'error';
}

header('Location: ../horarios_barbearia.php?tab=dias_fechados');
exit;
?>