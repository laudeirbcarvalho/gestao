<?php
// Inclui os arquivos de conexão e proteção
include '../../sistema/db.php';
include '../../sistema/protege.php';

// Links para os arquivos CSS e JavaScript do Bootstrap
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>';
echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';

// Inclui o Chart.js para o gráfico
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Inicializa as variáveis para os totais
$total_quantidade_geral = 0;
$total_faturado_geral = 0;

// Consulta para obter os produtos mais vendidos
$sql = "
    SELECT 
        ip.produto_nome,
        SUM(ip.quantidade) AS total_quantidade_vendida,
        SUM(ip.total) AS total_faturado
    FROM 
        itens_pedido_shopify ip
    JOIN 
        pedidos_shopify p ON ip.order_id = p.order_id
    WHERE 
        p.financial_status = 'paid' -- Considera apenas pedidos pagos
    GROUP BY 
        ip.produto_nome
    ORDER BY 
        total_quantidade_vendida DESC
";

$result = $conn->query($sql);

// Exibe os dados em uma tabela Bootstrap
echo '<div class="container">';

// Array para armazenar os dados do gráfico
$produtos = [];
$quantidade_vendida = [];
$faturamento = [];

// Loop para calcular os totais e armazenar dados para o gráfico
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_quantidade_geral += $row["total_quantidade_vendida"];
        $total_faturado_geral += $row["total_faturado"];
        // Adiciona os dados ao array para o gráfico
        $produtos[] = $row["produto_nome"];
        $quantidade_vendida[] = $row["total_quantidade_vendida"];
        $faturamento[] = $row["total_faturado"];
    }
}

// Exibindo os totais acima da tabela
$totais = '';
$totais .= "Total de Produtos Vendidos: " . $total_quantidade_geral . "  ";
//$totais .= "Total Faturado: R$ " . number_format($total_faturado_geral, 2, ',', '.') . "\n";

// Reseta o ponteiro do resultado para reutilizar no loop de exibição
$result->data_seek(0);

echo "<table class='table table-striped w-100'>";
echo "<thead>
        <tr>
            <th>Produto</th>
            <th>Total Vendido (Quantidade)</th>
          <!--  <th>Total Faturado</th> -->
        </tr>
    </thead>";
echo "<tbody>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["produto_nome"] . "</td>";
        echo "<td>" . $row["total_quantidade_vendida"] . "</td>";
       // echo "<td>R$ " . number_format($row["total_faturado"], 2, ',', '.') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>Nenhum produto encontrado.</td></tr>";
}

echo "</tbody>";
echo "</table>";
echo '</div>'; // Fecha a div container

// Gráfico com Chart.js
echo '<div class="container mt-5">';
echo '<canvas id="myChart" width="400" height="200"></canvas>';
echo '</div>';

echo '<script>
var ctx = document.getElementById("myChart").getContext("2d");
var myChart = new Chart(ctx, {
    type: "bar",
    data: {
        labels: ' . json_encode($produtos) . ',
        datasets: [{
            label: "Quantidade Vendida",
            data: ' . json_encode($quantidade_vendida) . ',
            backgroundColor: "rgba(54, 162, 235, 0.2)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 1
        }, {
            label: "Faturamento",
            data: ' . json_encode($faturamento) . ',
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            borderColor: "rgba(255, 99, 132, 1)",
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>';

// Fecha a conexão com o banco de dados
$conn->close();
?>

<!-- DataTables e Botões -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
    var dataAtual = new Date().toLocaleDateString(); // Obtém a data atual no formato local
    var NOME_SHOPIFY = "<?= addslashes(NOME_SHOPIFY) ?>"; // Converte a variável PHP para string JS
    var totais = `<?php echo addslashes($totais); ?>`; // Transfere os totais para o JavaScript

    $('.table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'print',
                title: NOME_SHOPIFY + ' - Relatório de Produtos Mais Vendidos - ' + dataAtual,
                messageTop: totais // Adiciona o resumo no topo do relatório
            },
            {
                extend: 'pdfHtml5',
                title: NOME_SHOPIFY + ' - Relatório de Produtos Mais Vendidos - ' + dataAtual,
                messageTop: totais // Adiciona o resumo no topo do PDF
            },
            {
                extend: 'excelHtml5',
                title: NOME_SHOPIFY + ' - Relatório de Produtos Mais Vendidos - ' + dataAtual,
                messageTop: totais // Adiciona o resumo no topo do Excel
            }
        ]
    });
});
</script>
