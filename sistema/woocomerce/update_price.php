<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Verifica se os dados foram enviados corretamente
if (isset($_POST['sku'], $_POST['field'], $_POST['value'])) {
    $sku = sanitize_text_field($_POST['sku']);
    $field = sanitize_text_field($_POST['field']);
    $value = floatval($_POST['value']); // Converte para número
    $central_id = $sessao_central;

    // Verificar se o campo é válido
    $allowed_fields = ['preco_salao', 'preco_final', 'preco_mktplace', 'estoque'];
    if (!in_array($field, $allowed_fields)) {
        echo "Campo inválido.";
        exit();
    }

    // Verificar se o valor é numérico e maior que zero
    if (!is_numeric($value) || $value < 0) { // Permitir zero para estoque, se desejar
        echo "Valor inválido. O valor deve ser um número maior ou igual a zero.";
        exit();
    }

    // Atualiza o valor na tabela 'produtos'
    $sql = "UPDATE produtos SET {$field} = ? WHERE sku = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ds', $value, $sku);

    if ($stmt->execute()) {
        $response = "Valor atualizado com sucesso na tabela produtos!";
    } else {
        $response = "Erro ao atualizar o valor na tabela produtos.";
    }
    $stmt->close();

    // Verificar se o SKU já existe na tabela 'precos_e_estoques' para essa 'central_id'
    $sql_check = "SELECT COUNT(*) FROM precos_e_estoques WHERE sku = ? AND central_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('si', $sku, $central_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // Atualiza o valor se o SKU já existir
        $sql_update = "UPDATE precos_e_estoques SET {$field} = ? WHERE sku = ? AND central_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('dsi', $value, $sku, $central_id);
        
        if ($stmt_update->execute()) {
            $response .= " Valor atualizado com sucesso na tabela precos_e_estoques!";
        } else {
            $response .= " Erro ao atualizar o valor na tabela precos_e_estoques.";
        }
        $stmt_update->close();
    } else {
        // Insere um novo registro se o SKU não existir para a central_id
        $sql_insert = "INSERT INTO precos_e_estoques (sku, central_id, {$field}) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param('sid', $sku, $central_id, $value);
        
        if ($stmt_insert->execute()) {
            $response .= " Novo valor inserido com sucesso na tabela precos_e_estoques!";
        } else {
            $response .= " Erro ao inserir novo valor na tabela precos_e_estoques.";
        }
        $stmt_insert->close();
    }

    // Retornar resposta como JSON para melhor integração com o JavaScript
    echo json_encode(['success' => !str_contains($response, 'Erro'), 'message' => $response]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
}
?>