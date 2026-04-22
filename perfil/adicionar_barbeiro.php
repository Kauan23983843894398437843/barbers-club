<?php
/**
 * Processamento de adição de barbeiro
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
    $sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $anos_experiencia = filter_input(INPUT_POST, 'anos_experiencia', FILTER_VALIDATE_INT);
    $especialidade = filter_input(INPUT_POST, 'especialidade', FILTER_SANITIZE_SPECIAL_CHARS);
    $biografia = filter_input(INPUT_POST, 'biografia', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Valida os dados
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($sobrenome)) {
        $erros[] = 'Sobrenome é obrigatório';
    }
    
    // Se não houver erros, tenta adicionar o barbeiro
    if (empty($erros)) {
        // Processa o upload da foto, se houver
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $diretorio = 'barbeiros/';
            $prefixo = 'barbeiro_';
            
            $foto = $perfil->uploadImagem($_FILES['foto'], $diretorio, $prefixo);
            
            if (!$foto) {
                $erros[] = 'Erro ao fazer upload da foto. Verifique se o formato é válido (JPG, PNG ou GIF) e se o tamanho é menor que 5MB.';
            }
        }
        
        if (empty($erros)) {
            // Prepara os dados para inserção
            $dados = [
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'email' => $email,
                'telefone' => $telefone,
                'foto' => $foto,
                'anos_experiencia' => $anos_experiencia,
                'especialidade' => $especialidade,
                'biografia' => $biografia,
                'ativo' => 1
            ];
            
            // Tenta adicionar o barbeiro
            $id_barbeiro = $perfil->adicionarBarbeiro($id_barbearia, $dados);
            
            if ($id_barbeiro) {
                $_SESSION['barbeiros_mensagem'] = 'Barbeiro adicionado com sucesso!';
                $_SESSION['barbeiros_status'] = 'success';
            } else {
                $_SESSION['barbeiros_mensagem'] = 'Erro ao adicionar o barbeiro.';
                $_SESSION['barbeiros_status'] = 'error';
            }
        } else {
            // Se houver erros, armazena na sessão
            $_SESSION['barbeiros_erros'] = $erros;
            $_SESSION['barbeiros_status'] = 'error';
        }
    } else {
        // Se houver erros, armazena na sessão
        $_SESSION['barbeiros_erros'] = $erros;
        $_SESSION['barbeiros_status'] = 'error';
    }
    
    // Redireciona de volta para a página de perfil
    header('Location: ../perfil_barbearia.php?tab=barbeiros');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de perfil
    header('Location: ../perfil_barbearia.php?tab=barbeiros');
    exit;
}
?>