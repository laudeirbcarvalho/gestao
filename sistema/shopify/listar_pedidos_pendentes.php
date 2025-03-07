<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

header('Content-Type: application/json');

try {
    // Consulta para buscar pedidos pendentes e associar ao nome do cliente
    $sql = "SELECT 
                p.n_pedido, 
                c.nome AS customer_name, 
                p.total_price, 
                p.created_at 
            FROM 
                pedidos_shopify AS p
            LEFT JOIN 
                clientes_shopify AS c
            ON 
                p.customer_id = c.id_shopify            
            WHERE 
                p.financial_status = 'pending' AND
                c.central_id = '$sessao_central'
            ORDER BY
                p.created_at DESC"; // Aqui estava faltando o ponto e vírgula
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('d/m/Y H:i:s', strtotime($row['created_at'])); // Formata a data
        $pedidos[] = $row;
    }

    // Retorna os dados como JSON
    echo json_encode(['status' => 'success', 'data' => $pedidos,'button' => '<button id="closeButton" onclick="closeDiv()">Fechar</button>']);
} catch (Exception $e) {
    // Em caso de erro, retorna uma mensagem de erro no JSON
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
