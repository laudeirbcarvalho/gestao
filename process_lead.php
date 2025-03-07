<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Incluindo a conexão com o banco de dados
    require_once('sistema/db.php');

    // Validando os campos obrigatórios
    $requiredFields = ['nome', 'campanha', 'email', 'whatsapp', 'cidade', 'estado', 'central_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "O campo $field é obrigatório."]);
            exit;
        }
    }

    // Recebendo e escapando os dados do formulário
    $nome = $conn->real_escape_string($_POST['nome']);
    $campanha = $conn->real_escape_string($_POST['campanha']);
    $email = $conn->real_escape_string($_POST['email']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $cidade = $conn->real_escape_string($_POST['cidade']);
    $estado = $conn->real_escape_string($_POST['estado']);
    $central_id = (int) $_POST['central_id'];

    // Verificando duplicata no mesmo mês
    $sqlCheck = "
        SELECT id 
        FROM leads 
        WHERE email = '$email' 
          AND campanha = '$campanha' 
          AND MONTH(created_at) = MONTH(NOW()) 
          AND YEAR(created_at) = YEAR(NOW())
    ";
    $result = $conn->query($sqlCheck);

    header('Content-Type: application/json');
    if ($result->num_rows > 0) {
        // Já existe um registro com os mesmos dados no mesmo mês
        echo json_encode(['success' => false, 'message' => 'Já existe um cadastro para este evento neste mês.']);
    } else {
        // Inserindo no banco de dados
        $sqlInsert = "
            INSERT INTO leads (nome, campanha, email, whatsapp, cidade, estado, central_id, created_at) 
            VALUES ('$nome', '$campanha', '$email', '$whatsapp', '$cidade', '$estado', $central_id, NOW())
        ";
        if ($conn->query($sqlInsert) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Lead cadastrado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar lead: ' . $conn->error]);
        }
    }

    $conn->close();
} else {
    // Resposta para métodos diferentes de POST
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>
