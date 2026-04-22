<?php
/**
 * Processamento do cadastro de clientes
 */

require_once 'includes/auth.php';

// Inicializa a classe de autenticação
$auth = new Auth();

// Verifica se o usuário já está logado
if ($auth->estaLogado()) {
    // Redireciona para a página apropriada
    if ($auth->ehCliente()) {
        header('Location: index_cliente.html');
    } else {
        header('Location: index_barbearia.html');
    }
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $nome = filter_input(INPUT_POST, 'first-name', FILTER_SANITIZE_SPECIAL_CHARS);
    $sobrenome = filter_input(INPUT_POST, 'last-name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['password'] ?? '';
    $confirmarSenha = $_POST['confirm-password'] ?? '';
    $termos = isset($_POST['terms']);
    
    // Valida os dados
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($sobrenome)) {
        $erros[] = 'Sobrenome é obrigatório';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido';
    }
    
    if (empty($telefone)) {
        $erros[] = 'Telefone é obrigatório';
    }
    
    if (empty($senha)) {
        $erros[] = 'Senha é obrigatória';
    } elseif (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter pelo menos 6 caracteres';
    }
    
    if ($senha !== $confirmarSenha) {
        $erros[] = 'As senhas não coincidem';
    }
    
    if (!$termos) {
        $erros[] = 'Você deve aceitar os termos de serviço';
    }
    
    // Se não houver erros, tenta registrar o cliente
    if (empty($erros)) {
        // Prepara os dados para inserção
        $dados = [
            'nome' => $nome,
            'sobrenome' => $sobrenome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'ativo' => 1
        ];
        
        // Tenta registrar o cliente
        $id_cliente = $auth->registrarCliente($dados);
        
        if ($id_cliente) {
            // Cadastro bem-sucedido, faz login automaticamente
            $auth->loginCliente($email, $senha);
            
            // Redireciona para a página do cliente
            header('Location: index_cliente.html');
            exit;
        } else {
            // Cadastro falhou
            $erros[] = 'Este email já está em uso';
        }
    }
    
    // Se chegou aqui, houve erros
    if (!empty($erros)) {
        // Armazena os erros na sessão
        session_start();
        $_SESSION['cadastro_erros'] = $erros;
        $_SESSION['cadastro_dados'] = [
            'nome' => $nome,
            'sobrenome' => $sobrenome,
            'email' => $email,
            'telefone' => $telefone
        ];
        
        // Redireciona de volta para o formulário
        header('Location: cadastro_cliente.html');
        exit;
    }
} else {
    // Se não for uma requisição POST, redireciona para a página de cadastro
    header('Location: cadastro_cliente.html');
    exit;
}
?>