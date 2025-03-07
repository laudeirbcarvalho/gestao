<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');

// Verifica se o usuário está logado e tem permissão para excluir
if (!isset($_SESSION['usuario_id']) || $_SESSION["permissao"] !== "admin") {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// Verifica se o método é POST e os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodifica os dados JSON enviados via AJAX
    $data = json_decode(file_get_contents('php://input'), true);
    $central_id = $data['central_id'] ?? null;

    if (!$central_id) {
        echo json_encode(['success' => false, 'error' => 'ID da central inválido.']);
        exit();
    }

    // Começa a transação para garantir que ambos os registros (central e usuário) sejam excluídos de forma atômica
    $conn->begin_transaction();

    try {
        // Excluir o usuário associado à central
        $query_user = "DELETE FROM usuarios WHERE central_id = ?";
        $stmt_user = $conn->prepare($query_user);
        $stmt_user->bind_param("i", $central_id);
        if (!$stmt_user->execute()) {
            throw new Exception("Erro ao excluir o usuário associado.");
        }
        $stmt_user->close();

        // Excluir a central
        $query_central = "DELETE FROM central WHERE central_id = ?";
        $stmt_central = $conn->prepare($query_central);
        $stmt_central->bind_param("i", $central_id);
        if (!$stmt_central->execute()) {
            throw new Exception("Erro ao excluir a central.");
        }
        $stmt_central->close();

         // Excluir a config
         $query_config = "DELETE FROM config WHERE central_id = ?";
         $stmt_config = $conn->prepare($query_config);
         $stmt_config->bind_param("i", $central_id);
         if (!$stmt_config->execute()) {
             throw new Exception("Erro ao excluir o config.");
         }
         $stmt_config->close();

        // Commit da transação se tudo ocorreu bem
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
}
