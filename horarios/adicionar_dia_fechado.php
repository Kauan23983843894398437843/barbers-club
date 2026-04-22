<?php
/**
 * Processamento de adição de dia fechado
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
    $data = filter_input(INPUT_POST, 'data');
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Valida os dados
    $erros = [];
    
    if (empty($data)) {
        $erros[] = 'Data é obrigatória';
    } else {
        // Verifica se a data é válida
        $data_obj = DateTime::createFromFormat('Y-m-d', $data);
        if (!$data_obj || $data_obj->format('Y-m-d') !== $data) {
            $erros[] = 'Data inválida';
        } else {
            // Verifica se a data é futura
            $hoje = new DateTime();
            if ($data_obj < $hoje) {
                $erros[] = 'A data deve ser futura';
            }
        }
    }
    
    // Se não houver erros, tenta adicionar o dia fechado
    if (empty($erros)) {
        // Tenta adicionar o dia fechado
        $id_dia_fechado = $horarios->adicionarDiaFechado($id_barbearia, $data, $motivo);
        
        if ($id_dia_fechado) {
            $_SESSION['dias_fechados_mensagem'] = 'Dia fechado adicionado com sucesso!';
            $_SESSION['dias_fechados_status'] = 'success';
        } else {
            $_SESSION['dias_fechados_mensagem'] = 'Erro ao adicionar o dia fechado.';
            $_SESSION['dias_fechados_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['dias_fechados_erros'] = $erros;
        $_SESSION['dias_fechados_status'] = 'error';
        $_SESSION['dias_fechados_dados'] = [
            'data' => $data,
            'motivo' => $motivo
        ];
    }
    
    // Redireciona de volta para a página de horários
    header('Location: ../horarios_barbearia.php?tab=dias_fechados');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de horários
    header('Location: ../horarios_barbearia.php?tab=dias_fechados');
    exit;
}
?>