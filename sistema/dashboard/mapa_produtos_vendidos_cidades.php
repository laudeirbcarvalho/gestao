<?php
include '../../sistema/db.php';
include '../../sistema/protege.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT 
    c.cidade,
    m.latitude,
    m.longitude,
    IFNULL(SUM(ip.quantidade), 0) AS total_produtos_vendidos,
    COALESCE((
        SELECT ip2.produto_nome
        FROM itens_pedido_shopify ip2
        INNER JOIN pedidos_shopify p2 ON ip2.order_id = p2.order_id
        INNER JOIN clientes_shopify c2 ON p2.customer_id = c2.id_shopify
        WHERE c2.cidade = c.cidade 
          AND c2.central_id = '$sessao_central' 
          AND p2.financial_status IN ('paid_shopify','paid')
        GROUP BY ip2.produto_nome
        ORDER BY SUM(ip2.quantidade) DESC
        LIMIT 1
    ), 'Nenhum produto vendido') AS produto_mais_vendido
FROM 
    clientes_shopify c
LEFT JOIN 
    pedidos_shopify p ON c.id_shopify = p.customer_id
LEFT JOIN 
    itens_pedido_shopify ip ON p.order_id = ip.order_id
LEFT JOIN 
    municipios m ON c.cidade = m.nome
WHERE 
    c.central_id = '$sessao_central' 
    AND m.latitude IS NOT NULL 
    AND m.longitude IS NOT NULL
GROUP BY 
    c.cidade, m.latitude, m.longitude
ORDER BY 
    total_produtos_vendidos DESC;



";

$result = $conn->query($sql);

try {
    if (!$result) {
        throw new Exception("Erro na consulta SQL: " . $conn->error);
    }

    $cidades = [];  // Array para armazenar os resultados
    while ($row = $result->fetch_assoc()) {
        $cidades[] = [
            'cidade' => $row['cidade'],
            'total_compras' => intval($row['total_produtos_vendidos'] ?? 0),  // Converte para inteiro
            'lat' => floatval($row['latitude'] ?? 0),
            'lon' => floatval($row['longitude'] ?? 0),
            'produto_mais_vendido' => $row['produto_mais_vendido'] 
        ];
    }

    echo json_encode($cidades, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>