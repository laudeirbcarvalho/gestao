<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
// Configuração da API da Shopify
$nome_lojaMatriz = NOME_SHOPIFY;
$shopify_storeMatriz = SHOPIFY_STORE;
$access_tokenMatriz = SHOPIFY_TOKEN;

// Função para importar os pedidos da Shopify
function importar_pedidos_shopify()
{
    global $conn, $shopify_storeMatriz, $access_tokenMatriz;

    $url = "https://$shopify_storeMatriz/admin/api/2023-04/orders.json?limit=250";
    $headers = array(
        "X-Shopify-Access-Token: $access_tokenMatriz",
        "Content-Type: application/json"
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($http_code == 200) {
        $orders = json_decode($response, true);
        $html = '<h5 class="text-center alert  alert-success">Pedidos Importados</h5>';
        $html .= "<table class='table table-striped table-hover'>";
        $html .= "<thead><tr><th>Pedido</th><th>Cliente</th><th>Valor Total</th><th>Status</th><th>Data</th></tr></thead>";
        $html .= "<tbody>";

        $newOrders = 0; // Contador de novos pedidos

        //echo '<pre>';
        //print_r($orders);

        foreach ($orders['orders'] as $order) {
            // Verifica se o número do pedido já existe no banco de dados
            $sql = "SELECT * FROM pedidos_shopify WHERE n_pedido = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $order['name']);
            $stmt->execute();
            $result = $stmt->get_result();

            $n_pedido = $order['name'];
            $order_id = $order['id'];
            $customer_id = $order['customer']['id'];
            $total_price = $order['total_price'];
            $financial_status = $order['financial_status'];

            //Se for pago na shopify ai identifica como pago na shopify
            if($financial_status==='paid'){
                $financial_status = 'paid_shopify';
            }

            $created_at = $order['created_at'];
            $operador = $_SESSION['nome'];
            $data_import = date('Y-m-d H:i:s');

            if ($result->num_rows == 0) {
                $sql = "INSERT INTO pedidos_shopify (n_pedido, order_id, customer_id, total_price, financial_status, created_at, operador, data_import)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssss", $n_pedido, $order_id, $customer_id, $total_price, $financial_status, $created_at, $operador, $data_import);
                $stmt->execute();

                // Inserção dos itens do pedido
                foreach ($order['line_items'] as $item) {
                    $sql_item = "INSERT INTO itens_pedido_shopify (order_id, item_id, produto_nome, quantidade, preco_unitario, total, variant_id, sku)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_item = $conn->prepare($sql_item);

                    $item_id = $item['id'];
                    $produto_nome = $item['name'];
                    $quantidade = $item['quantity'];                     
                    $variant_id = $item['variant_id'];
                    $sku = $item['sku'];

                    $preco_unitario = round((float) $item['price'], 2);
                    $total_item = round((float) ($item['price'] * $item['quantity']), 2);

                    $stmt_item->bind_param("iisiddis", $order_id, $item_id, $produto_nome, $quantidade, $preco_unitario, $total_item, $variant_id, $sku);
                    $stmt_item->execute();
                }


                // Exibição na tabela HTML
                $html .= "<tr>";
                $html .= "<td>" . $order['name'] . "</td>";
                $html .= "<td>{$order['customer']['first_name']} {$order['customer']['last_name']}</td>";
                $html .= "<td>R$ " . number_format($order['total_price'], 2, ',', '.') . "</td>";
                $html .= "<td><span class='badge badge-" . ($order['financial_status'] == 'paid' ? 'success' : 'warning') . "'>" . ucfirst($order['financial_status']) . "</span></td>";
                $html .= "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
                $html .= "</tr>";
                $newOrders++;
            }
        }


        $html .= "</tbody>";
        $html .= "</table>";

        if ($newOrders == 0) {
            $html = "
            <div class='alert alert-info'>Não há novos pedidos para importar.</div>
            <a href='#' onclick='showUsersShopify()' class='btn btn-primary'>Voltar</a>
            ";
        }

        return $html;
    } else {
        return "<div class='alert alert-danger'>Erro ao importar os pedidos: $http_code</div>";
    }
}

echo importar_pedidos_shopify();