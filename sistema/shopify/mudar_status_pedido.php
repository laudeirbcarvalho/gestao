<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

if (isset($_POST['pedido_id']) && isset($_POST['status'])) {
    $pedido_id = $_POST['pedido_id']; // Obtém o ID do pedido e converte para inteiro
    $status = $_POST['status']; // Obtém o status a ser atualizado
    $nota = isset($_POST['nota']) ? trim($_POST['nota']) : null; // Obtém a nota se existir


    // Adiciona a validação para evitar alteração se o ID do pedido for 0 ou vazio
    if (empty($pedido_id) || $pedido_id === 0) {
        echo "ID do pedido é inválido. Nenhuma alteração foi feita.";
        exit; // Encerra a execução do script
    }

    // Verifica se o status é um dos suportados
    if ($status === 'paid' || $status === 'canceled') {
        // Prepara a consulta SQL para atualizar o status do pedido
        $sql = "UPDATE pedidos_shopify SET financial_status = ? WHERE n_pedido = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("ss", $status, $pedido_id); // Vincula os parâmetros

        if ($stmt->execute()) {

            // Atualiza a nota se fornecida
            if (!empty($nota)) {
                $sql_nota = "UPDATE pedidos_shopify SET nota = ? WHERE n_pedido = ?";
                $stmt_nota = $conn->prepare($sql_nota);
                if ($stmt_nota) {
                    $stmt_nota->bind_param("ss", $nota, $pedido_id);
                    $stmt_nota->execute();
                    $stmt_nota->close();
                    echo " Nota adicionada com sucesso.";
                } else {
                    echo " Erro ao salvar a nota.";
                }
            }

            // Se o status for 'paid', processa o upload do comprovante
            if ($status === 'paid' && isset($_FILES['comprovante'])) {
                $upload_dir = '../../'.UPLOADS . 'comprovantes/';

                // Obtém a extensão do arquivo original
                $file_ext = pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION);
                // Remove o # do número do pedido, caso exista
                $pedido_id_comprovante = str_replace('#', '', $pedido_id);

                // Define o novo nome do arquivo como 'pedido_id.extensão'
                $file_name = $pedido_id_comprovante . '.' . $file_ext;
                $upload_file = $upload_dir . $file_name;


                // Move o arquivo enviado para o diretório de upload
                if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $upload_file)) {
                    // Cria o link do comprovante
                    $comprovante_link = URL .'/'. $upload_file; // Substitua 'seusite.com' pelo seu domínio real

                    $comprovante_link = str_replace('../../', '', $comprovante_link);

                    // Atualiza o caminho do comprovante no banco de dados
                    $sql_update_comprovante = "UPDATE pedidos_shopify SET comprovante = ? WHERE n_pedido = ?";
                    $stmt_comprovante = $conn->prepare($sql_update_comprovante);

                    if ($stmt_comprovante) {
                        $stmt_comprovante->bind_param("ss", $comprovante_link, $pedido_id);
                        $stmt_comprovante->execute();
                        $stmt_comprovante->close();
                        echo "Status do pedido atualizado para 'Pago' com sucesso e comprovante salvo.";
                    } else {
                        echo "Status do pedido atualizado para 'Pago', mas erro ao salvar o comprovante no banco.";
                    }

                    // Se o comprovante foi carregado, inclua o link na nota
                    if ($nota) {
                        $nota .= "<br><a href='$comprovante_link' target='_blank'>Clique aqui para ver o comprovante</a>";
                    } else {
                        $nota = "<a href='$comprovante_link' target='_blank'>Clique aqui para ver o comprovante</a>";
                    }

                    // Atualiza a nota com o link do comprovante
                    $sql_nota_comprovante = "UPDATE pedidos_shopify SET nota = ? WHERE n_pedido = ?";
                    $stmt_nota_comprovante = $conn->prepare($sql_nota_comprovante);
                    if ($stmt_nota_comprovante) {
                        $stmt_nota_comprovante->bind_param("ss", $nota, $pedido_id);
                        $stmt_nota_comprovante->execute();
                        $stmt_nota_comprovante->close();
                    }
                } else {
                    echo "Status do pedido atualizado para 'Pago', mas erro ao enviar o comprovante.";
                }
            } else {
                echo "Status do pedido atualizado para '$status' com sucesso.";
            }
        } else {
            echo "Erro ao atualizar status: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Status inválido. Use 'Pago' ou 'Cancelado'.";
    }
} else {
    echo "Dados inválidos.";
}

$conn->close(); // Fecha a conexão com o banco de dados
?>
