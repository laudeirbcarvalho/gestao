<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['error' => true, 'message' => 'Usuário não está logado.']);
  exit();
}

// Sanitiza e valida o pedido_id
$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
  echo json_encode(['error' => true, 'message' => 'Pedido ID inválido ou não fornecido.']);
  exit();
}

// Definir os dados de acesso à API do WooCommerce
$woocommerce_url = URL_WOO;
$consumer_key = KEY_WOO;
$consumer_secret = SECRET_WOO;

// Função para verificar se o cliente já existe no WooCommerce
function getCustomerWooCommerce($email)
{
  global $woocommerce_url, $consumer_key, $consumer_secret;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $woocommerce_url . 'customers?email=' . urlencode($email) . '&consumer_key=' . $consumer_key . '&consumer_secret=' . $consumer_secret);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  $customers = json_decode($response, true);

  // Verifica se há clientes na resposta e retorna o primeiro
  if (is_array($customers) && count($customers) > 0) {
    return $customers[0];
  }

  return null;
}

// Função para dividir o nome em first_name e last_name
function splitName($fullName)
{
  $nameParts = explode(' ', trim($fullName), 2);
  return [
    'first_name' => $nameParts[0] ?? '',
    'last_name' => $nameParts[1] ?? ''
  ];
}

// Função para criar o cliente no WooCommerce
function createCustomerWooCommerce($clientData)
{
  // Campos obrigatórios para o cliente
  $requiredFields = ['email', 'nome', 'telefone', 'endereco', 'cidade', 'estado', 'cep', 'pais'];

  foreach ($requiredFields as $field) {
    if (empty($clientData[$field])) {
      return ['error' => true, 'message' => 'O campo ' . $field . ' é obrigatório para criar o cliente.'];
    }
  }

  global $woocommerce_url, $consumer_key, $consumer_secret;

  $username = 'SALAO_' . explode('@', $clientData['email'])[0];
  $email = $clientData['email'];
  $nameParts = splitName($clientData['nome']);

  $data = [
    'email' => $clientData['email'],
    'first_name' => $nameParts['first_name'],
    'last_name' => $nameParts['last_name'],
    'username' => $username,
    'role' => 'Salao',
    'billing' => [
      'first_name' => $nameParts['first_name'],
      'last_name' => $nameParts['last_name'],
      'company' => $clientData['empresa'] ?? '',
      'address_1' => $clientData['endereco'],
      'address_2' => $clientData['complemento'],
      'city' => $clientData['cidade'],
      'postcode' => $clientData['cep'] ?? '',
      'country' => $clientData['pais'],
      'cpf' => $clientData['cpf'] ?? '',
      'state' => $clientData['estado'],
      'email' => $clientData['email'],
      'phone' => $clientData['telefone'],
    ],
    'shipping' => [
      'first_name' => $nameParts['first_name'],
      'last_name' => $nameParts['last_name'],
      'company' => $clientData['empresa'] ?? '',
      'address_1' => $clientData['endereco'],
      'address_2' => $clientData['complemento'],
      'city' => $clientData['cidade'],
      'postcode' => $clientData['cep'] ?? '',
      'country' => $clientData['pais'],
      'state' => $clientData['estado'],
      'phone' => $clientData['telefone'],
      'cpf' => $clientData['cpf'],
    ],
    'meta_data' => [
      [
        'key' => '_billing_number',
        'value' => $clientData['numero']
      ],
      [
        'key' => '_billing_neighborhood',
        'value' => $clientData['bairro']
      ],
      [
        'key' => '_billing_persontype',
        'value' => 'F'
      ],
      [
        'key' => '_billing_cpf',
        'value' => $clientData['cpf']
      ],
      [
        'key' => '_billing_cnpj',
        'value' => $clientData['cnpj']
      ],
      [
        'key' => '_billing_birthdate',
        'value' => $clientData['data_nascimento']
      ],
      [
        'key' => '_billing_gender',
        'value' => !empty($clientData['genero']) ? $clientData['genero'] : ''
      ],
      [
        'key' => '_billing_cellphone',
        'value' => $clientData['cnpj']
      ],
      [
        'key' => '_billing_company',
        'value' => !empty($clientData['empresa']) ? $clientData['empresa'] : ''
      ]
    ]
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $woocommerce_url . 'customers?consumer_key=' . $consumer_key . '&consumer_secret=' . $consumer_secret);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  $response = curl_exec($ch);

  if (curl_errno($ch)) {
    return ['error' => true, 'message' => 'Erro na requisição CURL: ' . curl_error($ch)];
  }

  curl_close($ch);

  // Verificar se a resposta é JSON válido
  $jsonResponse = json_decode($response, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    return [
      'error' => true,
      'message' => 'Resposta da API não é um JSON válido.',
      'details' => $response
    ];
  }

  // Registrar detalhes da resposta para depuração
  file_put_contents('log_woocommerce.txt', "Cliente enviado: " . json_encode($data) . "\nResposta: " . $response . "\n\n", FILE_APPEND);

  return $jsonResponse;
}

