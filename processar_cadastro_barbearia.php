<?php
/**
 * Processamento do cadastro de barbearias
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
    $nome = filter_input(INPUT_POST, 'shop-name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $cnpj = filter_input(INPUT_POST, 'cnpj', FILTER_SANITIZE_SPECIAL_CHARS);
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_SPECIAL_CHARS);
    $estado = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
    $cidade = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
    $endereco = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['password'] ?? '';
    $confirmarSenha = $_POST['confirm-password'] ?? '';
    $termos = isset($_POST['terms']);
    $descricao = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_SPECIAL_CHARS);
    
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
    
    // Processa o upload da logo, se houver
    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOAD_DIR . 'barbearias/';
        
        // Cria o diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Obtém a extensão do arquivo
        $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        // Verifica se a extensão é permitida
        if (!in_array($extensao, ALLOWED_EXTENSIONS)) {
            $erros[] = 'Formato de imagem não permitido. Use JPG, PNG ou GIF.';
        } else {
            // Gera um nome único para o arquivo
            $nomeArquivo = uniqid() . '.' . $extensao;
            $caminhoArquivo = $uploadDir . $nomeArquivo;
            
            // Move o arquivo para o diretório de uploads
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $caminhoArquivo)) {
                $logo = 'uploads/barbearias/' . $nomeArquivo;
            } else {
                $erros[] = 'Erro ao fazer upload da logo';
            }
        }
    }
    
    // Se não houver erros, tenta registrar a barbearia
    if (empty($erros)) {
        // Prepara os dados para inserção
        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'cnpj' => $cnpj,
            'cep' => $cep,
            'estado' => $estado,
            'cidade' => $cidade,
            'endereco' => $endereco,
            'descricao' => $descricao,
            'logo' => $logo,
            'data_cadastro' => date('Y-m-d H:i:s'),
            'ativo' => 1
        ];
        
        // Tenta registrar a barbearia
        $id_barbearia = $auth->registrarBarbearia($dados);
        
        if ($id_barbearia) {
            // Cadastro bem-sucedido, faz login automaticamente
            $auth->loginBarbearia($email, $senha);
            
            // Redireciona para a página da barbearia
            header('Location: index_barbearia.html');
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
            'email' => $email,
            'telefone' => $telefone,
            'cnpj' => $cnpj,
            'cep' => $cep,
            'estado' => $estado,
            'cidade' => $cidade,
            'endereco' => $endereco,
            'descricao' => $descricao
        ];
        
        // Redireciona de volta para o formulário
        header('Location: cadastro_cliente.html?tipo=barbearia');
        exit;
    }
} else {
    // Se não for uma requisição POST, redireciona para a página de cadastro
    header('Location: cadastro_cliente.html?tipo=barbearia');
    exit;
}
?>