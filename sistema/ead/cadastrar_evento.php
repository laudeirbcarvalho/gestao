<?php
require_once('../../sistema/db.php'); 
require_once('../../sistema/protege.php');

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $nome = $_POST['nomeEvento'];
    $dataInicial = $_POST['dataInicialEvento'];
    $dataFinal = $_POST['dataFinalEvento'];
    $valor = $_POST['valorEvento'];
    $descricao = $_POST['descricaoEvento'];
    $central_id = $sessao_central; // Obtém o central_id da sessão

    // Caminhos dos arquivos
    $logo = '';
    $banner = '';

    // Upload do Logo do Evento
    if (isset($_FILES['logoEvento']) && $_FILES['logoEvento']['error'] == 0) {
        $logo = UPLOADS.'leads/' . uniqid() . '_' . basename($_FILES['logoEvento']['name']);
        move_uploaded_file($_FILES['logoEvento']['tmp_name'], $logo);
    }

    // Upload do Banner do Evento
    if (isset($_FILES['bannerEvento']) && $_FILES['bannerEvento']['error'] == 0) {
        $banner = UPLOADS.'leads/' . uniqid() . '_' . basename($_FILES['bannerEvento']['name']);
        move_uploaded_file($_FILES['bannerEvento']['tmp_name'], $banner);
    }

    // Prepara o SQL para Inserir o Evento no Banco de Dados
    $sql = "INSERT INTO eventos (nome, data_inicial, data_final, valor, descricao, logo, banner, central_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $nome, $dataInicial, $dataFinal, $valor, $descricao, $logo, $banner, $central_id);

    // Executa a inserção e verifica se deu certo
    if ($stmt->execute()) {
        echo "Evento cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar o evento: " . $stmt->error;
    }

    // Fecha o statement e a conexão com o banco de dados
    $stmt->close();
    $conn->close();
} else {
    echo "Requisição inválida.";
}
?>