// Função para criar o pedido no WooCommerce
function createOrderWooCommerce($orderData, $orderItems)
{
  // Campos obrigatórios para o pedido
  $requiredFields = ['cliente_nome', 'cliente_email', 'cliente_telefone', 'cliente_endereco', 'cliente_cidade', 'cliente_estado', 'cliente_cep', 'cliente_pais', 'customer_id'];

  foreach ($requiredFields as $field) {
    if (empty($orderData[$field])) {
      return ['error' => true, 'message' => 'O campo ' . $field . ' é obrigatório para criar o pedido.'];
    }
  }

  global $woocommerce_url, $consumer_key, $consumer_secret;

  $nameParts = splitName($orderData['cliente_nome']);

  $nota = strip_tags($orderData['pedido_nota']);

  // Expressão regular para capturar a URL dentro do <a href='...'>
  preg_match("/<a\s+href=['\"]([^'\"]+)['\"]/i", $orderData['pedido_nota'], $matches);

  // Se encontrou o link, pega o primeiro grupo da regex
  $link = isset($matches[1]) ? $matches[1] : '';

  $notatratada = $nota . ' |  ' . $link . ' ';

  $data = [
    'payment_method' => 'bacs',
    'payment_method_title' => 'Banco',
    'set_paid' => false,
    'customer_id' => $orderData['customer_id'],
    'billing' => [
      'first_name' => $nameParts['first_name'],
      'last_name' => $nameParts['last_name'],
      'email' => $orderData['cliente_email'],
      'phone' => $orderData['cliente_telefone'],
      'address_1' => $orderData['cliente_endereco'],
      'city' => $orderData['cliente_cidade'],
      'state' => $orderData['cliente_estado'],
      'postcode' => $orderData['cliente_cep'],
      'country' => $orderData['cliente_pais'],
    ],
    'shipping' => [
      'first_name' => $nameParts['first_name'],
      'last_name' => $nameParts['last_name'],
      'address_1' => $orderData['cliente_endereco'],
      'city' => $orderData['cliente_cidade'],
      'state' => $orderData['cliente_estado'],
      'postcode' => $orderData['cliente_cep'],
      'country' => $orderData['cliente_pais']
    ],
    'line_items' => $orderItems,
    'status' => 'pending',
    'customer_note' => $notatratada, // Aqui está a nota do pedido
    'meta_data' => [
      [
        'key' => '_billing_number',
        'value' => $orderData['cliente_numero']
      ],
      [
        'key' => '_billing_neighborhood',
        'value' => $orderData['cliente_bairro']
      ],
      [
        'key' => '_billing_cpf',
        'value' => $orderData['cliente_cpf']
      ],
      [
        'key' => '_billing_cnpj',
        'value' => $orderData['cliente_cnpj']
      ],
      [
        'key' => '_billing_cellphone',
        'value' => $orderData['cliente_telefone']
      ],
      [
        'key' => '_billing_company',
        'value' => $orderData['cliente_empresa']
      ]
    ]
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $woocommerce_url . 'orders?consumer_key=' . $consumer_key . '&consumer_secret=' . $consumer_secret);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  $response = curl_exec($ch);

  if (curl_errno($ch)) {
    return ['error' => true, 'message' => 'Erro na requisição CURL: ' . curl_error($ch)];
  }

  curl_close($ch);

  // Verificar se a resposta é JSON válido
  $jsonResponse = json_decode($response, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    return [
      'error' => true,
      'message' => 'Resposta da API não é um JSON válido.',
      'details' => $response
    ];
  }

  // Registrar detalhes da resposta para depuração
  file_put_contents('log_woocommerce.txt', "Pedido enviado: " . json_encode($data) . "\nResposta: " . $response . "\n\n", FILE_APPEND);

  return $jsonResponse;
}

