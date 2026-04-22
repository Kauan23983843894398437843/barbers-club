<?php


require_once '../includes/auth.php';
require_once '../includes/perfil.php';

$auth = new Auth();
$perfil = new Perfil();

if (!$auth->estaLogado()) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

if (isset($_GET['id_barbearia']) && is_numeric($_GET['id_barbearia'])) {
    $id_barbearia = (int) $_GET['id_barbearia'];
    
    $barbeiros = $perfil->getBarbeirosDaBarbearia($id_barbearia);
    
    header('Content-Type: application/json');
    echo json_encode(['barbeiros' => $barbeiros]);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'ID da barbearia não fornecido']);
    exit;
}
?>