<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
        exit;
    }

    // Função para verificar o login
    function verificarLogin($conn, $email, $senha, $table, $idColumn, $passwordColumn, $redirectPage) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($senha, $user[$passwordColumn])) {
                $_SESSION['user_id'] = $user[$idColumn];
                $_SESSION['user_type'] = $table;
                echo json_encode(['success' => true, 'redirect' => $redirectPage]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
                exit;
            }
        }
        return false;
    }

    // Verifica login do cliente
    if (verificarLogin($conn, $email, $senha, 'clients', 'UserId', 'Password', 'cliente_homepage.php')) {
        exit;
    }

    // Verifica login do barbeiro
    if (verificarLogin($conn, $email, $senha, 'barbershop', 'BarbershopId', 'Password', 'empresa_dashboard.php')) {
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Email não encontrado.']);
}
?>