// Buscar os dados necessários no banco de dados
$sql = "SELECT customer_id, nota FROM pedidos_shopify WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $pedido = $result->fetch_assoc();
  $id_shopify = $pedido['customer_id'];
  
  
  $nota = $pedido['nota'];


  $sql_cliente = "SELECT nome, email, telefone, endereco, cidade, estado, pais, cep, data_nascimento, cpf, cnpj, numero, complemento, bairro, empresa FROM clientes_shopify WHERE id_shopify = ?";
  $stmt_cliente = $conn->prepare($sql_cliente);
  $stmt_cliente->bind_param("i", $id_shopify);
  $stmt_cliente->execute();
  $result_cliente = $stmt_cliente->get_result();

  if ($result_cliente->num_rows > 0) {
    $cliente = $result_cliente->fetch_assoc();
  } else {
    echo json_encode(['error' => true, 'message' => 'Cliente não encontrado.']);
    exit();
  }

  // Verificar se todos os campos obrigatórios do cliente estão presentes
  $requiredFields = ['nome', 'email', 'telefone', 'endereco', 'cidade', 'estado', 'cep', 'pais'];
  foreach ($requiredFields as $field) {
    if (empty($cliente[$field])) {
      echo json_encode(['error' => true, 'message' => 'O campo ' . $field . ' é obrigatório para o cliente.']);
      exit();
    }
  }

  $sql_itens = "SELECT * FROM itens_pedido_shopify WHERE order_id = ?";
  $stmt_itens = $conn->prepare($sql_itens);
  $stmt_itens->bind_param("i", $pedido_id);
  $stmt_itens->execute();
  $itemResult = $stmt_itens->get_result();

  $orderItems = [];
  if ($itemResult->num_rows > 0) {
    while ($item = $itemResult->fetch_assoc()) {
      $orderItems[] = [
        'product_id' => $item['variant_id'],
        'quantity' => $item['quantidade'],
        'total' => $item['total'],
        'sku' => $item['sku']
      ];
    }
  } else {
    echo json_encode(['error' => true, 'message' => 'Nenhum item válido encontrado no pedido.']);
    exit();
  }

  // Verificar se o cliente já existe no WooCommerce
  $wooCliente = getCustomerWooCommerce($cliente['email']);
  if (!$wooCliente) {
    // Se o cliente não existir, cria um novo
    $wooCliente = createCustomerWooCommerce($cliente);
    if (isset($wooCliente['error'])) {
      echo json_encode(['error' => true, 'message' => 'ATENÇÃO!!! O pedido não foi criado no WooCommerce.']);
      exit();
    }
  }



  // Prepara os dados do pedido
  $orderData = [
    'customer_id' => $wooCliente['id'],
    'cliente_nome' => $cliente['nome'],
    'cliente_email' => $cliente['email'],
    'cliente_telefone' => $cliente['telefone'],
    'cliente_endereco' => $cliente['endereco'],
    'cliente_cidade' => $cliente['cidade'],
    'cliente_estado' => $cliente['estado'],
    'cliente_cep' => $cliente['cep'],
    'cliente_pais' => $cliente['pais'],
    'cliente_cpf' => $cliente['cpf'],
    'cliente_cnpj' => $cliente['cnpj'],
    'cliente_complemento' => $cliente['complemento'],
    'cliente_numero' => $cliente['numero'],
    'cliente_bairro' => $cliente['bairro'],
    'cliente_empresa' => $cliente['empresa'],
    'pedido_nota' => $nota,

  ];

  // Cria o pedido no WooCommerce
  $wooOrder = createOrderWooCommerce($orderData, $orderItems);

  // var_dump($wooOrder);
  // exit();

  if (isset($wooOrder['id'])) {
    echo json_encode(['error' => false, 'message' => 'Pedido Criado com Sucesso no WooCommerce']);        
  } else {
    echo json_encode(['error' => true, 'message' => 'ATENÇÃO!!! O pedido não foi criado no WooCommerce.']);
  }
} else {
  echo json_encode(['error' => true, 'message' => 'Pedido não encontrado.']);
  exit();
}


?>