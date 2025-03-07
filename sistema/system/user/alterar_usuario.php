<?php
require_once('../../../sistema/db.php');
require_once('../../../sistema/protege.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $whatsapp = $_POST['whatsapp'];
    $permissao = $_POST['permissao'];
    $cargo = $_POST['cargo'];
    $avatar = $_POST['tem_avatar'];
    $central_id = $_SESSION['central_id'];

    // Verifica se um novo arquivo de avatar foi enviado
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../' . UPLOADS . 'avatares/'; // Diretório de upload

        // Verifica se o usuário já tem um avatar e exclui o arquivo antigo
        if ($avatar && $avatar !== 'avatardefault.jpg') {
            $oldAvatarPath = '../../../' . $avatar;
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath); // Exclui o arquivo antigo
            }
        }

        // Obtém a extensão do arquivo
        $extensao = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);

        // Formata o nome do cliente para o arquivo
        $nomeFormatado = preg_replace('/[^a-zA-Z0-9]/', '_', trim(strtolower($nome)));
        $nomeArquivo = $central_id . '-' . str_replace(' ', '-', $nome) . '.' . $extensao; // Compõe o nome do arquivo
        $uploadFile = $uploadDir . $nomeArquivo;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
            $avatar = $uploadDir . $nomeArquivo; // Salva o caminho do novo arquivo
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload do avatar.']);
            exit;
        }
    } else {
        $uploadDir = UPLOADS . 'avatares/';
        $avatar = $uploadDir . 'avatardefault.jpg'; // Usa a imagem padrão
    }

    // Limpa o link para salvar o caminho correto do avatar no banco de dados
    $avatar = str_replace("../../../", "", $avatar);

    // Construir a consulta SQL dinamicamente
    $sql = "UPDATE usuarios SET nome = ?, email = ?, whatsapp = ?";
    $params = [$nome, $email, $whatsapp];
    $types = 'sss';

    if (!empty($permissao)) {
        $sql .= ", permissao = ?";
        $params[] = $permissao;
        $types .= 's';
    }

    if (!empty($cargo)) {
        $sql .= ", cargo = ?";
        $params[] = $cargo;
        $types .= 's';
    }

    if (!empty($avatar)) {
        $sql .= ", avatar = ?";
        $params[] = $avatar;
        $types .= 's';
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>