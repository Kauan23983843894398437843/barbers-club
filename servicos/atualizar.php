<?php
/**
 * Processamento de atualização de serviço
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

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém o ID do serviço
    $id_servico = filter_input(INPUT_POST, 'id_servico', FILTER_VALIDATE_INT);
    
    if (!$id_servico) {
        $_SESSION['servicos_mensagem'] = 'ID do serviço inválido.';
        $_SESSION['servicos_status'] = 'error';
        header('Location: ../servicos_barbearia.php');
        exit;
    }
    
    // Verifica se o serviço pertence à barbearia
    $servico = $servicos->getServico($id_servico, $id_barbearia);
    
    if (!$servico) {
        $_SESSION['servicos_mensagem'] = 'Serviço não encontrado.';
        $_SESSION['servicos_status'] = 'error';
        header('Location: ../servicos_barbearia.php');
        exit;
    }
    
    // Obtém os dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
    $duracao_minutos = filter_input(INPUT_POST, 'duracao_minutos', FILTER_VALIDATE_INT);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Valida os dados
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'Nome do serviço é obrigatório';
    }
    
    if (!$id_categoria) {
        $erros[] = 'Categoria inválida';
    }
    
    if (!$preco || $preco <= 0) {
        $erros[] = 'Preço inválido';
    }
    
    if (!$duracao_minutos || $duracao_minutos <= 0) {
        $erros[] = 'Duração inválida';
    }
    
    // Se não houver erros, tenta atualizar o serviço
    if (empty($erros)) {
        // Prepara os dados para atualização
        $dados = [
            'nome' => $nome,
            'id_categoria' => $id_categoria,
            'descricao' => $descricao,
            'preco' => $preco,
            'duracao_minutos' => $duracao_minutos,
            'ativo' => $ativo
        ];
        
        // Tenta atualizar o serviço
        $sucesso = $servicos->atualizarServico($id_servico, $id_barbearia, $dados);
        
        if ($sucesso) {
            $_SESSION['servicos_mensagem'] = 'Serviço atualizado com sucesso!';
            $_SESSION['servicos_status'] = 'success';
        } else {
            $_SESSION['servicos_mensagem'] = 'Erro ao atualizar o serviço.';
            $_SESSION['servicos_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['servicos_erros'] = $erros;
        $_SESSION['servicos_status'] = 'error';
        $_SESSION['servicos_dados'] = [
            'id_servico' => $id_servico,
            'nome' => $nome,
            'id_categoria' => $id_categoria,
            'descricao' => $descricao,
            'preco' => $preco,
            'duracao_minutos' => $duracao_minutos,
            'ativo' => $ativo
        ];
    }
    
    // Redireciona de volta para a página de serviços
    header('Location: ../servicos_barbearia.php');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de serviços
    header('Location: ../servicos_barbearia.php');
    exit;
}
?>