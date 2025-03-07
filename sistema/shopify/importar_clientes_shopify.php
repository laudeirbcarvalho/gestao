<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Configurações da loja
$nome_lojaMatriz     = NOME_SHOPIFY;
$shopify_storeMatriz = SHOPIFY_STORE;
$access_tokenMatriz  = SHOPIFY_TOKEN;

// Função para listar clientes
function listarClientesShopify($shopify_store, $access_token)
{
    $url = "https://$shopify_store/admin/api/2023-10/customers.json?fields=id,first_name,last_name,email,tags,created_at,addresses";

    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro na requisição: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    return $data['customers'] ?? [];
}

// Insere os dados dos clientes no banco de dados
$clientes = listarClientesShopify($shopify_storeMatriz, $access_tokenMatriz);



if (!empty($clientes)) {
    foreach ($clientes as $cliente) {
        $id_shopify = $cliente['id'];
        $nome = ($cliente['first_name'] ?? '') . ' ' . ($cliente['last_name'] ?? ''); // Corrigido
        $email = $cliente['email'] ?? ''; // Usando coalescência nula para evitar erro
        $status = $cliente['tags'] ?? '';
        $data_cadastro = $cliente['created_at']; // Data de cadastro do cliente
    
        // Obter os dados do endereço
        $endereco = '';
        $cidade = '';
        $estado = '';
        $pais = '';
        $telefone = '';
        $data_nascimento = null;
    
        if (!empty($cliente['addresses'])) {
            $endereco = $cliente['addresses'][0]['address1'] ?? '';
            $cidade = $cliente['addresses'][0]['city'] ?? '';
            $estado = $cliente['addresses'][0]['province'] ?? '';
            $pais = $cliente['addresses'][0]['country'] ?? '';
            $telefone = $cliente['addresses'][0]['phone'] ?? '';
            $data_nascimento = $cliente['addresses'][0]['date_of_birth'] ?? null;
        }

        //Aqui deve listar todos os clientes_shopify

        $central_id = CENTRAL_ID;   
        // Prepare a instrução SQL para inserir ou atualizar
        $stmt = $conn->prepare("
    INSERT INTO clientes_shopify (id_shopify, nome, email, status, data_cadastro, endereco, cidade, estado, pais, telefone, data_nascimento, cpf, cnpj, status_atualizacao, central_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inserido', ?)
    ON DUPLICATE KEY UPDATE 
        nome = VALUES(nome),
        email = VALUES(email),
        status = VALUES(status),
        data_cadastro = VALUES(data_cadastro),
        endereco = VALUES(endereco),
        cidade = VALUES(cidade),
        estado = VALUES(estado),
        pais = VALUES(pais),
        telefone = VALUES(telefone),
        data_nascimento = VALUES(data_nascimento),
        cpf = VALUES(cpf),
        cnpj = VALUES(cnpj),
        status_atualizacao = 'atualizado',
        central_id = IF(status_atualizacao = 'inserido', VALUES(central_id), central_id)
");


        // Vincule os parâmetros corretamente
        $stmt->bind_param("isssssssssssss", $id_shopify, $nome, $email, $status, $data_cadastro, $endereco, $cidade, $estado, $pais, $telefone, $data_nascimento, $cpf, $cnpj, $central_id);

        // Executa a instrução
        if (!$stmt->execute()) {
            echo "Erro ao inserir cliente: " . $stmt->error;
        }
    }
}

// Busca os clientes da tabela clientes_shopify
$result = $conn->query("SELECT id_shopify, nome, email, status, data_cadastro, endereco, cidade, estado, pais, telefone, data_nascimento, status_atualizacao FROM clientes_shopify WHERE status_atualizacao IN ('inserido') AND central_id = '".$central_id."'");
$clientes_list = [];
while ($row = $result->fetch_assoc()) {
    $clientes_list[] = $row;
}


if (count($clientes_list) > 0) {
?>

<div class="container mt-3 row justify-content-center align-items-center h-100">
    <h5 class="text-center alert  alert-success">Clientes <?=NOME_SHOPIFY?> importados</h5>
    <ul class="list-group mb-3 mt-3">
        <?php foreach ($clientes_list as $cliente) {
            $data_cadastro_formatada = !empty($cliente['data_cadastro']) ? date("d/m/Y H:i", strtotime($cliente['data_cadastro'])) : 'Data não disponível';
            ?>
            <li class="list-group-item">
                <h5 class="mb-1"> <?php echo $cliente['nome']; ?></h5>
                <p class="mb-1">Email: <?php echo $cliente['email']; ?></p>
                <p class="mb-1">Status: <?php echo $cliente['status']; ?> / <?php echo $cliente['status_atualizacao']; ?>
                </p>
                <p class="mb-1">Data de Cadastro: <?php echo $data_cadastro_formatada; ?></p>
            </li>
        <?php } ?>
    </ul>
</div>

<?php } else {
    // Exibe a mensagem caso não haja clientes
    ?>
    <div class="container mt-3 row justify-content-center align-items-center h-100">
        <h5 class="text-center alert alert-info">Nenhum cliente novo importado no momento.</h5>
    </div>
<?php } ?>