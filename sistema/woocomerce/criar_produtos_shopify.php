<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit();
}

// Shopify credentials
$shopify_store = SHOPIFY_STORE;
$access_token = SHOPIFY_TOKEN;

// Função para ajustar o SKU (opcional)
function ajustar_sku($sku)
{
    // Verifica se o SKU é válido antes de aplicar str_pad
    if ($sku !== null) {
        return str_pad($sku, 3, '0', STR_PAD_LEFT); // Adiciona zeros à esquerda até ter 3 dígitos
    }
    return $sku; // Retorna uma string vazia se o SKU for nulo
}

// Função para obter location_id no Shopify
function getLocationId($shopify_store, $access_token)
{
    $url = "https://$shopify_store/admin/api/2023-01/locations.json";
    $headers = ["Content-Type: application/json", "X-Shopify-Access-Token: $access_token"];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['locations'][0]['id'] ?? null;
}

// Função para ajustar inventário no Shopify
function adjustInventory($shopify_store, $access_token, $inventory_item_id, $location_id, $new_quantity)
{
    $url = "https://$shopify_store/admin/api/2023-01/inventory_levels/set.json";
    $data = json_encode(['location_id' => $location_id, 'inventory_item_id' => $inventory_item_id, 'available' => $new_quantity]);
    $headers = ["Content-Type: application/json", "X-Shopify-Access-Token: $access_token"];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// Função para ajustar título e descrição no Shopify
function adjustTitleAndDescription($shopify_store, $access_token, $product_id, $new_title, $new_description, $tags, $category, $slug)
{
    $url = "https://$shopify_store/admin/api/2023-01/products/$product_id.json";

    $data = json_encode([
        'product' => [
            'id' => $product_id,
            'title' => $new_title,
            'body_html' => $new_description,
            'tags' => $tags, // Tags do produto (separadas por vírgula)
            'product_type' => $category,
            'handle' => $slug
        ]
    ]);

    $headers = [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}

// Função para ajustar preço no Shopify
function adjustPrice($shopify_store, $access_token, $variant_id, $new_price)
{
    $url = "https://$shopify_store/admin/api/2023-01/variants/$variant_id.json";
    $data = json_encode(['variant' => ['id' => $variant_id, 'price' => $new_price, 'compare_at_price' => 0]]);
    $headers = ["Content-Type: application/json", "X-Shopify-Access-Token: $access_token"];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// Função para adicionar imagem ao produto no Shopify
function addProductImage($shopify_store, $access_token, $product_id, $image_url)
{
    $url = "https://$shopify_store/admin/api/2023-01/products/$product_id/images.json";
    $data = json_encode([
        'image' => [
            'src' => $image_url
        ]
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
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Obtém o código HTTP da resposta
    curl_close($curl);

    // Verifique o status da resposta
    if ($http_code == 201) {
        return json_decode($response, true); // Imagem adicionada com sucesso
    } else {
        return ['error' => 'Falha ao adicionar a imagem', 'response' => $response];
    }
}

// Função para obter produto no Shopify pelo SKU
function getShopifyProductBySku($shopify_store, $access_token, $sku)
{
    $url = "https://$shopify_store/admin/api/2023-01/products.json?fields=id,variants&limit=250";
    $headers = [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ];

    do {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        $products = json_decode($response, true);

        if (isset($products['products'])) {
            foreach ($products['products'] as $product) {
                foreach ($product['variants'] as $variant) {
                    if ($variant['sku'] === $sku) {
                        return [
                            'id' => $product['id'],
                            'variant_id' => $variant['id'],
                            'inventory_item_id' => $variant['inventory_item_id']
                        ];
                    }
                }
            }
        }

        // Shopify paginação (verifique se há próxima página)
        $url = isset($products['link']['next']) ? $products['link']['next'] : null;
    } while ($url);

    return null; // Retorna null se o SKU não for encontrado
}



// Função para criar um produto no Shopify
function createProductOnShopify($shopify_store, $access_token, $title, $description, $price, $sku, $image_url, $supplier, $category, $tags, $slug)
{
    $url = "https://$shopify_store/admin/api/2023-01/products.json";
    $data = json_encode([
        'product' => [
            'title' => $title,
            'body_html' => $description,
            'variants' => [['price' => $price, 'sku' => $sku]],
            'images' => [['src' => $image_url]],
            'vendor' => $supplier,
            'tags' => $tags,
            'product_type' => $category,
            'handle' => $slug
        ]
    ]);
    $headers = ["Content-Type: application/json", "X-Shopify-Access-Token: $access_token"];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

// Função para ativar o rastreamento de inventário
function enableInventoryTracking($shopify_store, $access_token, $inventory_item_id)
{
    $url = "https://$shopify_store/admin/api/2023-01/inventory_items/$inventory_item_id.json";
    $data = [
        'inventory_item' => [
            'id' => $inventory_item_id,
            'tracked' => true
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . $access_token
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Receber os SKUs e URLs de imagens via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['skus'])) {
        $skus = $_POST['skus'];
        $log = [];

        $central_id = $sessao_central;

        foreach ($skus as $sku) {
            $sku_ajustado = ajustar_sku($sku);

            // Verificar se o SKU existe na tabela `precos_e_estoques`
            $query_check = $conn->prepare("SELECT COUNT(*) as total FROM precos_e_estoques WHERE sku = ? AND central_id = ?");
            $query_check->bind_param("si", $sku_ajustado, $central_id);
            $query_check->execute();
            $result_check = $query_check->get_result();
            $row_check = $result_check->fetch_assoc();

            if ($row_check['total'] == 0) {
                // Inserir SKU com valores padrão caso não exista
                $query_insert = $conn->prepare("INSERT INTO precos_e_estoques (sku, central_id, preco_salao, estoque) VALUES (?, ?, 0, 0)");
                $query_insert->bind_param("si", $sku_ajustado, $central_id);
                $query_insert->execute();
                $query_insert->close();
            }

            // Agora buscar produto no banco de dados
            $query = $conn->prepare("
                SELECT 
                    pe.preco_salao, 
                    pe.estoque, 
                    p.nomeproduto, 
                    p.descricao, 
                    p.imagem, 
                    p.categorias, 
                    p.tags, 
                    p.slug 
                FROM precos_e_estoques pe
                JOIN produtos p ON p.sku = pe.sku
                WHERE p.sku = ? AND pe.central_id = ?
            ");
            $query->bind_param("si", $sku_ajustado, $central_id);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                $produto = $result->fetch_assoc();

                $log[$sku_ajustado] = [
                    'nomeproduto' => $produto['nomeproduto'],
                    'preco' => $produto['preco_salao'],
                    'estoque' => $produto['estoque'],
                    'status' => []
                ];

                // Definir variáveis
                $new_price = $produto['preco_salao'];
                $new_quantity = $produto['estoque'];
                $new_title = $produto['nomeproduto'];
                $new_description = $produto['descricao'];
                $base_url = URL;
                $image_url = $base_url . $produto['imagem'];
                $category = $produto['categorias'];
                $tags = $produto['tags'];
                $slug = $produto['slug'];

                // Buscar produto no Shopify
                $product_data = getShopifyProductBySku($shopify_store, $access_token, $sku_ajustado);

                if (!$product_data) {
                    // Criar produto no Shopify se não existir
                    $created_product = createProductOnShopify(
                        $shopify_store,
                        $access_token,
                        $new_title,
                        $new_description,
                        $new_price,
                        $sku_ajustado,
                        $image_url,
                        $supplier,
                        $category,
                        $tags,
                        $slug
                    );

                    $nomeshopify = NOME_SHOPIFY;
                    if (!isset($created_product['product']['id'])) {
                        $log[$sku_ajustado]['status'][] = "Erro ao criar produto no $nomeshopify.";
                        continue;
                    }

                    $log[$sku_ajustado]['status'][] = "Produto criado no $nomeshopify.";

                    $product_data = [
                        'id' => $created_product['product']['id'],
                        'variant_id' => $created_product['product']['variants'][0]['id'],
                        'inventory_item_id' => $created_product['product']['variants'][0]['inventory_item_id']
                    ];

                    $location_id = getLocationId($shopify_store, $access_token);

                    // Ajustar inventário inicial
                    adjustInventory($shopify_store, $access_token, $product_data['inventory_item_id'], $location_id, $new_quantity);
                    $log[$sku_ajustado]['status'][] = "Inventário inicial ajustado para $new_quantity unidades.";

                    enableInventoryTracking($shopify_store, $access_token, $product_data['inventory_item_id']);
                    $log[$sku_ajustado]['status'][] = "Rastreamento de inventário ativado.";
                }

                // Atualizar produto existente
                $inventory_item_id = $product_data['inventory_item_id'];
                $variant_id = $product_data['variant_id'];
                $product_id = $product_data['id'];
                $location_id = getLocationId($shopify_store, $access_token);

                adjustInventory($shopify_store, $access_token, $inventory_item_id, $location_id, $new_quantity);
                $log[$sku_ajustado]['status'][] = "Inventário ajustado para $new_quantity unidades.";

                adjustPrice($shopify_store, $access_token, $variant_id, $new_price);
                $log[$sku_ajustado]['status'][] = "Preço ajustado para R$ $new_price.";

                adjustTitleAndDescription($shopify_store, $access_token, $product_id, $new_title, $new_description, $tags, $category, $slug);
                $log[$sku_ajustado]['status'][] = "Título e descrição atualizados.";

                addProductImage($shopify_store, $access_token, $product_id, $image_url);
                $log[$sku_ajustado]['status'][] = "Imagem adicionada ao produto.";

                // Atualizar `atualizado_em_shopify`
                $data_atual = date('Y-m-d H:i:s');
                $query_atualizar = $conn->prepare("UPDATE produtos SET atualizado_em_shopify = ? WHERE sku = ?");
                $query_atualizar->bind_param("ss", $data_atual, $sku_ajustado);
                $query_atualizar->execute();
                $query_atualizar->close();
            } else {
                $log[$sku_ajustado]['status'][] = "Produto não encontrado no banco de dados.";
            }
        }

        // Exibir log
        foreach ($log as $sku => $info) {
            echo "<div class='alert alert-info'>";
            echo "Produto SKU: $sku<br>";
            echo "Nome: " . ($info['nomeproduto'] ?? 'Não encontrado') . "<br>";
            echo "Preço: R$ " . ($info['preco'] ?? '0') . "<br>";
            echo "Estoque: " . ($info['estoque'] ?? '0') . " unidades<br>";
            echo "Status:<br><ul>";
            foreach ($info['status'] as $status) {
                echo "<li>$status</li>";
            }
            echo "</ul></div>";
        }
    }
}


?>