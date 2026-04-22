<?php


require_once '../includes/auth.php';
require_once '../includes/agendamentos.php';
require_once '../includes/servicos.php';
require_once '../includes/perfil.php';

$auth = new Auth();
$agendamentos = new Agendamentos();
$servicos = new Servicos();
$perfil = new Perfil();

$auth->verificarCliente();

$id_cliente = $auth->getIdUsuarioLogado();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barbearia = filter_input(INPUT_POST, 'id_barbearia', FILTER_VALIDATE_INT);
    $id_barbeiro = filter_input(INPUT_POST, 'id_barbeiro', FILTER_VALIDATE_INT);
    $id_servico = filter_input(INPUT_POST, 'id_servico', FILTER_VALIDATE_INT);
    $data = filter_input(INPUT_POST, 'data');
    $hora_inicio = filter_input(INPUT_POST, 'hora_inicio');
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $erros = [];
    
    if (!$id_barbearia) {
        $erros[] = 'Barbearia inválida';
    }
    
    if (!$id_barbeiro) {
        $erros[] = 'Barbeiro inválido';
    }
    
    if (!$id_servico) {
        $erros[] = 'Serviço inválido';
    }
    
    if (empty($data)) {
        $erros[] = 'Data é obrigatória';
    } else {
        $data_obj = DateTime::createFromFormat('Y-m-d', $data);
        if (!$data_obj || $data_obj->format('Y-m-d') !== $data) {
            $erros[] = 'Data inválida';
        } else {
            $hoje = new DateTime();
            if ($data_obj < $hoje) {
                $erros[] = 'A data deve ser futura';
            }
        }
    }
    
    if (empty($hora_inicio)) {
        $erros[] = 'Horário é obrigatório';
    }
    
    if (empty($erros)) {
        $servico = $servicos->getServico($id_servico, $id_barbearia);
        
        if (!$servico) {
            $_SESSION['agendamento_mensagem'] = 'Serviço não encontrado.';
            $_SESSION['agendamento_status'] = 'error';
            header('Location: ../agendar.php?id_barbearia=' . $id_barbearia);
            exit;
        }
        
        $hora_fim = $agendamentos->calcularHoraFim($hora_inicio, $servico['duracao_minutos']);
        
        if (!$agendamentos->verificarDisponibilidade($id_barbearia, $id_barbeiro, $data, $hora_inicio, $hora_fim)) {
            $_SESSION['agendamento_mensagem'] = 'Horário não disponível. Por favor, escolha outro horário.';
            $_SESSION['agendamento_status'] = 'error';
            header('Location: ../agendar.php?id_barbearia=' . $id_barbearia);
            exit;
        }
        
        $dados = [
            'id_cliente' => $id_cliente,
            'id_barbearia' => $id_barbearia,
            'id_barbeiro' => $id_barbeiro,
            'id_servico' => $id_servico,
            'data_agendamento' => $data,
            'hora_inicio' => $hora_inicio,
            'hora_fim' => $hora_fim,
            'status' => 'agendado',
            'observacoes' => $observacoes,
            'data_criacao' => date('Y-m-d H:i:s')
        ];
        
        $id_agendamento = $agendamentos->criarAgendamento($dados);
        
        if ($id_agendamento) {
            $_SESSION['agendamento_mensagem'] = 'Agendamento realizado com sucesso!';
            $_SESSION['agendamento_status'] = 'success';
            header('Location: ../agendamentos_cliente.php');
            exit;
        } else {
            $_SESSION['agendamento_mensagem'] = 'Erro ao realizar o agendamento. Por favor, tente novamente.';
            $_SESSION['agendamento_status'] = 'error';
            header('Location: ../agendar.php?id_barbearia=' . $id_barbearia);
            exit;
        }
    } else {
        $_SESSION['agendamento_erros'] = $erros;
        $_SESSION['agendamento_status'] = 'error';
        $_SESSION['agendamento_dados'] = [
            'id_barbearia' => $id_barbearia,
            'id_barbeiro' => $id_barbeiro,
            'id_servico' => $id_servico,
            'data' => $data,
            'hora_inicio' => $hora_inicio,
            'observacoes' => $observacoes
        ];
        
        header('Location: ../agendar.php?id_barbearia=' . $id_barbearia);
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>