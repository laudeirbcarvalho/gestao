<?php
// Inclui o arquivo de conexão com o banco de dados caminho absoluto
require_once('../../sistema/db.php');

echo PATH;

 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0); // Remove o limite de tempo de execução

// Função para registrar logs no arquivo cron_log.txt
function log_message($message) {
    $log_file = PATH . 'sistema/cron/cron_log.txt'; // Caminho absoluto para o arquivo de log
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}


// Shopify credentials
$shopify_store = SHOPIFY_STORE;
$access_token = SHOPIFY_TOKEN;
$limit = 500; // Máximo de produtos a serem recuperados do WooCommerce
$central_id = $sessao_central;


// Função para buscar todos os produtos no WooCommerce
function getWooCommerceProducts()
{
    // WooCommerce credentials
    $woocommerce_url = URL_WOO . 'products'; 
    $consumer_key = KEY_WOO;
    $consumer_secret = SECRET_WOO;

    $page = 1;
    $products = [];
    $per_page = 80;

    do {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $woocommerce_url . '?consumer_key=' . $consumer_key . '&consumer_secret=' . $consumer_secret . '&page=' . $page . '&per_page=' . $per_page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            log_message("Erro ao acessar a API WooCommerce 1: $err");
            return [];
        } else {
            $current_page_products = json_decode($response, true);
            if (!empty($current_page_products)) {
                $products = array_merge($products, $current_page_products);
                $page++;
            } else {
                break;
            }
        }
    } while (count($current_page_products) === $per_page);

    return $products;
}

// Função para ajustar o SKU
function ajustar_sku($sku)
{
    return str_pad($sku, 3, '0', STR_PAD_LEFT); // Adiciona zeros à esquerda até o SKU ter 3 dígitos
}

// Função para obter o `location_id` no Shopify
function getLocationId($shopify_store, $access_token)
{
    $url = "https://$shopify_store/admin/api/2023-01/locations.json";
    $headers = [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        log_message("Erro ao acessar a API Shopify (Location): $err");
        throw new Exception("Erro ao acessar a API Shopify (Location): $err");
    } else {
        $data = json_decode($response, true);
        if (isset($data['locations']) && !empty($data['locations'])) {
            return $data['locations'][0]['id'];  // Retorna o primeiro location_id encontrado
        } else {
            log_message("Nenhum location_id encontrado no Shopify.");
            throw new Exception("Nenhum location_id encontrado no Shopify.");
        }
    }
}

// Função para ajustar o inventário no Shopify
function adjustInventory($shopify_store, $access_token, $inventory_item_id, $location_id, $new_quantity)
{
    $url = "https://$shopify_store/admin/api/2023-01/inventory_levels/set.json";
    $data = json_encode([
        'location_id' => $location_id,
        'inventory_item_id' => $inventory_item_id,
        'available' => $new_quantity
    ]);

    $headers = [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        log_message("Erro ao ajustar o inventário no Shopify: $err");
        throw new Exception("Erro ao ajustar o inventário no Shopify: $err");
    } else {
        log_message("Inventário ajustado no Shopify: " . print_r($response, true));
    }
}

