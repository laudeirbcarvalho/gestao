<?php
session_start();

require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');


//Registra o log
function registro_de_log($tipo, $mensagem)
{
    $arquivo_log = 'log_importacao.txt';
    $data_hora = date('Y-m-d H:i:s');
    $linha_log = "{$data_hora} - {$tipo}: {$mensagem}\n";
    file_put_contents($arquivo_log, $linha_log, FILE_APPEND);
}

// Função para verificar se a imagem já existe no servidor
function imageExists($path)
{
    return file_exists($path);
}

// Função para baixar imagem do produto

function downloadImage($url, $slug)
{
    $directory = '../../img/produtos/';

    // Verificar se o diretório existe, se não, criar
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    // Definir o caminho do arquivo com SKU_Adlux
    $path = $directory . '' . $slug . '.jpg';

    // Se a imagem já existir, retornar o caminho
    if (imageExists($path)) {
        return '/img/produtos/' . '' . $slug . '.jpg';
    }

    // Configurar o tempo máximo de execução para 60 segundos
    $context = stream_context_create([
        'http' => [
            'timeout' => 60 // Tempo máximo de espera em segundos
        ]
    ]);

    // Baixar a imagem
    $image_data = file_get_contents($url);
    if ($image_data === false) {
        echo "Erro ao baixar a imagem: $url";
        return null;
    }

    // Salvar a imagem no diretório
    file_put_contents($path, $image_data);

    return '/img/produtos/' . '' . $slug . '.jpg';
}

// Função para buscar produtos no WooCommerce
function getWooCommerceProducts()
{
    $woocommerce_url = 'https://compreadlux.com.br/wp-json/wc/v3/products';
    $consumer_key = 'ck_3044ff2e1cc56ef0a070cab67b1102e2b04cf910';
    $consumer_secret = 'cs_e2c82183112dd3c43ff466ae34318d4c5f35e185';

    $page = 1;
    $products = [];
    $per_page = 100;

    do {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $woocommerce_url . "?consumer_key=$consumer_key&consumer_secret=$consumer_secret&page=$page&per_page=$per_page",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            registro_de_log('WOOCOMMERCE_IMPORTAR_PRODUTOS_WOOCOMMERCE', "Erro ao acessar a API WooCommerce: $err");
            echo "Erro ao acessar a API WooCommerce: $err";
            return [];
        }

        $current_page_products = json_decode($response, true);
        if (!empty($current_page_products)) {
            $products = array_merge($products, $current_page_products);
            $page++;
        } else {
            break;
        }
    } while (count($current_page_products) === $per_page);

    return $products;
}


