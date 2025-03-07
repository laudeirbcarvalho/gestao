<?php
// Inclui os arquivos de conexão e proteção
include '../../sistema/db.php';
include '../../sistema/protege.php';

// Links para os arquivos CSS e JavaScript do Bootstrap
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>';
echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'; 
// Inicializa as variáveis para os totais
$total_pedidos_geral = 0;
$total_pago_geral = 0;
$total_faturado_geral = 0;

// Consulta para obter dados dos clientes e pedidos
$sql = "SELECT 
    c.nome,
    c.data_cadastro,
    c.cidade,
    c.estado,
    MIN(p.created_at) AS data_primeiro_pedido,
    COUNT(DISTINCT p.n_pedido) AS total_pedidos,
    MAX(p.created_at) AS data_ultimo_pedido,
    DATEDIFF(CURDATE(), MAX(p.created_at)) AS dias_sem_pedido,
    (SELECT total_price FROM pedidos_shopify WHERE customer_id = c.id_shopify ORDER BY created_at DESC LIMIT 1) AS valor_ultimo_pedido,
    SUM(p.total_price) AS total_faturado,
    SUM(CASE WHEN p.financial_status = 'paid' OR p.financial_status = 'shopify_paid' THEN p.total_price ELSE 0 END) AS total_pago
FROM clientes_shopify c
LEFT JOIN pedidos_shopify p ON c.id_shopify = p.customer_id
WHERE c.central_id = '$sessao_central' AND c.status = 'ativo'
GROUP BY c.id_shopify, p.n_pedido
ORDER BY p.created_at DESC";

$result = $conn->query($sql);

// Exibe os dados em uma tabela Bootstrap
echo '<div class="container">';
// Adiciona o filtro por mês
echo '<div class="form-group">';
echo '<label for="mesFiltro">Filtrar por Mês:</label>';
echo '<select class="form-control" id="mesFiltro">';
echo '<option value="">Todos os Meses</option>';
echo '<option value="01">Janeiro</option>';
echo '<option value="02">Fevereiro</option>';
echo '<option value="03">Março</option>';
echo '<option value="04">Abril</option>';
echo '<option value="05">Maio</option>';
echo '<option value="06">Junho</option>';
echo '<option value="07">Julho</option>';
echo '<option value="08">Agosto</option>';
echo '<option value="09">Setembro</option>';
echo '<option value="10">Outubro</option>';
echo '<option value="11">Novembro</option>';
echo '<option value="12">Dezembro</option>';
echo '</select>';
echo '</div>';
// Loop para calcular os totais
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_pedidos_geral += $row["total_pedidos"];
        $total_faturado_geral += $row["total_faturado"];
        $total_pago_geral += $row["total_pago"];
    }
}

// Exibindo os totais acima da tabela

 
// Defina a variável $totais antes de usá-la no JavaScript
$totais = '';
/*
$totais .= "Pedidos: " . $total_pedidos_geral . " | ";
$totais .= "Total: R$ " . number_format($total_faturado_geral, 2, ',', '.') . " | ";
$totais .= "Total Pago: R$ " . number_format($total_pago_geral, 2, ',', '.') . "\n";
 */


// Reseta o ponteiro do resultado para reutilizar no loop de exibição
$result->data_seek(0);

echo "<table class='table table-striped w-100'>";
echo "<thead>
        <tr class=''>
            <th>Nome</th>
            <th>Data Cadastro</th>
            <th>Data Primeiro Pedido</th>
            <th>Total de Pedidos</th>
            <th>Data Último Pedido</th>
            <th>Dias Sem Pedido</th>
            <th>Valor Último Pedido</th>
            <th>Total em Pedidos</th>
            <th>Total Pago</th>
        </tr>
    </thead>";
echo "<tbody>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_criacao = !empty($row['data_cadastro']) ? date('d/m/Y H:i:s', strtotime($row['data_cadastro'])) : 'N/A';
        $data_primeiro_pedido = !empty($row['data_primeiro_pedido']) ? date('d/m/Y H:i:s', strtotime($row['data_primeiro_pedido'])) : 'N/A';
        $data_ultimo_pedido = !empty($row['data_ultimo_pedido']) ? date('d/m/Y H:i:s', strtotime($row['data_ultimo_pedido'])) : 'N/A';

        if (!empty($row['nome'])) {
            echo "<tr>";
            echo "<td>" . $row["nome"] . " <br> <em class='small'> " . $row["cidade"] . "/" . $row["estado"] . " </em></td>";
            echo "<td>" . $data_criacao . "</td>";
            echo "<td>" . $data_primeiro_pedido . "</td>";
            echo "<td>" . $row["total_pedidos"] . "</td>";
            echo "<td>" . $data_ultimo_pedido . "</td>";
            echo "<td>" . $row["dias_sem_pedido"] . "</td>";
            echo "<td>" . $row["valor_ultimo_pedido"] . "</td>";
            echo "<td>" . $row["total_faturado"] . "</td>";
            echo "<td>" . $row["total_pago"] . "</td>";
            echo "</tr>";
        }
    }
} else {
    echo "<tr><td colspan='9'>Nenhum cliente encontrado.</td></tr>";
}

echo "</tbody>";
echo "</table>";
echo '</div>'; // Fecha a div container

/*
echo '<!-- Adicionando o gráfico logo abaixo da tabela -->
<div class="container">
    <!-- Adicionando o gráfico -->
    <canvas id="graficoPedidos"></canvas>
</div>
';
*/
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
    var dataAtual = new Date().toLocaleDateString();
    var NOME_SHOPIFY = "<?= addslashes(NOME_SHOPIFY) ?>";
    var totais = `<?php echo addslashes($totais); ?>`;

    // Inicializa a tabela DataTable
    var table = $('.table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'print',
                title: NOME_SHOPIFY + ' - Relatório de Pedidos  - impresso em ' + dataAtual,
                messageTop: totais
            },
            {
                extend: 'pdfHtml5',
                title: NOME_SHOPIFY + ' - Relatório de Pedidos  - impresso em ' + dataAtual,
                messageTop: totais
            },
            {
                extend: 'excelHtml5',
                title: NOME_SHOPIFY + ' - Relatório de Pedidos  - impresso em ' + dataAtual,
                messageTop: totais
            }
        ]
    });

    // Adiciona o filtro por mês
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var mesSelecionado = $('#mesFiltro').val();
            var dataUltimoPedido = data[4]; // A coluna da data do último pedido

            if (!mesSelecionado) {
                return true; // Exibe todas as linhas se nenhum mês for selecionado
            }

            if (dataUltimoPedido === 'N/A') {
                return false; // Não exibe linhas com "N/A" na data do último pedido
            }

            var mesPedido = dataUltimoPedido.substring(3, 5); // Extrai o mês da data no formato "dd/mm/aaaa"

            return mesPedido === mesSelecionado;
        }
    );

    // Atualiza a tabela quando o mês selecionado muda
    $('#mesFiltro').on('change', function () {
        table.draw();
    });

    // Dados do gráfico (substitua conforme necessário com dados reais)
    var labels = [];
    var pedidos = [];
    <?php
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        echo "labels.push('" . addslashes($row['nome']) . "');";
        echo "pedidos.push(" . (int) $row['total_pedidos'] . ");";
    }
    ?>

    // Configuração do gráfico de barras
    var ctx = document.getElementById('graficoPedidos').getContext('2d');
    var graficoPedidos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total de Pedidos',
                data: pedidos,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

