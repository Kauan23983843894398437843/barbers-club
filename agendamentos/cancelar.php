<?php


require_once '../includes/auth.php';
require_once '../includes/agendamentos.php';

$auth = new Auth();
$agendamentos = new Agendamentos();

if (!$auth->estaLogado()) {
    header('Location: ../login.php');
    exit;
}

$tipo_usuario = $auth->ehCliente() ? 'cliente' : 'barbearia';
$id_usuario = $auth->getIdUsuarioLogado();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_agendamento = (int) $_GET['id'];
    
    $status = $tipo_usuario === 'cliente' ? 'cancelado_cliente' : 'cancelado_barbearia';
    
    $sucesso = $agendamentos->atualizarStatus($id_agendamento, $status, $id_usuario, $tipo_usuario);
    
    if ($sucesso) {
        $_SESSION['agendamentos_mensagem'] = 'Agendamento cancelado com sucesso!';
        $_SESSION['agendamentos_status'] = 'success';
    } else {
        $_SESSION['agendamentos_mensagem'] = 'Erro ao cancelar o agendamento. Verifique se você tem permissão para cancelar este agendamento ou se o prazo para cancelamento já expirou.';
        $_SESSION['agendamentos_status'] = 'error';
    }
} else {
    $_SESSION['agendamentos_mensagem'] = 'ID do agendamento não fornecido.';
    $_SESSION['agendamentos_status'] = 'error';
}

if ($tipo_usuario === 'cliente') {
    header('Location: ../agendamentos_cliente.php');
} else {
    header('Location: ../agendamentos_barbearia.php');
}
exit;
?>