function listWooCommerceProducts()
{
    $woocommerce_products = getWooCommerceProducts();

    if (empty($woocommerce_products)) {
        registro_de_log('WOOCOMMERCE_IMPORTAR_PRODUTOS_WOOCOMMERCE' , "Nenhum produto encontrado");
        echo "<p class='text-danger'>Nenhum produto encontrado no WooCommerce.</p>";
        return;
    }

    $updated_count = 0;
    $inserted_count = 0;

    foreach ($woocommerce_products as $product) {
        try {
            $sku = $product['sku'] ?? 'N/A';
            $nproduto = $product['name'] ?? 'N/A';
            $price = $product['price'] ?? 0;
            $slug = $product['slug'] ?? '';
            $categories = !empty($product['categories']) ? implode(', ', array_column($product['categories'], 'name')) : 'Sem categoria';
            $stock = $product['stock_quantity'] ?? 0;
            $image_url = isset($product['images'][0]['src']) && filter_var($product['images'][0]['src'], FILTER_VALIDATE_URL) ? $product['images'][0]['src'] : '/img/sem_imagem.jpg';
            $statusw = $product['status'];
            $tags = !empty($product['tags']) ? implode(', ', array_column($product['tags'], 'name')) : 'Sem tags';

            $image_url = isset($product['images'][0]['src']) && filter_var($product['images'][0]['src'], FILTER_VALIDATE_URL)
                ? $product['images'][0]['src']
                : null;
            
            //Trata o nome do produto para inserir o mesmo nome na imagem.
            // Remove acentos e caracteres especiais
            $slug_imagem = iconv('UTF-8', 'ASCII//TRANSLIT', $nproduto);    
            // Substitui espaços e caracteres inválidos por "_"
            $slug_imagem = preg_replace('/[^A-Za-z0-9]/', '_', $slug_imagem);
            // Remove múltiplos "_" consecutivos
            $slug_imagem = preg_replace('/_+/', '_', $slug_imagem);
            // Remove "_" do início e fim da string
            $slug_imagem = trim($slug_imagem, '_');

            // Só baixa a imagem se `image_url` for um link válido
            $image = $image_url ? downloadImage($image_url, $slug_imagem) : '/img/sem_imagem.jpg';

            global $conn;
            $sql_check = "SELECT * FROM produtos WHERE sku = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $sku);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if ($result->num_rows > 0) {
                $sql_update = "UPDATE produtos SET nomeproduto=?, preco_cd=?, estoque=?, categorias=?, status=?, imagem=?, slug=?, tags=?, atualizado_em=NOW() WHERE sku=?";
                $stmt_update = $conn->prepare($sql_update);

                // "sdisssss" agora tem 8 parâmetros para 8 variáveis
                $stmt_update->bind_param("sdissssss", $nproduto, $price, $stock, $categories, $statusw, $image, $slug, $tags, $sku);
                $stmt_update->execute();
                $updated_count++;
            } else {
                $image = $image ?? '/img/sem_imagem.jpg'; // Define um valor padrão caso seja NULL

                $sql_insert = "INSERT INTO produtos (sku, nomeproduto, preco_cd, estoque, categorias, status, imagem, slug, tags, atualizado_em) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssdisssss", $sku, $nproduto, $price, $stock, $categories, $statusw, $image, $slug, $tags);


                $stmt_insert->execute();
                $inserted_count++;

                registro_de_log('WOOCOMMERCE_IMPORTAR_PRODUTOS_WOOCOMMERCE' , "Produto SKU: $sku. Importado.");
            }

        } catch (Exception $e) {
            registro_de_log('WOOCOMMERCE_IMPORTAR_PRODUTOS_WOOCOMMERCE' , "Erro ao processar SKU: $sku. Mensagem:" . $e->getMessage() );

            echo "Erro ao processar SKU: $sku. Mensagem: " . $e->getMessage();
        }
    }

    echo "<p class='text-success'>Operação concluída com sucesso!</p>";
    echo "<p class='text-info'>Produtos atualizados: $updated_count</p>";
    echo "<p class='text-info'>Produtos inseridos: $inserted_count</p>";
}


// Função para listar os produtos na tabela
function displayProducts()
{
    global $conn;
    $sql = "SELECT sku, nomeproduto, preco_cd, estoque, categorias, status, imagem, slug FROM produtos";
    $result = $conn->query($sql);

    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>SKU</th><th>Nome Produto</th><th>Preço CD</th><th>Preço Salão</th><th>Estoque</th><th>Categorias</th><th>Status</th><th>Imagem</th><th>Slug</th></tr></thead><tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['sku'] . "</td>";
        echo "<td>" . $row['nomeproduto'] . "</td>";
        echo "<td>" . $row['preco_cd'] . "</td>";
        echo "<td>" . $row['estoque'] . "</td>";
        echo "<td>" . $row['categorias'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td><img src='" . $row['imagem'] . "' alt='Imagem do Produto' style='width: 50px;'></td>";
        echo "<td>" . $row['slug'] . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
}



// Executar as funções
listWooCommerceProducts(); // Atualizar/inserir produtos
//displayProducts(); // Listar os produtos no banco de dados
