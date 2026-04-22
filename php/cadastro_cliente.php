<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (empty($usuario) || empty($email) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
        exit;
    }

    // Verifica se o email já existe
    $stmt = $conn->prepare("SELECT * FROM clients WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email já cadastrado.']);
        exit;
    }

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o novo usuário
    $stmt = $conn->prepare("INSERT INTO clients (UserName, Email, Password, UserType) VALUES (?, ?, ?, 'client')");
    $stmt->bind_param("sss", $usuario, $email, $senhaHash);

    if ($stmt->execute()) {
        // Se o cadastro for bem-sucedido
        echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!', 'redirect' => 'login.html']);
    } else {
        // Se ocorrer um erro ao cadastrar
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar. Tente novamente mais tarde.']);
    }

    exit;
}
?>