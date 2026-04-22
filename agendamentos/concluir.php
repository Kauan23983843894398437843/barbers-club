<?php

require_once '../includes/auth.php';
require_once '../includes/agendamentos.php';

$auth = new Auth();
$agendamentos = new Agendamentos();

$auth->verificarBarbearia();

$id_barbearia = $auth->getIdUsuarioLogado();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_agendamento = (int) $_GET['id'];
    
    $sucesso = $agendamentos->atualizarStatus($id_agendamento, 'concluido', $id_barbearia, 'barbearia');
    
    if ($sucesso) {
        $_SESSION['agendamentos_mensagem'] = 'Agendamento concluído com sucesso!';
        $_SESSION['agendamentos_status'] = 'success';
    } else {
        $_SESSION['agendamentos_mensagem'] = 'Erro ao concluir o agendamento.';
        $_SESSION['agendamentos_status'] = 'error';
    }
} else {
    $_SESSION['agendamentos_mensagem'] = 'ID do agendamento não fornecido.';
    $_SESSION['agendamentos_status'] = 'error';
}

header('Location: ../agendamentos_barbearia.php');
exit;
?>