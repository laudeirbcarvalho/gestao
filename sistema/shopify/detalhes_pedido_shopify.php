<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
if (isset($_GET['n_pedido'])) {
    $n_pedido = $_GET['n_pedido'];

    $sql = "
        SELECT pedidos_shopify.*, clientes_shopify.nome AS nome_cliente, clientes_shopify.endereco, clientes_shopify.cidade, clientes_shopify.estado, clientes_shopify.pais, clientes_shopify.telefone, clientes_shopify.cpf, clientes_shopify.cnpj, clientes_shopify.email,clientes_shopify.numero, clientes_shopify.cep, clientes_shopify.bairro, clientes_shopify.complemento, clientes_shopify.empresa
        FROM pedidos_shopify
        LEFT JOIN clientes_shopify ON pedidos_shopify.customer_id = clientes_shopify.id_shopify
        WHERE pedidos_shopify.order_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $n_pedido);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pedido = $result->fetch_assoc();

        if ($pedido['financial_status'] == "paid") {
            $status_financeiro = "PAGO";
        } elseif ($pedido['financial_status'] == "paid_shopify") {
            $status_financeiro = "PAGO NA SHOPIFY";
        } elseif ($pedido['financial_status'] == "canceled") {
            $status_financeiro = "CANCELADO PELO OPERADOR";
        } else {
            $status_financeiro = "PENDENTE";
        }

        $nome_shopify = NOME_SHOPIFY;


        echo " 
            <button onclick='printPage()' class='btn btn-warning'>Imprimir Pedido</button>   
            <hr><br>
           ";
        echo "<div id='printArea'>";
        echo "<h2>Pedido Nº: {$pedido['n_pedido']}</h2>";
        echo "<h4>Informações do Pedido {$nome_shopify}</h4>";
        echo "<table class='table table-bordered'>";

        // Nome do Cliente (uma coluna)
        echo "<tr><th>Nome do Cliente</th><td colspan='3'>{$pedido['nome_cliente']}</td></tr>";

        // Endereço (uma coluna)
        echo "<tr><th>Endereço</th><td colspan='3'>"
            . htmlspecialchars($pedido['endereco'] ?? '') . ", "
            . ($pedido['numero'] ?? '') . ", "
            . htmlspecialchars($pedido['complemento'] ?? '') . ", Cep: "
            . ($pedido['cep'] ?? '') . ", "
            . htmlspecialchars($pedido['bairro'] ?? '') . " - "
            . htmlspecialchars($pedido['cidade'] ?? '') . " - "
            . htmlspecialchars($pedido['estado'] ?? '') . ", "
            . htmlspecialchars($pedido['pais'] ?? '') . "</td></tr>";

        // Demais informações em duas colunas
        echo "<tr>
                    <th>CPF</th><td>" . (!empty($pedido['cpf']) ? $pedido['cpf'] : "Não informado") . "</td>
                    <th>CNPJ</th><td>" . (!empty($pedido['cnpj']) ? $pedido['cnpj'] : "Não informado") . "</td>                     
                </tr>";
        echo "<tr><th>Empresa</th><td colspan='3'>" . (!empty($pedido['empresa']) ? $pedido['empresa'] : "Não informado") . "</td></tr>";
        echo "<tr>
                    <th>Pedido ID</th><td>{$pedido['order_id']}</td>
                    <th>Valor Total</th><td>R$ <b>" . number_format($pedido['total_price'], 2, ',', '.') . "</b></td>
                </tr>";
        echo "<tr>
                    <th>Status Financeiro</th><td>{$status_financeiro}</td>
                    <th>Data de Criação</th><td>" . date('d/m/Y H:i', strtotime($pedido['created_at'])) . "</td>
                </tr>";
        echo "<tr>
                    <th>Telefone</th><td>{$pedido['telefone']}</td>
                    <th>E-mail</th><td>{$pedido['email']}</td>
                </tr>";

        echo "</table>";

        $sql_itens = "SELECT * FROM itens_pedido_shopify WHERE order_id = ?";
        $stmt_itens = $conn->prepare($sql_itens);
        $stmt_itens->bind_param("s", $pedido['order_id']);
        $stmt_itens->execute();
        $result_itens = $stmt_itens->get_result();

        if ($result_itens->num_rows > 0) {
            echo "<h3>Itens do Pedido</h3>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>SKU</th><th>Produto</th><th>Quantidade</th><th>Preço Unitário</th><th>Total</th></tr></thead>";
            echo "<tbody>";

            while ($item = $result_itens->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$item['sku']}</td>";
                echo "<td>{$item['produto_nome']}</td>";
                echo "<td>{$item['quantidade']}</td>";
                echo "<td>R$ " . number_format($item['preco_unitario'], 2, ',', '.') . "</td>";
                echo "<td>R$ " . number_format($item['total'], 2, ',', '.') . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";

            // Exibe o total final do pedido diretamente da tabela
            echo "<h3>Total Geral: R$ " . number_format($pedido['total_price'], 2, ',', '.') . "</h3>";
            echo "<hr>";
            //echo "<p>Operador: ". $pedido['operador'] . "</p>";
            echo "<p>Observações:  " . $pedido['nota'] . "</p>";
            echo "<hr>";



        } else {
            echo "<div class='alert alert-info'>Nenhum item encontrado para este pedido.</div>";
        }

        // Adiciona o comprovante no rodapé (DENTRO da printArea)
        if (!empty($pedido['comprovante'])) {
            $comprovante = $pedido['comprovante'];
            $extensao = strtolower(pathinfo($comprovante, PATHINFO_EXTENSION));

            echo "<div style='text-align: center; margin-top: 20px;'>";
            echo "<h3>Comprovante de Pagamento</h3>";

            if ($extensao == 'jpg' || $extensao == 'jpeg' || $extensao == 'png' || $extensao == 'gif') {
                // Exibe imagem
                //  echo "<img src='{$comprovante}' alt='Comprovante de pagamento' style='max-width: 500px;'>";
                $imageData = base64_encode(file_get_contents($comprovante));
                $src = 'data:image/' . $extensao . ';base64,' . $imageData;
                echo "<img src='{$src}' alt='Comprovante de pagamento' style='max-width:300px;'>";

            } elseif ($extensao == 'pdf') {
                // Abre PDF em nova aba
                echo "<a href='{$comprovante}' target='_blank'>Visualizar Comprovante (PDF)</a>";
            } else {
                echo "<p>Formato de comprovante não suportado.</p>";
            }

            echo "</div>";
        }


        echo "</div>"; // Fechamento da printArea

    } else {
        echo "
        <div class='alert alert-info'>Detalhes do pedido {$n_pedido} não encontrados.</div>
        <a href='#' onclick='showUsersShopify()' class='btn btn-primary'>Voltar</a>
        ";
    }
} else {
    echo "<div class='alert alert-danger'>Número do pedido não fornecido.</div>";
}
?>

<script>
    function printPage() {
        var img = document.querySelector("#printArea img");

        if (img) {
            img.onload = function () {
                window.print();
            };

            // Se a imagem já estiver carregada, imprime imediatamente
            if (img.complete) {
                window.print();
            }
        } else {
            window.print();
        }
    }

    window.onload = function () {
        setTimeout(function () {
            window.print();
        }, 500);
    };

</script>