<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Verifica se os dados foram enviados corretamente
if (isset($_POST['sku'], $_POST['field'], $_POST['value'])) {
    $sku = $_POST['sku'];
    $field = $_POST['field'];
    $value = $_POST['value'];
    $central_id = $sessao_central; 

    // Verificar se o valor é numérico e maior que zero
    if (!is_numeric($value) || $value <= 0) {
        echo "Valor inválido. O preço deve ser um número maior que zero.";
        exit();
    }

    // Atualiza o preço na tabela 'produtos'
    $sql = "UPDATE produtos SET {$field} = ? WHERE sku = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ds', $value, $sku);

    if ($stmt->execute()) {
        echo "<script> alert('Preço atualizado com sucesso na tabela produtos!');</script>";
    } else {
        echo "<script> alert('Erro ao atualizar o preço na tabela produtos.');</script>";
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
        // Atualiza o preço se o SKU já existir
        $sql_update = "UPDATE precos_e_estoques SET {$field} = ? WHERE sku = ? AND central_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('dsi', $value, $sku, $central_id);
        
        if ($stmt_update->execute()) {
            echo "Preço atualizado com sucesso na tabela preços!";
        } else {
            echo "Erro ao atualizar o preço.";
        }
        
        $stmt_update->close();
    } else {
        // Insere um novo registro se o SKU não existir para a central_id
        $sql_insert = "INSERT INTO precos_e_estoques (sku, central_id, {$field}) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param('sid', $sku, $central_id, $value);
        
        if ($stmt_insert->execute()) {
            echo "Novo preço inserido com sucesso na tabela preços!";
        } else {
            echo "Erro ao inserir novo preço.";
        }
        
        $stmt_insert->close();
    }
} else {
    echo "Dados incompletos.";
}
?>
