<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Função para buscar produtos no Banco de dados
function getWooCommerceProducts($central_id)
{
    global $conn;
    $sql = "SELECT p.sku, p.nomeproduto, p.preco_cd, 
                   pe.preco_salao, pe.preco_final, pe.preco_mktplace, pe.estoque,
                   p.categorias, p.status, p.imagem, p.slug, p.tags, p.atualizado_em, p.atualizado_em_shopify
            FROM produtos p
            LEFT JOIN precos_e_estoques pe ON p.sku = pe.sku AND pe.central_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $central_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    $stmt->close();
    $conn->close();
    return $products;
}


//Ultima execução Automatica do Estoque CRON
function obter_ultima_data_execucao_estoque($tipo)
{
    $arquivo_log = 'cron_log.txt';
    $ultima_data = 'Não disponível';

    if (file_exists($arquivo_log)) {
        $linhas = file($arquivo_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_reverse($linhas) as $linha) {
            if (strpos($linha, $tipo) !== false) {
                // Verifica se a linha contém uma data válida
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $linha, $matches)) {
                    // Converte a data para o formato desejado
                    $ultima_data = date("d/m/Y H:i:s", strtotime($matches[1]));
                }
                break;
            }
        }
    }

    return $ultima_data;
}


// Função para listar produtos
function listWooCommerceProducts($central_id)
{
    // Obtenha a última data de execução do estoque
    $data_ultimos_estoque = obter_ultima_data_execucao_estoque('Estoque');
    $woocommerce_products = getWooCommerceProducts($central_id);

    if (empty($woocommerce_products)) {
        echo "<p class='text-danger'>Nenhum produto salvo na base de dados.</p>";
        return;
    }

    // Inicializar contadores
    $publishedCount = 0;
    $pendingCount = 0;
    $draftCount = 0;
    $botaoshopify = "";
    $botaoshopify_criar_produtos = "";


    if ($_SESSION["permissao"] == "admin") {
        $botaoshopify = '<button id="integrarProdutosShopify" class="btn btn-success">Atualizar Produtos ' . NOME_SHOPIFY . ' <em>(shopify)</em></button>';
        $botaoshopify_criar_produtos = '<button id="integrarProdutosShopifyCriarProdutos" class="btn btn-danger" title="Se já exixtir um produto ' . NOME_SHOPIFY . ' ele irá duplicar !!!">Criar Produtos ' . NOME_SHOPIFY . ' <em>(shopify)</em></button>';

    } else {
        $botaoshopify = '';
        $botaoshopify_criar_produtos = '<div id="integrarProdutosShopifyCriarProdutos" ></div>';

    }

    $nome_woo = NOME_WOO;
    $nome_shopify = NOME_SHOPIFY;
    // Cabeçalho HTML com Bootstrap

    $CD = "CD";

    if (CENTRAL_SUPERUSER == "superuser") {
        $CD = "<th>CD</th>";
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos {$nome_woo}</title>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    
<div class="container-fluid vh-100  mt-5 sumirlista">
<div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Lista de Produtos {$nome_woo}</h1>
        <a class="btn btn-secondary" href="#" id="voltarpainel">Voltar</a>         
        {$botaoshopify}
        {$botaoshopify_criar_produtos}
 </div>


 <div id="listar_produtos_integrados_shopify"></div>


    <!-- Exibir o total de produtos por status -->
    <div class="mb-3">
        <strong>Status de Produtos*:</strong>
        <span class="badge badge-success">Publicado: <span id="published-count">0</span></span>
        <span class="badge badge-danger">Oculto: <span id="pending-count">0</span></span>
        <span class="badge badge-dark">Pendente: <span id="draft-count">0</span></span>
        <span class="badge badge-warning">Última Atualização de Estoque: <span id="draft-count">{$data_ultimos_estoque}</span></span>
    </div>

    <input id="search" class="form-control mb-3" placeholder="Buscar produtos...">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th><input type="checkbox" id="select-all" class=""></th>
                <th>SKU</th>
                <th>Imagem</th>
                <th>Nome</th>
                {$CD}
                <th>Salão</th>
                <th>Final</th>
                <th>Mktplace</th>                
                <th>Estoque</th>
                <th>Categorias</th>
                <th>Tags</th>
                <th>Status</th>
                <th>Última Atualização</th>
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
        $nomeproduto = isset($product['nomeproduto']) ? $product['nomeproduto'] : 'N/A';
        $preco_cd = isset($product['preco_cd']) ? $product['preco_cd'] : 'N/A';
        $preco_salao = isset($product['preco_salao']) ? $product['preco_salao'] : 'N/A';
        $preco_mktplace = isset($product['preco_mktplace']) ? $product['preco_mktplace'] : 'N/A';
        $preco_final = isset($product['preco_final']) ? $product['preco_final'] : 'N/A';
        $estoque = isset($product['estoque']) ? $product['estoque'] : 'N/A';
        $imagem = isset($product['imagem']) ? $product['imagem'] : 'https://via.placeholder.com/100';
        $status = isset($product['status']) ? $product['status'] : 'N/A';
        $slug = isset($product['slug']) ? $product['slug'] : 'N/A';
        $tags = isset($product['tags']) ? $product['tags'] : 'N/A';

        $updated_at = isset($product['atualizado_em']) ? $product['atualizado_em'] : null;
        $updated_at_shopify = isset($product['atualizado_em_shopify']) ? $product['atualizado_em_shopify'] : null;

        $categories = isset($product['categorias']) ? $product['categorias'] : 'Sem categoria';

        // Contar o status
        if ($status == "publish") {
            $status = '<div class="p-2 bg-success text-white text-center">Publicado</div>';
            $publishedCount++;
        } elseif ($status == "pending") {
            $status = '<div class="p-2 bg-danger text-white text-center">Oculto</div>';
            $pendingCount++;
        } elseif ($status == "draft") {
            $status = '<div class="p-2 bg-dark text-white text-center">Pendente</div>';
            $draftCount++;
        }

        // Convertendo e formatando as datas
        if ($updated_at) {
            $updated_at = DateTime::createFromFormat('Y-m-d H:i:s', $updated_at);
            $formatted_date_woo = $updated_at instanceof DateTime
                ? $updated_at->format('d/m/Y \à\s H:i')
                : 'Data inválida';
        } else {
            $formatted_date_woo = 'Não disponível';
        }

        if ($updated_at_shopify) {
            $updated_at_shopify = DateTime::createFromFormat('Y-m-d H:i:s', $updated_at_shopify);
            $formatted_date_shopify = $updated_at_shopify instanceof DateTime
                ? $updated_at_shopify->format('d/m/Y \à\s H:i')
                : 'Data inválida';
        } else {
            $formatted_date_shopify = 'Não disponível';
        }

        // Exibir produto na tabela
        echo "<tr>
        <td><input type='checkbox' class='product-checkbox' data-sku='{$sku}'></td>
        <td>{$sku}</td>
        <td>
            <img title='$imagem' src='$imagem' alt='Imagem do Produto' style='width: 100px; height: auto;'>
          ";
        if (CENTRAL_SUPERUSER == "superuser") {
            if ($_SESSION["permissao"] === "admin") {
                echo "<i class='btn-alterar-imagem fa fa-fw fa-camera-retro' data-sku='{$sku}' data-imagem='{$imagem}'></i>
            <div id='retornoimagem'></div>";
            }
        }
        echo "</td>
        <td>{$nomeproduto}</td>";

        if (CENTRAL_SUPERUSER == "superuser") {
            echo "<td>R$ {$preco_cd}</td>";
        }

        foreach (['preco_salao', 'preco_final', 'preco_mktplace'] as $campo) {
            $valor = $$campo;
            if ($_SESSION["permissao"] === "admin") {
                echo "<td class='editable' data-sku='{$sku}' data-field='{$campo}' data-value='{$valor}'>
                    <span class='price-text text-primary'>R$ {$valor}</span>
                    <input type='number' class='price-input' value='{$valor}' style='display:none;'></td>";
            } else {
                echo "<td><span class='price-text text-primary'>R$ {$valor}</span></td>";
            }
        }

        echo "
         
        <td><b>{$estoque}</b></td>
        <td>{$categories}</td>
        <td>{$tags}</td>
        <td>{$status}</td>
        <td>
        <span class='badge badge-success'>{$nome_woo}</span>
        {$formatted_date_woo}</br>
        <span class='badge badge-success'>{$nome_shopify}</span>
        {$formatted_date_shopify}</br>
        </td>
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

document.getElementById('integrarProdutosShopifyCriarProdutos').addEventListener('click', function (event) {
        // Exibe uma mensagem de confirmação
        const confirmacao = confirm("Atenção! Se o produto já existir na Shopify, ele será duplicado. Deseja continuar?");
        
        // Se o usuário cancelar, impede a execução da ação e interrompe a propagação
        if (!confirmacao) {
            event.preventDefault();
            event.stopPropagation(); // Impede que o evento continue se propagando
        }
    });
    
    $(document).ready(function () {

        //Volta o painel do compre
        $('#voltarpainel').on('click', function () {         
             $('.painelcompre').show('slow');
             $('.sumirlista').hide('slow'); 
          });  

        const rowsPerPage = 25;
        const rows = $('#product-table tr');
        const rowsCount = rows.length;
        const pagination = $('#pagination');
        let searchText = '';

        // Função para atualizar a paginação
        function updatePagination(filteredRows) {
            const filteredCount = filteredRows.length;
            const pageCount = Math.ceil(filteredCount / rowsPerPage);
            
            pagination.empty(); // Limpar paginação existente

            // Criar a nova paginação com base no número de itens filtrados
            for (let i = 1; i <= pageCount; i++) {
                pagination.append('<li class="page-item"><a class="page-link" href="#">' + i + '</a></li>');
            }

            pagination.find('a').first().addClass('active'); // Adicionar classe de página ativa
            showPage(1, filteredRows); // Exibir a primeira página

            // Função para exibir uma página específica
            pagination.find('a').click(function (e) {
                e.preventDefault();
                const page = $(this).text();
                pagination.find('a').removeClass('active');
                $(this).addClass('active');
                showPage(page, filteredRows);
            });
        }

        // Função para mostrar os itens de uma página específica
        function showPage(page, filteredRows) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            filteredRows.hide();
            filteredRows.slice(start, end).show();
        }

        // Função de filtragem
        $('#search').on('keyup', function () {
            searchText = $(this).val().toLowerCase();
            filterTable();
        });

        // Função para filtrar a tabela com base no texto de pesquisa
        function filterTable() {
            const filteredRows = rows.filter(function () {
                return $(this).text().toLowerCase().indexOf(searchText) > -1;
            });

            // Exibir apenas as linhas filtradas
            rows.hide();
            filteredRows.show();

            updatePagination(filteredRows); // Atualizar a paginação com os resultados filtrados
        }

        // Inicializar a paginação e esconder todas as linhas inicialmente
        rows.hide();
        updatePagination(rows);

        // Exibir o total de produtos por status
        $('#published-count').text($publishedCount);
        $('#pending-count').text($pendingCount);
        $('#draft-count').text($draftCount);
    
    
       
        // Quando o preço de Salão for clicado, troca para o campo de edição
        $('.editable').click(function () {
    var priceCell = $(this);
    var currentValue = priceCell.data('value');

    console.log('Valor atual:', currentValue); // Log do valor atual
    priceCell.find('.price-text').hide();
    priceCell.find('.price-input').show().focus();
});

$('.price-input').on('blur keyup', function (e) {
    if (e.type === 'blur' || e.key === 'Enter') {
        var priceCell = $(this).closest('td');
        var newValue = $(this).val();
        var sku = priceCell.data('sku');
        var field = priceCell.data('field');

        console.log('Novo valor:', newValue, 'SKU:', sku, 'Campo:', field); // Log dos dados enviados

        $.ajax({
            url: 'sistema/woocomerce/update_price.php',
            method: 'POST',
            data: {
                sku: sku,
                field: field,
                value: newValue
            },
            success: function (response) {
                console.log('Resposta do servidor:', response); // Log da resposta do PHP
                priceCell.data('value', newValue);
                priceCell.find('.price-text').text('R$ ' + newValue).show();
                priceCell.find('.price-input').hide();
            },
            error: function () {
                alert('Erro ao salvar o preço. Tente novamente.');
            }
        });
    }
});

    
    
//Botão integrar  ShopiFy ATUALIZAR PRODUTOS #integrarProdutosShopify
$('#integrarProdutosShopify').on('click', function () {
    // Array para armazenar os SKUs dos itens selecionados
    let selectedSKUs = [];

    // Percorrer todos os checkboxes selecionados e coletar o atributo data-sku
    document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
        selectedSKUs.push(checkbox.getAttribute("data-sku")); // Coleta o data-sku
    });

    console.log("SKUs selecionados: ", selectedSKUs);  // Adicionando log para depuração

    // Verifica se algum item foi selecionado
    if (selectedSKUs.length === 0) {
        alert('Por favor, selecione pelo menos um item para integrar.');
        return;
    }

    // Exibir o "Aguarde..." enquanto processa
    $('#loading').show();
    $('#listar_produtos_integrados_shopify').html(''); // Limpar div anterior

    // Envia os dados via AJAX
    $.ajax({
        url: 'sistema/woocomerce/integra_produtos_woo_shopify.php',
        type: 'POST',
        data: { skus: selectedSKUs }, // Envia os SKUs selecionados
        success: function (response) {
            console.log("Resposta da integração: ", response);  // Log para verificar resposta
            // Exibe o retorno na div listar_produtos_integrados_shopify
            $('#listar_produtos_integrados_shopify').html(response);
        },
        error: function (xhr, status, error) {
            console.error("Erro na requisição AJAX: ", status, error);  // Log de erro
            $('#listar_produtos_integrados_shopify').html('Erro ao integrar os produtos. Tente novamente.');
        },
        complete: function () {
            // Oculta o "Aguarde..." ao finalizar
            $('#loading').hide();
        }
    });
});

 
        //Botão integrar pedido ShopiFy CRIAR PRODUTOS 
        $('#integrarProdutosShopifyCriarProdutos').on('click', function () {
            // Array para armazenar os SKUs dos itens selecionados
            let selectedSKUs = [];

            // Percorrer todos os checkboxes selecionados e coletar o atributo data-sku
            document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
                selectedSKUs.push(checkbox.getAttribute("data-sku")); // Coleta o data-sku
            });

            // Verifica se algum item foi selecionado
            if (selectedSKUs.length === 0) {
                alert('Por favor, selecione pelo menos um item para integrar.');
                return;
            }

            // Exibir o "Aguarde..." enquanto processa
            $('#loading').show();
            $('#listar_produtos_integrados_shopify').html(''); // Limpar div anterior

            // Envia os dados via AJAX
            $.ajax({
                url: 'sistema/woocomerce/criar_produtos_shopify.php',
                type: 'POST',
                data: { skus: selectedSKUs }, // Envia os SKUs selecionados
                success: function (response) {
                    // Exibe o retorno na div listar_produtos_integrados_shopify
                    $('#listar_produtos_integrados_shopify').html(response);
                },
                error: function () {
                    $('#listar_produtos_integrados_shopify').html('Erro ao integrar os produtos. Tente novamente.');
                },
                complete: function () {
                    // Oculta o "Aguarde..." ao finalizar
                    $('#loading').hide();
                }
            });
        });
 

    
 });


</script>
</body>
</html>
HTML;
}

listWooCommerceProducts(CENTRAL_ID);

?>
<script>
    // Selecionar todos os checkboxes
    document.getElementById("select-all").addEventListener("change", function () {
        const checkboxes = document.querySelectorAll(".product-checkbox");
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Enviar os SKUs selecionados para a Shopify
    document.getElementById("integrate-shopify-btn").addEventListener("click", function () {
        const selectedSKUs = [];

        // Pegar todos os SKUs dos produtos selecionados
        document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
            selectedSKUs.push(checkbox.getAttribute("data-sku"));
        });

        if (selectedSKUs.length > 0) {

            // Enviar os SKUs para Shopify via API ou qualquer outro método
            console.log("Enviando SKUs:", selectedSKUs);
            // Aqui você pode chamar sua função de integração com Shopify passando os SKUs selecionados
        } else {
            alert("Por favor, selecione pelo menos um produto.");
        }
    });
</script>