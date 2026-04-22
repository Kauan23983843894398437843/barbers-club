<?php


require_once '../includes/auth.php';
require_once '../includes/avaliacoes.php';
require_once '../includes/agendamentos.php';

$auth = new Auth();
$avaliacoes = new Avaliacoes();
$agendamentos = new Agendamentos();

$auth->verificarCliente();

$id_cliente = $auth->getIdUsuarioLogado();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_agendamento = filter_input(INPUT_POST, 'id_agendamento', FILTER_VALIDATE_INT);
    $nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_FLOAT);
    $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $erros = [];
    
    if (!$id_agendamento) {
        $erros[] = 'Agendamento inválido';
    }
    
    if (!$nota || $nota < 1 || $nota > 5) {
        $erros[] = 'Nota inválida. A nota deve ser entre 1 e 5.';
    }
    
    if (empty($erros)) {
        $agendamento = $agendamentos->getAgendamento($id_agendamento);
        
        if (!$agendamento) {
            $_SESSION['avaliacao_mensagem'] = 'Agendamento não encontrado.';
            $_SESSION['avaliacao_status'] = 'error';
            header('Location: ../agendamentos_cliente.php');
            exit;
        }
        
        if ($agendamento['id_cliente'] != $id_cliente) {
            $_SESSION['avaliacao_mensagem'] = 'Você não tem permissão para avaliar este agendamento.';
            $_SESSION['avaliacao_status'] = 'error';
            header('Location: ../agendamentos_cliente.php');
            exit;
        }
        
        if ($agendamento['status'] !== 'concluido') {
            $_SESSION['avaliacao_mensagem'] = 'Você só pode avaliar agendamentos concluídos.';
            $_SESSION['avaliacao_status'] = 'error';
            header('Location: ../agendamentos_cliente.php');
            exit;
        }
        
        if ($avaliacoes->clienteJaAvaliou($id_agendamento, $id_cliente)) {
            $_SESSION['avaliacao_mensagem'] = 'Você já avaliou este agendamento.';
            $_SESSION['avaliacao_status'] = 'error';
            header('Location: ../agendamentos_cliente.php');
            exit;
        }
        
        $dados = [
            'id_agendamento' => $id_agendamento,
            'id_cliente' => $id_cliente,
            'id_barbearia' => $agendamento['id_barbearia'],
            'id_barbeiro' => $agendamento['id_barbeiro'],
            'nota' => $nota,
            'comentario' => $comentario
        ];
        
        $id_avaliacao = $avaliacoes->adicionarAvaliacao($dados);
        
        if ($id_avaliacao) {
            $_SESSION['avaliacao_mensagem'] = 'Avaliação enviada com sucesso! Obrigado pelo feedback.';
            $_SESSION['avaliacao_status'] = 'success';
        } else {
            $_SESSION['avaliacao_mensagem'] = 'Erro ao enviar a avaliação. Talvez você já tenha avaliado este agendamento.';
            $_SESSION['avaliacao_status'] = 'error';
        }
    } else {
        $_SESSION['avaliacao_erros'] = $erros;
        $_SESSION['avaliacao_status'] = 'error';
        $_SESSION['avaliacao_dados'] = [
            'id_agendamento' => $id_agendamento,
            'nota' => $nota,
            'comentario' => $comentario
        ];
    }
    
    header('Location: ../agendamentos_cliente.php');
    exit;
} else {
    header('Location: ../agendamentos_cliente.php');
    exit;
}
?>