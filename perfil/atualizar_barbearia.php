<?php
/**
 * Processamento da atualização de perfil da barbearia
 */

require_once '../includes/auth.php';
require_once '../includes/perfil.php';

// Inicializa as classes
$auth = new Auth();
$perfil = new Perfil();

// Verifica se o usuário está logado como barbearia
$auth->verificarBarbearia();

// Obtém o ID da barbearia logada
$id_barbearia = $auth->getIdUsuarioLogado();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $cnpj = filter_input(INPUT_POST, 'cnpj', FILTER_SANITIZE_SPECIAL_CHARS);
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_SPECIAL_CHARS);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
    $cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS);
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_SPECIAL_CHARS);
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS);
    $complemento = filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Valida os dados
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'Nome da barbearia é obrigatório';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido';
    }
    
    if (empty($telefone)) {
        $erros[] = 'Telefone é obrigatório';
    }
    
    if (empty($cep)) {
        $erros[] = 'CEP é obrigatório';
    }
    
    if (empty($estado)) {
        $erros[] = 'Estado é obrigatório';
    }
    
    if (empty($cidade)) {
        $erros[] = 'Cidade é obrigatória';
    }
    
    if (empty($endereco)) {
        $erros[] = 'Endereço é obrigatório';
    }
    
    // Se não houver erros, tenta atualizar o perfil
    if (empty($erros)) {
        // Prepara os dados para atualização
        $dados = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'cnpj' => $cnpj,
            'cep' => $cep,
            'estado' => $estado,
            'cidade' => $cidade,
            'bairro' => $bairro,
            'endereco' => $endereco,
            'complemento' => $complemento,
            'descricao' => $descricao
        ];
        
        // Tenta atualizar o perfil
        $sucesso = $perfil->atualizarPerfilBarbearia($id_barbearia, $dados);
        
        if ($sucesso) {
            // Atualização bem-sucedida
            $_SESSION['perfil_mensagem'] = 'Perfil atualizado com sucesso!';
            $_SESSION['perfil_status'] = 'success';
        } else {
            // Atualização falhou
            $_SESSION['perfil_mensagem'] = 'Erro ao atualizar o perfil. Este email já está em uso.';
            $_SESSION['perfil_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['perfil_erros'] = $erros;
        $_SESSION['perfil_status'] = 'error';
    }
    
    // Redireciona de volta para a página de perfil
    header('Location: ../perfil_barbearia.php');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de perfil
    header('Location: ../perfil_barbearia.php');
    exit;
}
?>