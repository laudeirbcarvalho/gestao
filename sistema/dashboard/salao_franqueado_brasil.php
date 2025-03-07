<?php
include '../../sistema/db.php';
include '../../sistema/protege.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT 
    c.nome AS nome_cliente,
    c.cidade,
    m.latitude,
    m.longitude,
    COUNT(*) OVER (PARTITION BY c.cidade) AS quantidade_clientes,
    cen.central_id
FROM 
    clientes_shopify c
LEFT JOIN 
    municipios m ON c.cidade = m.nome
LEFT JOIN  
    central cen ON c.central_id = cen.central_id  
WHERE 
    m.latitude IS NOT NULL AND m.longitude IS NOT NULL
    AND c.status = 'ativo'
    -- AND c.central_id = $sessao_central
    ";

$result = $conn->query($sql);

try {
    if (!$result) {
        throw new Exception("Erro na consulta SQL: " . $conn->error);
    }

    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = [            
            'cidade' => $row['cidade'],
            'lat' => floatval($row['latitude'] ?? 0),
            'lon' => floatval($row['longitude'] ?? 0),
            'quantidade_clientes' => intval($row['quantidade_clientes']),
            'central_id' => $row['central_id']
        ];
    }

    echo json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>