// Função para ajustar o inventário do Shopify em lotes
function adjustShopifyInventory($shopify_store, $access_token, $limit, $batch_size = 10): void
{
    global $conn;

    // Obter produtos do WooCommerce
    $woocommerce_products = getWooCommerceProducts();
    $woo_sku_stock = [];

    // Criar um array associativo com SKU e quantidade de estoque no WooCommerce
    foreach ($woocommerce_products as $product) {
        if (!empty($product['sku'])) {
            $woo_sku_stock[$product['sku']] = $product['stock_quantity'];
        }
    }

    // Paginar consultas GraphQL
    $cursor = null;
    do {
        // Query GraphQL para buscar os produtos no Shopify
        $query = json_encode([
            'query' => '{
                products(first: 250, after: ' . ($cursor ? '"' . $cursor . '"' : 'null') . ') {
                    edges {
                        cursor
                        node {
                            id
                            title
                            variants(first: 1) {
                                edges {
                                    node {
                                        sku
                                        inventoryItem {
                                            id
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }'
        ]);

        $url = "https://$shopify_store/admin/api/2023-01/graphql.json";
        $headers = [
            "Content-Type: application/json",
            "X-Shopify-Access-Token: $access_token"
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            log_message("Erro ao acessar a API Shopify adjustShopifyInventory: $err");
            throw new Exception("Erro ao acessar a API Shopify: $err");
        } else {
            $data = json_decode($response, true);
            if (isset($data['errors'])) {
                $error_messages = array_column($data['errors'], 'message');
                throw new Exception("Erro na query GraphQL: " . implode(', ', $error_messages));
            }

            $products = $data['data']['products']['edges'] ?? [];
            $cursor = end($products)['cursor'] ?? null;

            // Array para armazenar as atualizações em lotes
            $update_batches = [];
            $cont = 1;

            // Iterar sobre os produtos do Shopify
            foreach ($products as $product) {
                $shopify_sku = $product['node']['variants']['edges'][0]['node']['sku'];

                // Ajustar o SKU do Shopify para 3 dígitos, se necessário
                $shopify_sku_ajustado = ajustar_sku($shopify_sku);

                // Extrair apenas o número do ID de inventário (remover o prefixo gid://shopify/InventoryItem/)
                $inventory_item_id_full = $product['node']['variants']['edges'][0]['node']['inventoryItem']['id'];
                $inventory_item_id = preg_replace('/^gid:\/\/shopify\/InventoryItem\//', '', $inventory_item_id_full);

                // Verificar se o SKU ajustado existe no WooCommerce
                if (isset($woo_sku_stock[$shopify_sku_ajustado])) {
                    $new_quantity = $woo_sku_stock[$shopify_sku_ajustado]; // Quantidade do WooCommerce
                    $data_atual = date('Y-m-d H:i:s');

                    // 🔹 Verificar o estoque atual no banco de dados
                    $query_estoque = $conn->prepare("SELECT estoque FROM produtos WHERE sku = ?");
                    $query_estoque->bind_param("s", $shopify_sku_ajustado);
                    $query_estoque->execute();
                    $result_estoque = $query_estoque->get_result();
                    $estoque_atual = $result_estoque->fetch_assoc()['estoque'] ?? null;
                    $query_estoque->close();

                    // 🔹 Comparar o estoque do WooCommerce com o banco de dados
                    if ($estoque_atual !== null && $estoque_atual != $new_quantity) {
                        // 🔹 Atualizar o estoque no Shopify
                        $location_id = getLocationId($shopify_store, $access_token);
                        $update_batches[] = [
                            'inventory_item_id' => $inventory_item_id,
                            'location_id' => $location_id,
                            'available' => $new_quantity
                        ];

                        log_message("Estoque preparado para SKU: $shopify_sku_ajustado com quantidade: $new_quantity");

                        // 🔹 Atualizar o banco de dados para `produtos` e `precos_e_estoques`
                        try {
                            // 🔹 Atualizar tabela `produtos`
                            $query_produtos = $conn->prepare("UPDATE produtos SET atualizado_em_shopify = ?, estoque = ? WHERE sku = ?");
                            $query_produtos->bind_param("sis", $data_atual, $new_quantity, $shopify_sku_ajustado);
                            $query_produtos->execute();
                            $query_produtos->close();

                            // 🔹 Atualizar tabela `precos_e_estoques` (com central_id)
                            $query_precos = $conn->prepare("UPDATE precos_e_estoques SET ultima_atualizacao = ?, estoque = ? WHERE sku = ? AND central_id = ?");
                            $query_precos->bind_param("ssss", $data_atual, $new_quantity, $shopify_sku_ajustado, $central_id);
                            $query_precos->execute();
                            $query_precos->close();

                            log_message("Estoque atualizado no banco de dados para SKU: $shopify_sku_ajustado");

                        } catch (Exception $e) {
                            log_message("Erro ao atualizar o banco de dados: " . $e->getMessage());
                        }
                    } else {
                        // 🔹 Registrar no log que não houve alteração no estoque
                        log_message("SKU $shopify_sku_ajustado: Estoque não alterado (WooCommerce: $new_quantity, Banco de Dados: $estoque_atual).");
                    }
                } else {
                    log_message("SKU $shopify_sku_ajustado não encontrado no WooCommerce");
                }

                // Enviar atualizações em lotes quando o número de itens atingir o limite do lote
                if (count($update_batches) >= $batch_size) {
                    foreach ($update_batches as $update) {
                        adjustInventory($shopify_store, $access_token, $update['inventory_item_id'], $update['location_id'], $update['available']);

                        // Adiciona um delay de 1 segundo entre as requisições
                        sleep(1);
                    }
                    $update_batches = []; // Limpar o array após a atualização
                }
                $cont++;
            }

            // Enviar quaisquer atualizações restantes que não atingiram o tamanho do lote
            if (!empty($update_batches)) {
                foreach ($update_batches as $update) {
                    adjustInventory($shopify_store, $access_token, $update['inventory_item_id'], $update['location_id'], $update['available']);

                    // Adiciona um delay de 1 segundo entre as requisições
                    sleep(1);
                }
            }
        }
    } while ($cursor); // Continua enquanto houver mais produtos para buscar
}

// Executar a função
try {
    adjustShopifyInventory($shopify_store, $access_token, $limit);
    log_message("Script Estoque Shopify executado para " . CENTRAL_NOME . ".");
} catch (Exception $e) {
    log_message("ERRO: Script Estoque Shopify executado para " . CENTRAL_NOME . ". Erro: " . $e->getMessage());
}
 