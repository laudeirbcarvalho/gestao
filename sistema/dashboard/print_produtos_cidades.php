<?php
include '../../sistema/db.php';
include '../../sistema/protege.php';

// Conexão ao banco de dados e consulta SQL
$sessao_central = $_SESSION['central_id'];  // Exemplo de como pegar o ID da central

// Exemplo de consulta SQL, adapte conforme sua necessidade
$query = "
SELECT 
    c.cidade,
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
    ), 'Nenhum produto vendido') AS produto_mais_vendido,
    c.central_id,  -- Incluindo o central_id
    ce.central_nome  -- Incluindo o nome da central
FROM 
    clientes_shopify c
LEFT JOIN 
    pedidos_shopify p ON c.id_shopify = p.customer_id
LEFT JOIN 
    itens_pedido_shopify ip ON p.order_id = ip.order_id
LEFT JOIN 
    municipios m ON c.cidade = m.nome
LEFT JOIN 
    central ce ON c.central_id = ce.central_id  -- Adicionando o JOIN com a tabela central
WHERE 
    c.central_id = '$sessao_central' 
    AND m.latitude IS NOT NULL 
    AND m.longitude IS NOT NULL
GROUP BY 
    c.cidade, c.central_id, ce.central_nome
ORDER BY 
    total_produtos_vendidos DESC;
";

// Aqui você vai executar a consulta e gerar o HTML da tabela.
$result = $conn->query($query);

// Gerar a tabela HTML
echo "<h1>Lista de Produtos Vendidos por Cidade</h1>";
echo "<table border='1' cellpadding='3' cellspacing='0'>";
echo "<tr>
        <th>Cidade</th>
        <th>Total de Produtos Vendidos</th>
        <th>Produto Mais Vendido</th>
        <th>Central ID</th>
        <th>Nome da Central</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['cidade'] . "</td>";
    echo "<td>" . $row['total_produtos_vendidos'] . "</td>";
    echo "<td>" . $row['produto_mais_vendido'] . "</td>";
    echo "<td>" . $row['central_id'] . "</td>";
    echo "<td>" . $row['central_nome'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
