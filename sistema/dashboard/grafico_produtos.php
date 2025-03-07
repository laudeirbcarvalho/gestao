<?php
// Inclui os arquivos de conexão e proteção
include '../../sistema/db.php';
include '../../sistema/protege.php';

// Verifica se a requisição é AJAX
if (isset($_POST['acao']) && $_POST['acao'] == 'carregar_grafico_produtos') {
    // Inicializa os arrays para os dados do gráfico
    $labels = [];
    $quantidades = [];

    // Consulta para obter os produtos mais vendidos
    $sql = "SELECT 
    ip.produto_nome,
    SUM(ip.quantidade) AS total_quantidade_vendida
FROM 
    itens_pedido_shopify ip
JOIN 
    pedidos_shopify p ON ip.order_id = p.order_id
JOIN 
    clientes_shopify c ON p.customer_id = c.id_shopify
WHERE 
    p.financial_status IN ('paid', 'paid_shopify')
    AND c.central_id = '$sessao_central' -- Verifique se é string ou número
GROUP BY 
    ip.produto_nome
ORDER BY 
    total_quantidade_vendida DESC
LIMIT 5;

        ";  // Ordenando os mais vendidos

    $result = $conn->query($sql);

    // Preenchendo os arrays com os dados do PHP
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = addslashes($row['produto_nome']);
            $quantidades[] = (int)$row['total_quantidade_vendida'];
        }
    }

    // Fecha a conexão com o banco de dados
    $conn->close();

    // Retorna os dados em formato JSON
    echo json_encode([
        'labels' => $labels,
        'quantidades' => $quantidades
    ]);
    exit(); // Interrompe o script para evitar renderização desnecessária
}
?>
