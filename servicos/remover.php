<?php
/**
 * Processamento de remoção de serviço
 */

require_once '../includes/auth.php';
require_once '../includes/servicos.php';

// Inicializa as classes
$auth = new Auth();
$servicos = new Servicos();

// Verifica se o usuário está logado como barbearia
$auth->verificarBarbearia();

// Obtém o ID da barbearia logada
$id_barbearia = $auth->getIdUsuarioLogado();

// Verifica se o ID do serviço foi fornecido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_servico = (int) $_GET['id'];
    
    // Verifica se o serviço pertence à barbearia
    $servico = $servicos->getServico($id_servico, $id_barbearia);
    
    if (!$servico) {
        $_SESSION['servicos_mensagem'] = 'Serviço não encontrado.';
        $_SESSION['servicos_status'] = 'error';
        header('Location: ../servicos_barbearia.php');
        exit;
    }
    
    // Verifica se o serviço tem agendamentos futuros
    if ($servicos->temAgendamentosFuturos($id_servico)) {
        $_SESSION['servicos_mensagem'] = 'Não é possível remover este serviço pois ele possui agendamentos futuros.';
        $_SESSION['servicos_status'] = 'error';
        header('Location: ../servicos_barbearia.php');
        exit;
    }
    
    // Tenta remover o serviço
    $sucesso = $servicos->removerServico($id_servico, $id_barbearia);
    
    if ($sucesso) {
        $_SESSION['servicos_mensagem'] = 'Serviço removido com sucesso!';
        $_SESSION['servicos_status'] = 'success';
    } else {
        $_SESSION['servicos_mensagem'] = 'Erro ao remover o serviço.';
        $_SESSION['servicos_status'] = 'error';
    }
} else {
    $_SESSION['servicos_mensagem'] = 'ID do serviço não fornecido.';
    $_SESSION['servicos_status'] = 'error';
}

// Redireciona de volta para a página de serviços
header('Location: ../servicos_barbearia.php');
exit;
?>