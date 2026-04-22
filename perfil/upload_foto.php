<?php
/**
 * Processamento de upload de fotos de perfil
 */

require_once '../includes/auth.php';
require_once '../includes/perfil.php';

// Inicializa as classes
$auth = new Auth();
$perfil = new Perfil();

// Verifica se o usuário está logado
if (!$auth->estaLogado()) {
    header('Location: ../login.php');
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém o tipo de usuário e o ID
    $tipo_usuario = $auth->ehCliente() ? 'cliente' : 'barbearia';
    $id_usuario = $auth->getIdUsuarioLogado();
    
    // Verifica se um arquivo foi enviado
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['foto_mensagem'] = 'Nenhum arquivo enviado ou erro no upload.';
        $_SESSION['foto_status'] = 'error';
        
        // Redireciona de volta para a página de perfil
        header('Location: ../perfil_' . $tipo_usuario . '.php');
        exit;
    }
    
    // Define o diretório de destino e o prefixo do arquivo
    $diretorio = $tipo_usuario . 's/';
    $prefixo = $tipo_usuario . '_' . $id_usuario . '_';
    
    // Processa o upload da imagem
    $url_foto = $perfil->uploadImagem($_FILES['foto'], $diretorio, $prefixo);
    
    if ($url_foto) {
        // Upload bem-sucedido, atualiza o perfil
        $campo = $tipo_usuario === 'cliente' ? 'foto_perfil' : 'logo';
        $dados = [$campo => $url_foto];
        
        if ($tipo_usuario === 'cliente') {
            $sucesso = $perfil->atualizarPerfilCliente($id_usuario, $dados);
        } else {
            $sucesso = $perfil->atualizarPerfilBarbearia($id_usuario, $dados);
        }
        
        if ($sucesso) {
            $_SESSION['foto_mensagem'] = 'Foto atualizada com sucesso!';
            $_SESSION['foto_status'] = 'success';
        } else {
            $_SESSION['foto_mensagem'] = 'Erro ao atualizar a foto no perfil.';
            $_SESSION['foto_status'] = 'error';
        }
    } else {
        // Upload falhou
        $_SESSION['foto_mensagem'] = 'Erro ao fazer upload da foto. Verifique se o formato é válido (JPG, PNG ou GIF) e se o tamanho é menor que 5MB.';
        $_SESSION['foto_status'] = 'error';
    }
    
    // Redireciona de volta para a página de perfil
    header('Location: ../perfil_' . $tipo_usuario . '.php');
    exit;
} else {
    // Se não for uma requisição POST, redireciona para a página de perfil
    $tipo_usuario = $auth->ehCliente() ? 'cliente' : 'barbearia';
    header('Location: ../perfil_' . $tipo_usuario . '.php');
    exit;
}
?>