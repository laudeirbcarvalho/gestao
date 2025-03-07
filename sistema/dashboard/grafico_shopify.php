<?php
// Inclui os arquivos de conexão e proteção
include '../../sistema/db.php';
include '../../sistema/protege.php';
 
// Verifica se a requisição é AJAX
if (isset($_POST['acao']) && $_POST['acao'] == 'carregar_grafico') {
    // Inicializa os arrays para os dados do gráfico
    $labels = [];
    $pedidos = [];

    // Consulta para obter dados dos clientes e pedidos
    $sql = "SELECT 
                c.nome,
                COUNT(DISTINCT p.n_pedido) AS total_pedidos
            FROM clientes_shopify c 
            LEFT JOIN pedidos_shopify p ON c.id_shopify = p.customer_id
            WHERE c.central_id = '$sessao_central'
            AND p.financial_status IN ('paid', 'paid_shopify')
            GROUP BY c.id_shopify
            ORDER BY total_pedidos DESC
            LIMIT 5
            ";

    $result = $conn->query($sql);

    // Preenchendo os arrays com os dados do PHP
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = addslashes($row['nome']);
            $pedidos[] = (int)$row['total_pedidos'];
        }
    }

    // Fecha a conexão com o banco de dados
    $conn->close();

    // Retorna os dados em formato JSON
    echo json_encode([
        'labels' => $labels,
        'pedidos' => $pedidos
    ]);
    exit(); // Interrompe o script para evitar renderização desnecessária
}
?>
