<?php
require_once('sistema/db.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit();
}

// Função para buscar produtos no WooCommerce
function getWooCommerceProducts()
{
    $woocommerce_url = URL_WOO.'/products';
    $consumer_key = KEY_WOO;
    $consumer_secret = SECRET_WOO;

    $nomewoo = NOME_WOO;

    $page = 1;
    $products = [];
    $per_page = 100;

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
            echo "Erro ao acessar a API $nomewoo: " . $err;
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

// Função para listar produtos
function listWooCommerceProducts()
{
    $woocommerce_products = getWooCommerceProducts();

    if (empty($woocommerce_products)) {
        echo "<p class='text-danger'>Nenhum produto encontrado no $nomewoo.</p>";
        return;
    }

    // Cabeçalho HTML com Bootstrap
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos $nomewoo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Lista de Produtos Atualizados</h1>
    <input id="search" class="form-control mb-3" placeholder="Buscar produtos...">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>SKU</th>
                <th>Imagem</th>
                <th>Nome</th>
                <th>CD</th>
                <th>Salão</th>
                <th>Estoque</th>
                <th>Categorias</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="product-table">
HTML;

    // Arredondar valor
    function roundUp($number, $decimals)
    {
        $factor = pow(10, $decimals);
        return ceil($number * $factor) / $factor;
    }

    // Iterar sobre os produtos
    foreach ($woocommerce_products as $product) {
        $sku = isset($product['sku']) ? $product['sku'] : 'N/A';
        $name = isset($product['name']) ? $product['name'] : 'N/A';
        $price = isset($product['price']) ? $product['price'] : 'N/A';
        $slug = isset($product['slug']) ? $product['slug'] : '';
        $categories = !empty($product['categories']) ? array_column($product['categories'], 'name') : [];

        if (in_array('Linha Profissional', $categories) || in_array('Kit Profissional', $categories)) {
            $porcentagem_aumento = 3.4444; // Aumento para esses slugs
            $pricesalao = roundUp($price * (1 + $porcentagem_aumento), 2) / 2;


        } else {
            $porcentagem_aumento = 2.020165289256198; // Aumento padrão
            $pricesalao = roundUp($price * (1 + $porcentagem_aumento), 2) / 2;

        }




        $stock = isset($product['stock_quantity']) ? $product['stock_quantity'] : 'N/A';
        $image = $product['images'][0]['src'] ?? 'https://via.placeholder.com/100';
        $statusw = $product['status'];
        $categories = !empty($product['categories']) ? implode(', ', array_column($product['categories'], 'name')) : 'Sem categoria';

        if (in_array($categories, ['Material de Apoio', 'Material de Apoio, Promoções', 'Banner Linha Home Care', 'Banner Linha Profissional', 'Banners Linha Profissional'])) {
            $pricesalao = $price;
        }

        $price = number_format($price, 2, '.', '');
        $pricesalao = number_format($pricesalao, 2, '.', '');

        // Formatar status
        if ($statusw == "publish") {
            $statusw = '<div class="p-2 bg-success text-white text-center">Publicado</div>';
        } elseif ($statusw == "pending") {
            $statusw = '<div class="p-2 bg-danger text-white text-center">Oculto</div>';
        } elseif ($statusw == "draft") {
            $statusw = '<div class="p-2 bg-dark text-white text-center">Pendente</div>';
        }

        //INSERE OS DADOS NO banco produtos
        global $conn;
        // Verifica se o produto já existe pelo SKU
        $sql_check = "SELECT id FROM produtos WHERE sku = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $sku);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Atualiza o produto se já existir
            $sql_update = "UPDATE produtos SET 
        sku = ?,
        nome = ?,          
        preco_cd = ?, 
        preco_salao = ?, 
        estoque = ?, 
        categorias = ?, 
        status = ?, 
        imagem = ?, 
        slug = ?, 
        atualizado_em = NOW() 
        WHERE sku = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param(
                "sddisssss", 
                $sku,
                $name,               
                $price,
                $pricesalao,
                $stock,
                $categories,
                $statusw,
                $image,
                $slug 
            );

            if (!$stmt_update->execute()) {
                echo "Erro ao atualizar produto SKU $sku: " . $stmt_update->error;
            }
        } else {
            // Insere o produto se não existir
            $sql_insert = "INSERT INTO produtos (sku, nome, preco_cd, preco_salao, estoque, categorias, status, imagem, slug, atualizado_em) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param(
                "sddisssss", 
                $sku, 
                $name, 
                $price, 
                $pricesalao, 
                $stock, 
                $categories, 
                $statusw, 
                $image, 
                $slug
            );

            if (!$stmt_insert->execute()) {
                echo "Erro ao inserir produto SKU $sku: " . $stmt_insert->error;
            }
        }


        echo "<tr>
            <td>{$sku}</td>
            <td><img src='$image' alt='Imagem do Produto' style='width: 100px; height: auto;'></td>
            <td>{$name}</td>
            <td>R$ {$price}</td>
            <td>R$ {$pricesalao}</td>
            <td>{$stock}</td>
            <td>{$categories}</td>
            <td>$statusw</td>
        </tr>";
    }

    // Fechar HTML
    echo <<<HTML
        </tbody>
    </table>
    <nav>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </nav>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        const rowsPerPage = 100;
        const rows = $('#product-table tr');
        const rowsCount = rows.length;
        const pageCount = Math.ceil(rowsCount / rowsPerPage);
        const pagination = $('#pagination');

        for (let i = 1; i <= pageCount; i++) {
            pagination.append('<li class="page-item"><a class="page-link" href="#">' + i + '</a></li>');
        }

        pagination.find('a').first().addClass('active');
        rows.hide();
        rows.slice(0, rowsPerPage).show();

        pagination.find('a').click(function (e) {
            e.preventDefault();
            const page = $(this).text();

            pagination.find('a').removeClass('active');
            $(this).addClass('active');

            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.hide();
            rows.slice(start, end).show();
        });

        $('#search').on('keyup', function () {
            const searchText = $(this).val().toLowerCase();
            rows.filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
            });
        });
    });
</script>
</body>
</html>
HTML;
}

// Chamar a função
listWooCommerceProducts();
?>