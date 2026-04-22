<?php


require_once '../includes/auth.php';
require_once '../includes/agendamentos.php';
require_once '../includes/servicos.php';

$auth = new Auth();
$agendamentos = new Agendamentos();
$servicos = new Servicos();

if (!$auth->estaLogado()) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

if (isset($_GET['id_barbearia']) && isset($_GET['id_barbeiro']) && isset($_GET['id_servico']) && isset($_GET['data'])) {
    $id_barbearia = filter_input(INPUT_GET, 'id_barbearia', FILTER_VALIDATE_INT);
    $id_barbeiro = filter_input(INPUT_GET, 'id_barbeiro', FILTER_VALIDATE_INT);
    $id_servico = filter_input(INPUT_GET, 'id_servico', FILTER_VALIDATE_INT);
    $data = filter_input(INPUT_GET, 'data');
    
    if (!$id_barbearia || !$id_barbeiro || !$id_servico || !$data) {
        header('Content-Type: application/json');
        echo json_encode(['erro' => 'Parâmetros inválidos']);
        exit;
    }
    
    $servico = $servicos->getServico($id_servico, $id_barbearia);
    
    if (!$servico) {
        header('Content-Type: application/json');
        echo json_encode(['erro' => 'Serviço não encontrado']);
        exit;
    }
    
    $horarios = $agendamentos->getHorariosDisponiveis($id_barbearia, $id_barbeiro, $data, $servico['duracao_minutos']);
    
    header('Content-Type: application/json');
    echo json_encode(['horarios' => $horarios]);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Parâmetros não fornecidos']);
    exit;
}
?>