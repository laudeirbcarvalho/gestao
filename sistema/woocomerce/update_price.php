<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

if (isset($_POST['sku'], $_POST['field'], $_POST['value'])) {

    $sku = htmlspecialchars($_POST['sku'], ENT_QUOTES, 'UTF-8');
    $field = htmlspecialchars($_POST['field'], ENT_QUOTES, 'UTF-8');

    $value = ($field == 'estoque') ? intval($_POST['value']) : floatval($_POST['value']); // Usa inteiro para estoque, float para preços
    $central_id = $sessao_central;

    // Campos permitidos
    $allowed_fields = ['preco_salao', 'preco_final', 'preco_mktplace', 'estoque'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => "Campo inválido."]);
        exit();
    }

    // Atualiza o valor na tabela 'produtos'
    $sql = "UPDATE produtos SET $field = ? WHERE sku = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(($field == 'estoque' ? 'is' : 'ds'), $value, $sku);
        if ($stmt->execute()) {
            $response = "Valor atualizado com sucesso na tabela produtos!";
        } else {
            $response = "Erro ao atualizar o valor na tabela produtos.";
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => "Erro na preparação da query produtos."]);
        exit();
    }

    // Verifica se o SKU já existe na tabela 'precos_e_estoques'
    $sql_check = "SELECT COUNT(*) FROM precos_e_estoques WHERE sku = ? AND central_id = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param('si', $sku, $central_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    } else {
        echo json_encode(['success' => false, 'message' => "Erro na preparação da query de verificação."]);
        exit();
    }

    if ($count > 0) {
        // Atualiza se já existir
        $sql_update = "UPDATE precos_e_estoques SET $field = ? WHERE sku = ? AND central_id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param(($field == 'estoque' ? 'isi' : 'dsi'), $value, $sku, $central_id);
            if ($stmt_update->execute()) {
                $response .= " Valor atualizado na tabela precos_e_estoques!";
            } else {
                $response .= " Erro ao atualizar na tabela precos_e_estoques.";
            }
            $stmt_update->close();
        } else {
            echo json_encode(['success' => false, 'message' => "Erro na preparação da query update."]);
            exit();
        }
    } else {
        // Insere se não existir
        $sql_insert = "INSERT INTO precos_e_estoques (sku, central_id, $field) VALUES (?, ?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param('sid', $sku, $central_id, $value);
            if ($stmt_insert->execute()) {
                $response .= " Novo valor inserido na tabela precos_e_estoques!";
            } else {
                $response .= " Erro ao inserir novo valor na tabela precos_e_estoques.";
            }
            $stmt_insert->close();
        } else {
            echo json_encode(['success' => false, 'message' => "Erro na preparação da query insert."]);
            exit();
        }
    }

    echo json_encode(['success' => !str_contains($response, 'Erro'), 'message' => $response]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
}
?>