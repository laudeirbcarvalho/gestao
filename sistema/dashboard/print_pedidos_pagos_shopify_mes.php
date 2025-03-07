<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Define o ano e o mês atual
$ano = date('Y');
$mes = date('m');

// Consulta o total de pedidos no mês atual
$sql_total = "SELECT COUNT(*) AS total_pedidos_mes FROM woo_comerce_integra WHERE status = 'integrado' AND YEAR(data_hora) = YEAR(CURDATE()) AND MONTH(data_hora) = MONTH(CURDATE())";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
$total_pedidos_mes = $row_total['total_pedidos_mes'];

// Consulta os pedidos do mês atual
$sql = "SELECT 
    w.n_pedido, 
    w.operador,
    w.data_hora, 
    p.customer_id,
    p.financial_status, 
    p.total_price,
    c.nome AS nome_cliente
FROM woo_comerce_integra w
LEFT JOIN pedidos_shopify p ON w.n_pedido = p.n_pedido
LEFT JOIN clientes_shopify c ON p.customer_id = c.id_shopify
WHERE w.status = 'integrado' 
    AND YEAR(w.data_hora) = YEAR(CURDATE())
    AND MONTH(w.data_hora) = MONTH(CURDATE()) -- Filtra pelo mês atual
    AND (p.financial_status = 'paid' OR p.financial_status = 'paid_shopify')
    AND c.central_id = '$sessao_central'
ORDER BY w.data_hora DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$nomeprojetoshopify = NOME_SHOPIFY;

// Verifica se há pedidos
if ($result->num_rows > 0) {
    // Exibe os pedidos em uma tabela HTML com layout Bootstrap
    echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
    echo "<div class='container'>";
    echo "<h1>Pedidos Pagos no Mês " . date('F') . " de $ano ($nomeprojetoshopify)</h1>"; // Título dinâmico com o mês atual
    echo "<p>Total de Pedidos no Mês: " . $total_pedidos_mes . "</p>"; // Exibe o total de pedidos no mês
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Nº Pedido</th>";
    echo "<th>Cliente</th>";
    echo "<th>Valor Total</th>";
    echo "<th>Status</th>";
    echo "<th>Data Integração</th>";
    echo "<th>Operador</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    $valor_total = 0; // Variável para armazenar o valor total dos pedidos

    while ($row = $result->fetch_assoc()) {
        // Formata a data
        $data_criacao = date('d/m/Y H:i:s', strtotime($row['data_hora']));
        echo "<tr>";
        echo "<td>" . $row['n_pedido'] . "</td>";
        echo "<td>" . $row['nome_cliente'] . "</td>";
        echo "<td>" . $row['total_price'] . "</td>";
        echo "<td>" . $row['financial_status'] . "</td>";
        echo "<td>" . $data_criacao . "</td>";
        echo "<td>" . $row['operador'] . "</td>";
        echo "</tr>";

        $valor_total += $row['total_price']; // Adiciona o valor do pedido ao valor total
    }

    echo "</tbody>";
    echo "</table>";
    echo "<div class='alert alert-primary' role='alert'>Valor Total dos Pedidos: R$ " . number_format($valor_total, 2, ',', '.') . "</div>"; // Exibe o valor total dos pedidos formatado em reais
    echo "</div>";
} else {
    echo "Não há pedidos pagos para o mês atual.";
}

// Fecha a conexão com o banco de dados
$conn->close();
?>