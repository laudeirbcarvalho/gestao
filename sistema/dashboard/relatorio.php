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
        c.cidade,
        c.estado,
        p.n_pedido,
        p.created_at AS data_pedido,
        p.data_import AS data_pago,
        p.total_price,
        p.financial_status,
        CASE 
            WHEN p.financial_status = 'paid' OR p.financial_status = 'shopify_paid' THEN p.total_price 
            ELSE 0 
        END AS total_pago,
        COUNT(p.n_pedido) AS total_pedidos,
        SUM(CASE 
                WHEN p.financial_status = 'paid' OR p.financial_status = 'shopify_paid' THEN p.total_price 
                ELSE 0 
            END) AS total_faturado
    FROM pedidos_shopify p
    LEFT JOIN clientes_shopify c ON c.id_shopify = p.customer_id
    WHERE c.central_id = '$sessao_central'
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
            <th>Nº Pedido</th>
            <th>Data Pedido</th>
            <th>Data Integrado (pago)</th>
            <th>Total do Pedido</th>
            <th>Status</th>
        </tr>
    </thead>";
echo "<tbody>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_pedido = !empty($row['data_pedido']) ? date('d/m/Y H:i:s', strtotime($row['data_pedido'])) : 'Sem Data';
        if(($row["financial_status"]=='canceled') || ($row["financial_status"]=='pending') ){
          $data_pago = '-- -- -- -- -- --';
        }else{
          $data_pago = !empty($row['data_pago']) ? date('d/m/Y H:i:s', strtotime($row['data_pago'])) : 'Sem Data';  
        }
        


        $status_formatado = '';

if ($row["financial_status"] == 'paid') {
    $status_formatado = 'PAGO';
} elseif ($row["financial_status"] == 'paid_shopify') {
    $status_formatado = 'PAGO NA SHOPIFY';
} elseif ($row["financial_status"] == 'canceled') {
    $status_formatado = 'CANCELADO';
} elseif ($row["financial_status"] == 'pending') {
    $status_formatado = 'PENDENTE';
} else {
    $status_formatado = ucfirst(str_replace('_', ' ', $row["financial_status"]));
}

 


        

        echo "<tr>";
        echo "<td>" . $row["nome"] . " <br> <em class='small'> " . $row["cidade"] . "/" . $row["estado"] . " </em></td>";
        echo "<td>" . $row["n_pedido"] . "</td>";
        echo "<td>" . $data_pedido . "</td>";
        echo "<td>" . $data_pago . "</td>";
        echo "<td>" . $row["total_price"] . "</td>";
        echo "<td>" . $status_formatado . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>Nenhum pedido encontrado.</td></tr>";
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
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8"
    src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

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
          title: NOME_SHOPIFY + ' - Relatório de Pedidos Detalhado - impresso em ' + dataAtual,
          messageTop: totais
        },
        {
          extend: 'pdfHtml5',
          title: NOME_SHOPIFY + ' - Relatório de Pedidos Detalhado - impresso em ' + dataAtual,
          messageTop: totais
        },
        {
          extend: 'excelHtml5',
          title: NOME_SHOPIFY + ' - Relatório de Pedidos Detalhado - impresso em ' + dataAtual,
          messageTop: totais
        }
      ]
    });

    // Adiciona o filtro por mês
    $.fn.dataTable.ext.search.push(
      function (settings, data, dataIndex) {
        var mesSelecionado = $('#mesFiltro').val();
        var dataPedido = data[2]; // A coluna da data do pedido (ajuste se necessário)

        if (!mesSelecionado) {
          return true; // Exibe todas as linhas se nenhum mês for selecionado
        }

        var mesPedido = dataPedido.substring(3, 5); // Extrai o mês da data no formato "dd/mm/aaaa"

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