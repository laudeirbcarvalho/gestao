<?php
require_once('../../db.php');

// Verifica se a sessão contém o central_id
if (!isset($_SESSION['central_id'])) {
    echo "Central ID não encontrado. O usuário não está logado corretamente.";
    exit();
}

// Preparando a consulta SQL para buscar usuários com base no central_id
$sql = "SELECT id, nome, email, senha, data_cadastro, status, avatar, whatsapp, permissao, cargo
        FROM usuarios
        WHERE central_id = ?
        ORDER BY data_cadastro DESC";

// Preparando a consulta
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Ligando o parâmetro central_id à consulta (usando 'i' para indicar que é um inteiro)
    $stmt->bind_param("i", $_SESSION['central_id']);
    
    // Executando a consulta
    $stmt->execute();
    
    // Obtendo o resultado da consulta
    $result = $stmt->get_result();

    // Exibindo os dados
    while ($row = $result->fetch_assoc()):
        $data_cadastro = !empty($row['data_cadastro']) ? date("d/m/Y H:i", strtotime($row['data_cadastro'])) : 'Data não disponível';
        if($_SESSION["permissao"] == 'admin') {
            $cargo = "Administrador";
        } else {
            $cargo = $_SESSION["cargo"];
        }
        ?>
        <li class="list-group-item">
            <img  src="<?= htmlspecialchars($row['avatar']) ?>" alt="Avatar" class="rounded-circle">
            <strong><?php echo htmlspecialchars($row['nome']); ?></strong> <br>            
            <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
            <strong>Data de Cadastro:</strong> <?php echo htmlspecialchars($data_cadastro); ?><br>
            <strong>Status:</strong> <?php echo htmlspecialchars($row['status']); ?><br>
            <strong>WhatsApp:</strong> <?php echo htmlspecialchars($row['whatsapp']); ?><br>
            <strong>Permissão:</strong> <?php echo htmlspecialchars($row['permissao']); ?><br>
            <strong>Função:</strong> <?php echo htmlspecialchars($row['cargo']); ?><br>
            <button id="editarusuario" class="edit-button" data-id="<?= $row['id'] ?>" data-nome="<?= $row['nome'] ?>" data-email="<?= $row['email'] ?>" data-whatsapp="<?= $row['whatsapp'] ?>" data-permissao="<?= $row['permissao'] ?>" data-cargo="<?= $row['cargo'] ?>" data-avatar="<?= $row['avatar'] ?>">
                <i class="fas fa-pencil-alt"></i> Editar
            </button>
             
            <button class="deletaruser" data-id="<?= $row['id'] ?>">
                <i class="fas fa-trash-alt"></i> Excluir
            </button>
           
        </li>
    <?php endwhile;

    // Fechando o statement
    $stmt->close();
} else {
    echo "Erro ao preparar a consulta: " . $conn->error;
}

// Fechar a conexão
$conn->close();
?>
