<?php
session_start();

// Incluindo os arquivos necessários
require_once('../../sistema/db.php');  // Conexão com o banco de dados
require_once('../../sistema/protege.php');  // Proteção de sessão

// Configurações do WooCommerce
$woocommerce_url = 'https://compreadlux.com.br/wp-json/wc/v3/customers';
$consumer_key = 'ck_3044ff2e1cc56ef0a070cab67b1102e2b04cf910';
$consumer_secret = 'cs_e2c82183112dd3c43ff466ae34318d4c5f35e185';

// Função para salvar o usuário no banco de dados
function saveUser($conn, $user) {
    // Verifica se o usuário já existe no banco de dados (baseado no email ou username)
    $stmt = $conn->prepare("SELECT id FROM clientes_woocomerce WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $user['email'], $user['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Usuário já existe: " . $user['email'] . " / " . $user['username'] . "<br>";
        $stmt->close();
        return; // Se o usuário já existir, não insere
    }

    $stmt->close();
    
    // Inserir o usuário no banco
    $stmt = $conn->prepare("INSERT INTO clientes_woocomerce (shopify_id, date_created, date_modified, email, first_name, last_name, username, role, billing_first_name, billing_last_name, billing_company, billing_address_1, billing_address_2, billing_city, billing_postcode, billing_country, billing_state, billing_email, billing_phone, billing_cpf, billing_rg, billing_birthdate, billing_gender, shipping_first_name, shipping_last_name, shipping_company, shipping_address_1, shipping_address_2, shipping_city, shipping_postcode, shipping_country, shipping_state, shipping_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "issssssssssssssssssssssssssssssss", // 35 letras
        $user['shopify_id'],
        $user['date_created'],
        $user['date_modified'],
        $user['email'],
        $user['first_name'],
        $user['last_name'],
        $user['username'],
        $user['role'],
        $user['billing_first_name'],
        $user['billing_last_name'],
        $user['billing_company'],
        $user['billing_address_1'],
        $user['billing_address_2'],
        $user['billing_city'],
        $user['billing_postcode'],
        $user['billing_country'],
        $user['billing_state'],
        $user['billing_email'],
        $user['billing_phone'],
        $user['billing_cpf'],
        $user['billing_rg'],
        $user['billing_birthdate'],
        $user['billing_gender'],
        $user['shipping_first_name'],
        $user['shipping_last_name'],
        $user['shipping_company'],
        $user['shipping_address_1'],
        $user['shipping_address_2'],
        $user['shipping_city'],
        $user['shipping_postcode'],
        $user['shipping_country'],
        $user['shipping_state'],
        $user['shipping_phone']
    );

    $stmt->execute();
    $stmt->close();
    echo "Usuário " . $user['email'] . " registrado com sucesso.<br>";
}

// Função para buscar dados do WooCommerce (com paginação)
function fetchWooCommerceData($url, $consumer_key, $consumer_secret, $page = 1, $per_page = 100) {
    $full_url = $url . '?page=' . $page . '&per_page=' . $per_page;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $full_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $consumer_key . ":" . $consumer_secret);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Conectar ao banco de dados (já configurado no db.php)
global $conn;

// Loop para todas as páginas
$page = 1;
$all_users = [];
$continue = true;

echo "<h2>Importação de Usuários do WooCommerce</h2>";

while ($continue) {
    $users = fetchWooCommerceData($woocommerce_url, $consumer_key, $consumer_secret, $page);

    if (!empty($users)) {
        foreach ($users as $user) {
            // Estrutura do usuário
            $userData = [
                'shopify_id' => isset($user['id']) ? $user['id'] : null,
                'date_created' => isset($user['date_created']) ? $user['date_created'] : null,
                'date_modified' => isset($user['date_modified']) ? $user['date_modified'] : null,
                'email' => isset($user['email']) ? $user['email'] : '',
                'first_name' => isset($user['first_name']) ? $user['first_name'] : '',
                'last_name' => isset($user['last_name']) ? $user['last_name'] : '',
                'username' => isset($user['username']) ? $user['username'] : '',
                // Verifica se o campo 'roles' existe e é um array
                'role' => isset($user['roles']) && is_array($user['roles']) ? implode(", ", $user['roles']) : 'N/A', // Caso não exista, atribui 'N/A'
                'billing_first_name' => isset($user['billing']['first_name']) ? $user['billing']['first_name'] : '',
                'billing_last_name' => isset($user['billing']['last_name']) ? $user['billing']['last_name'] : '',
                'billing_company' => isset($user['billing']['company']) ? $user['billing']['company'] : '',
                'billing_address_1' => isset($user['billing']['address_1']) ? $user['billing']['address_1'] : '',
                'billing_address_2' => isset($user['billing']['address_2']) ? $user['billing']['address_2'] : '',
                'billing_city' => isset($user['billing']['city']) ? $user['billing']['city'] : '',
                'billing_postcode' => isset($user['billing']['postcode']) ? $user['billing']['postcode'] : '',
                'billing_country' => isset($user['billing']['country']) ? $user['billing']['country'] : '',
                'billing_state' => isset($user['billing']['state']) ? $user['billing']['state'] : '',
                'billing_email' => isset($user['billing']['email']) ? $user['billing']['email'] : '',
                'billing_phone' => isset($user['billing']['phone']) ? $user['billing']['phone'] : '',
                'billing_cpf' => isset($user['billing']['cpf']) ? $user['billing']['cpf'] : '',
                'billing_rg' => isset($user['billing']['rg']) ? $user['billing']['rg'] : '',
                'billing_birthdate' => isset($user['billing']['birthdate']) ? $user['billing']['birthdate'] : '',
                'billing_gender' => isset($user['billing']['gender']) ? $user['billing']['gender'] : '',
                'shipping_first_name' => isset($user['shipping']['first_name']) ? $user['shipping']['first_name'] : '',
                'shipping_last_name' => isset($user['shipping']['last_name']) ? $user['shipping']['last_name'] : '',
                'shipping_company' => isset($user['shipping']['company']) ? $user['shipping']['company'] : '',
                'shipping_address_1' => isset($user['shipping']['address_1']) ? $user['shipping']['address_1'] : '',
                'shipping_address_2' => isset($user['shipping']['address_2']) ? $user['shipping']['address_2'] : '',
                'shipping_city' => isset($user['shipping']['city']) ? $user['shipping']['city'] : '',
                'shipping_postcode' => isset($user['shipping']['postcode']) ? $user['shipping']['postcode'] : '',
                'shipping_country' => isset($user['shipping']['country']) ? $user['shipping']['country'] : '',
                'shipping_state' => isset($user['shipping']['state']) ? $user['shipping']['state'] : '',
                'shipping_phone' => isset($user['shipping']['phone']) ? $user['shipping']['phone'] : ''
            ];

            // Exibe os dados do usuário (para visualização no navegador)
            echo "<strong>Email:</strong> " . $userData['email'] . "<br>";
            echo "<strong>Nome:</strong> " . $userData['first_name'] . " " . $userData['last_name'] . "<br>";
            echo "<strong>Papel(s):</strong> " . $userData['role'] . "<br><br>"; // Mostra os papéis

            // Salva o usuário se não existir
            saveUser($conn, $userData);
        }

        // Aumenta a página e continua o loop
        $page++;
    } else {
        // Se não houver mais usuários, termina o loop
        $continue = false;
        echo "Não há mais usuários para importar.<br>";
    }
}

echo "Processo de importação concluído!";
?>
