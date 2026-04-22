<?php
/**
 * Processamento de upload de fotos da barbearia (galeria)
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
    // Obtém a descrição da foto
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Verifica se um arquivo foi enviado
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['galeria_mensagem'] = 'Nenhum arquivo enviado ou erro no upload.';
        $_SESSION['galeria_status'] = 'error';
        
        // Redireciona de volta para a página de perfil
        header('Location: ../perfil_barbearia.php?tab=galeria');
        exit;
    }
    
    // Define o diretório de destino e o prefixo do arquivo
    $diretorio = 'barbearias/galeria/';
    $prefixo = 'barbearia_' . $id_barbearia . '_';
    
    // Processa o upload da imagem
    $url_foto = $perfil->uploadImagem($_FILES['foto'], $diretorio, $prefixo);
    
    if ($url_foto) {
        // Upload bem-sucedido, adiciona a foto à galeria
        $id_foto = $perfil->adicionarFotoBarbearia($id_barbearia, $url_foto, $descricao);
        
        if ($id_foto) {
            $_SESSION['galeria_mensagem'] = 'Foto adicionada com sucesso!';
            $_SESSION['galeria_status'] = 'success';
        } else {
            $_SESSION['galeria_mensagem'] = 'Erro ao adicionar a foto à galeria.';
            $_SESSION['galeria_status'] = 'error';
        }
    } else {
        // Upload falhou
        $_SESSION['galeria_mensagem'] = 'Erro ao fazer upload da foto. Verifique se o formato é válido (JPG, PNG ou GIF) e se o tamanho é menor que 5MB.';
        $_SESSION['galeria_status'] = 'error';
    }
    
    // Redireciona de volta para a página de perfil
    header('Location: ../perfil_barbearia.php?tab=galeria');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de perfil
    header('Location: ../perfil_barbearia.php?tab=galeria');
    exit;
}
?>