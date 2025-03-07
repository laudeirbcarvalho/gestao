<?php
require_once('sistema/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['customer_id']) && $_POST['action'] === 'delete') {
        $customer_id = $_POST['customer_id'];

        // Verifica se o cliente possui pedidos associados
        $sql = "SELECT COUNT(*) AS total_pedidos FROM pedidos_shopify WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total_pedidos'] > 0) {
            echo json_encode(array('success' => false, 'error' => 'Não é possível excluir o cliente. Ele possui pedidos associados.'));
        } else {
            // Exclui o cliente da tabela clientes_shopify
            $sql = "DELETE FROM clientes_shopify WHERE id_shopify = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $customer_id);
            $result = $stmt->execute();

            if ($result) {
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('success' => false, 'error' => 'Erro ao excluir o cliente.'));
            }
        }
    } else {
        echo json_encode(array('success' => false, 'error' => 'Requisição inválida.'));
    }
}
?>