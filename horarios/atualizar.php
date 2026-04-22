<?php
/**
 * Processamento de atualização de horários de funcionamento
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

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $dia_semana = filter_input(INPUT_POST, 'dia_semana', FILTER_VALIDATE_INT);
    $hora_abertura = filter_input(INPUT_POST, 'hora_abertura');
    $hora_fechamento = filter_input(INPUT_POST, 'hora_fechamento');
    $intervalo_agendamento = filter_input(INPUT_POST, 'intervalo_agendamento', FILTER_VALIDATE_INT);
    $acao = filter_input(INPUT_POST, 'acao');
    
    // Valida os dados
    $erros = [];
    
    if ($dia_semana === false || $dia_semana < 0 || $dia_semana > 6) {
        $erros[] = 'Dia da semana inválido';
    }
    
    if ($acao === 'adicionar' || $acao === 'atualizar') {
        if (empty($hora_abertura)) {
            $erros[] = 'Hora de abertura é obrigatória';
        }
        
        if (empty($hora_fechamento)) {
            $erros[] = 'Hora de fechamento é obrigatória';
        }
        
        if ($hora_abertura >= $hora_fechamento) {
            $erros[] = 'A hora de abertura deve ser anterior à hora de fechamento';
        }
        
        if (!$intervalo_agendamento || $intervalo_agendamento <= 0) {
            $erros[] = 'Intervalo de agendamento inválido';
        }
    }
    
    // Se não houver erros, processa a ação
    if (empty($erros)) {
        $sucesso = false;
        
        if ($acao === 'adicionar' || $acao === 'atualizar') {
            // Adiciona ou atualiza o horário
            $sucesso = $horarios->definirHorarioDia(
                $id_barbearia,
                $dia_semana,
                $hora_abertura,
                $hora_fechamento,
                $intervalo_agendamento
            );
            
            if ($sucesso) {
                $_SESSION['horarios_mensagem'] = 'Horário ' . ($acao === 'adicionar' ? 'adicionado' : 'atualizado') . ' com sucesso!';
                $_SESSION['horarios_status'] = 'success';
            } else {
                $_SESSION['horarios_mensagem'] = 'Erro ao ' . ($acao === 'adicionar' ? 'adicionar' : 'atualizar') . ' o horário.';
                $_SESSION['horarios_status'] = 'error';
            }
        } elseif ($acao === 'remover') {
            // Remove o horário
            $sucesso = $horarios->removerHorarioDia($id_barbearia, $dia_semana);
            
            if ($sucesso) {
                $_SESSION['horarios_mensagem'] = 'Horário removido com sucesso!';
                $_SESSION['horarios_status'] = 'success';
            } else {
                $_SESSION['horarios_mensagem'] = 'Erro ao remover o horário.';
                $_SESSION['horarios_status'] = 'error';
            }
        } else {
            $_SESSION['horarios_mensagem'] = 'Ação inválida.';
            $_SESSION['horarios_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['horarios_erros'] = $erros;
        $_SESSION['horarios_status'] = 'error';
        $_SESSION['horarios_dados'] = [
            'dia_semana' => $dia_semana,
            'hora_abertura' => $hora_abertura,
            'hora_fechamento' => $hora_fechamento,
            'intervalo_agendamento' => $intervalo_agendamento
        ];
    }
    
    // Redireciona de volta para a página de horários
    header('Location: ../horarios_barbearia.php');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de horários
    header('Location: ../horarios_barbearia.php');
    exit;
}
?>