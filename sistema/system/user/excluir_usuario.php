<?php
require_once('../../../sistema/db.php');
require_once('../../../sistema/protege.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $userId = $_POST['id'];

    // 1. Recupera o caminho da imagem do usuário antes da exclusão
    $sqlSelect = "SELECT avatar FROM usuarios WHERE id = ?";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bind_param("i", $userId);
    $stmtSelect->execute();
    $stmtSelect->bind_result($avatar);
    $stmtSelect->fetch();
    $stmtSelect->close();

    // 2. Remove a imagem, se existir
    $caminhoImagem = "../../../" . $avatar; // Adiciona o caminho correto do servidor

    if (!empty($avatar) && file_exists($caminhoImagem)) {
        unlink($caminhoImagem);
    }

    // 3. Exclui o usuário do banco de dados
    $sqlDelete = "DELETE FROM usuarios WHERE id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $userId);

    if ($stmtDelete->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuário e imagem excluídos com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário: ' . $stmtDelete->error]);
    }

    $stmtDelete->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
}
?>
