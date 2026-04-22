<?php


require_once '../includes/auth.php';
require_once '../includes/avaliacoes.php';

$auth = new Auth();
$avaliacoes = new Avaliacoes();

$auth->verificarBarbearia();

$id_barbearia = $auth->getIdUsuarioLogado();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_avaliacao = filter_input(INPUT_POST, 'id_avaliacao', FILTER_VALIDATE_INT);
    $resposta = filter_input(INPUT_POST, 'resposta', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $erros = [];
    
    if (!$id_avaliacao) {
        $erros[] = 'Avaliação inválida';
    }
    
    if (empty($resposta)) {
        $erros[] = 'Resposta é obrigatória';
    }
    
    if (empty($erros)) {
        $sucesso = $avaliacoes->responderAvaliacao($id_avaliacao, $resposta, $id_barbearia);
        
        if ($sucesso) {
            $_SESSION['avaliacoes_mensagem'] = 'Resposta enviada com sucesso!';
            $_SESSION['avaliacoes_status'] = 'success';
        } else {
            $_SESSION['avaliacoes_mensagem'] = 'Erro ao enviar a resposta. Verifique se a avaliação pertence à sua barbearia.';
            $_SESSION['avaliacoes_status'] = 'error';
        }
    } else {
        $_SESSION['avaliacoes_erros'] = $erros;
        $_SESSION['avaliacoes_status'] = 'error';
    }
    
    header('Location: ../avaliacoes_barbearia.php');
    exit;
} else {
    header('Location: ../avaliacoes_barbearia.php');
    exit;
}
?>