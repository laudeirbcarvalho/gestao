<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
 
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
function adjustTitleAndDescription($shopify_store, $access_token, $product_id, $new_title, $new_description, $category)
{
    $url = "https://$shopify_store/admin/api/2023-01/products/$product_id.json";

    $data = json_encode([
        'product' => [
            'id' => $product_id,
            'title' => $new_title,
            'body_html' => $new_description,
            'product_type' => $category
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
    $url = "https://$shopify_store/admin/api/2023-01/variants.json?fields=id,product_id,sku,inventory_item_id&limit=250";
    $headers = ["Content-Type: application/json", "X-Shopify-Access-Token: $access_token"];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    


    curl_close($curl);

    $variants = json_decode($response, true);

    if (isset($variants['variants'])) {
        foreach ($variants['variants'] as $variant) {
            if ($variant['sku'] === $sku) {
                return [
                    'product_id' => $variant['product_id'],
                    'variant_id' => $variant['id'],
                    'inventory_item_id' => $variant['inventory_item_id']
                ];
            }
        }
    }

    return null; // Retorna null se o SKU não for encontrado
}


 
// Função para criar um produto no Shopify
function createProductOnShopify($shopify_store, $access_token, $title, $description, $price, $sku, $image_url)
{
    $url = "https://$shopify_store/admin/api/2023-01/products.json";
    $data = json_encode([
        'product' => [
            'title' => $title,
            'body_html' => $description,
            'variants' => [['price' => $price, 'sku' => $sku]],
            'images' => [['src' => $image_url]]
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
    
  
    
    if (isset($_POST['skus'])) { // A chave é 'skus', pois você está enviando um array
        $skus = $_POST['skus']; // Recupera o array de SKUs
        $central_id = $sessao_central;

        foreach ($skus as $sku) {
            $sku_ajustado = ajustar_sku($sku); // Ajusta o SKU

           
            // Buscar produto e estoque no banco de dados pelo SKU
            $query = $conn->prepare("
                SELECT 
                    pe.preco_salao, 
                    pe.estoque, 
                    p.nomeproduto, 
                    p.descricao, 
                    p.imagem, 
                    p.categorias 
                FROM precos_e_estoques pe
                JOIN produtos p ON p.sku = pe.sku
                WHERE pe.sku = ? AND pe.central_id = ?
            ");
            $query->bind_param("si", $sku_ajustado, $central_id);
            $query->execute();
            $result = $query->get_result();

          

            if ($result->num_rows > 0) {
                $produto = $result->fetch_assoc();
                $new_price = $produto['preco_salao'];

               

                $new_quantity = $produto['estoque'];
                $new_title = $produto['nomeproduto'];
                $new_description = $produto['descricao'];
                $base_url = URL;
                $image_url = $base_url . $produto['imagem']; // URL da imagem que será associada ao produto
                $category = $produto['categorias'];
                // Buscar produto no Shopify pelo SKU
                $product_data = getShopifyProductBySku($shopify_store, $access_token, $sku_ajustado);

                //Se não exixtir o produto na shopify aqui cria um novo
                if ($product_data == "sem produto") {
                    $created_product = createProductOnShopify($shopify_store, $access_token, $new_title, $new_description, $new_price, $sku_ajustado, $image_url, $category);
                    if (!isset($created_product['product']['id'])) {
                        echo "<div class='alert alert-danger mt-3'>Erro ao criar produto SKU $sku_ajustado no Shopify.</div>";
                        continue; // Para passar ao próximo SKU
                    } else {
                        echo "<div class='alert alert-success mt-3'>Produto SKU $sku_ajust criado com sucesso no Shopify.</div>";
                    }
                    $product_data = [
                        'id' => $created_product['product']['id'],
                        'variant_id' => $created_product['product']['variants'][0]['id'],
                        'inventory_item_id' => $created_product['product']['variants'][0]['inventory_item_id']
                    ];

                    // Recuperar o location_id para definir o inventário
                    $location_id = getLocationId($shopify_store, $access_token);

                 

                    // Criar o inventário logo após criar o produto
                    $response_inventory = adjustInventory($shopify_store, $access_token, $product_data['inventory_item_id'], $location_id, $new_quantity);
                    echo "<div class='alert alert-success mt-3' role='alert'>
          <i class='fa fa-check'></i> Inventário de $new_quantity unidades para o novo produto SKU $sku_ajustado , criado na shopify.<br>";

                    // Ativar o rastreamento de quantidade
                    enableInventoryTracking($shopify_store, $access_token, $product_data['inventory_item_id']);
                    echo "<div class='alert alert-success mt-3' role='alert'>
          <i class='fa fa-check'></i> Produto criado com inventário de $new_quantity unidades para o SKU $sku_ajustado e rastreamento ativado.<br>";
                }

                $inventory_item_id = $product_data['inventory_item_id'] ?? null;
                $variant_id = $product_data['variant_id'] ?? null;
                $product_id = $product_data['id'] ?? null;
                $location_id = getLocationId($shopify_store, $access_token);

                // Ajustar inventário no Shopify
                adjustInventory($shopify_store, $access_token, $inventory_item_id, $location_id, $new_quantity);
                echo "<div class='alert alert-success mt-3' role='alert'>
                ID do Produto na Loja: <b> $product_id </b> <br>
                <i class='fa fa-check'></i> Inventário ajustado para $new_quantity unidades para o SKU $sku_ajustado.<br>";

                // Ajustar preço no Shopify
                
                adjustPrice($shopify_store, $access_token, $variant_id, $new_price);
                echo "<i class='fa fa-check'></i> Preço ajustado para R$ $new_price.<br>";

                // Ajustar título e descrição no Shopify
                 adjustTitleAndDescription($shopify_store, $access_token, $product_id, $new_title, $new_description, $category);
                echo "<i class='fa fa-check'></i> Título e descrição ajustados. E categoria ($category).<br>";

                //Pega o id da imagem na shopify
                $a = adjustPrice($shopify_store, $access_token, $variant_id, $new_price);
                $data = json_decode($a, true); // Decodifica a resposta JSON em um array associativo
                $product_id = $data['variant']['product_id']; // Pega o product_id
                
                // Adicionar imagem ao produto no Shopify
                 addProductImage($shopify_store, $access_token, $product_id, $image_url);
                 

                echo "<i class='fa fa-check'></i> Imagem adicionada ao produto.<br></div>";

                // Atualizar o campo 'atualizado_em_shopify' após atualização do inventário
                $data_atual = date('Y-m-d H:i:s');
                $query_atualizar = $conn->prepare("UPDATE produtos SET atualizado_em_shopify = ? WHERE sku = ?");
                $query_atualizar->bind_param("ss", $data_atual, $sku_ajustado);
                $query_atualizar->execute();
                $query_atualizar->close();
            }
        }
    }
}
