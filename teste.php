<?php

$woocommerce_url = 'https://compreadlux.com.br/wp-json/wc/v3/';
$consumer_key = 'ck_3044ff2e1cc56ef0a070cab67b1102e2b04cf910';
$consumer_secret = 'cs_e2c82183112dd3c43ff466ae34318d4c5f35e185';

// Dados do cliente
$clientData = [
    'nome' => 'Laudeir Adlux Academy Teste',
    'email' => 'adluxacademy@gmail.com',
    'telefone' => '15997385426',
    'endereco' => 'Rua Venâncio Peres',
    'numero_cliente' => '106',
    'bairro' => 'Jardim Santa Emília',
    'cidade' => 'Tatuí',
    'estado' => 'SP',
    'cep' => '18277049',
    'pais' => 'BR',
    'cpf_cliente' => '182.77724884',
    'cnpj_cliente' => '0519610800115',
    'data_nascimento' => '1990-01-01',
    'genero' => 'Masculino',
    'celular' => '15997385426',
    'complemento' => 'Casa',
    'empresa' => 'EMPRESA TESTE'
];

// Função para dividir o nome em first_name e last_name
function splitName($fullName) {
    $nameParts = explode(' ', trim($fullName), 2);
    return [
        'first_name' => $nameParts[0] ?? '',
        'last_name' => $nameParts[1] ?? ''
    ];
}

// Função para criar o cliente
function createCustomerWooCommerce($clientData) {
    global $woocommerce_url, $consumer_key, $consumer_secret;

    $username = 'SALAO_' . explode('@', $clientData['email'])[0];
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
            'company' => $clientData['empresa'],            
            'address_1' => $clientData['endereco'],
            'address_2' => $clientData['complemento'],
            'city' => $clientData['cidade'],            
            'postcode' => $clientData['cep'],
            'country' => $clientData['pais'],  
            'cpf' => $clientData['cpf'] ?? '',          
            'state' => $clientData['estado'],
            'email' => $clientData['email'],
            'phone' => $clientData['telefone'],
            
        ],
        'shipping' => [
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'company' => $clientData['empresa'],  
            'address_1' => $clientData['endereco'],
            'address_2' => $clientData['complemento'], 
            'city' => $clientData['cidade'],
            'postcode' => $clientData['cep'],
            'country' => $clientData['pais'],
            'state' => $clientData['estado'],
            'phone' => $clientData['telefone'], 
            'cpf' => $clientData['cliente_cpf'],
        ],
        'meta_data' => [
            [
                'key' => '_billing_number',
                'value' => $clientData['numero_cliente']
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
                'value' => $clientData['cpf_cliente']
            ],
            [
                'key' => '_billing_cnpj',
                'value' => $clientData['cnpj_cliente']
            ],
            [
                'key' => '_billing_birthdate',
                'value' => $clientData['data_nascimento']
            ],
            [
                'key' => '_billing_gender',
                'value' => $clientData['genero']
            ],
            [
                'key' => '_billing_cellphone',
                'value' => $clientData['celular']
            ],
            [
                'key' => '_billing_company',
                'value' => $clientData['empresa']
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
    curl_close($ch);

    return json_decode($response, true); // Retorna o resultado da API
}

// Função para recuperar meta-dados do cliente
function getCustomerMetaData($customer_id) {
    global $woocommerce_url, $consumer_key, $consumer_secret;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $woocommerce_url . 'customers/' . $customer_id . '?consumer_key=' . $consumer_key . '&consumer_secret=' . $consumer_secret);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Criar cliente e exibir na tela
echo '<pre>';
echo "Criando cliente...\n";
$clienteResult = createCustomerWooCommerce($clientData);
print_r($clienteResult);

if (isset($clienteResult['error'])) {
    echo "Erro ao criar cliente: " . $clienteResult['message'] . "\n";
    exit();
}

// Recuperar meta-dados do cliente
echo "\nRecuperando meta-dados do cliente...\n";
$customerMetaData = getCustomerMetaData($clienteResult['id']);
print_r($customerMetaData['meta_data']);

// Dados para criar um pedido
$orderData = [
    'customer_id' => $clienteResult['id'], 
    'cliente_nome' => $clientData['nome'],
    'cliente_email' => $clientData['email'],
    'cliente_telefone' => $clientData['telefone'],
    'cliente_endereco' => $clientData['endereco'],
    'cliente_cidade' => $clientData['cidade'],
    'cliente_estado' => $clientData['estado'],
    'cliente_cep' => $clientData['cep'],
    'cliente_pais' => $clientData['pais'],
    'cliente_cpf' => $clientData['cpf_cliente'],
    'cliente_cnpj' => $clientData['cnpj_cliente'],
    'cliente_complemento' => $clientData['complemento'],
    'cliente_numero' => $clientData['numero_cliente'],
    'cliente_bairro' => $clientData['bairro'],
    'cliente_empresa' => $clientData['empresa']
];

$orderItems = [
    [
        'product_id' => 37591363,
        'quantity' => 2,
        'total' => '34.90'
    ],
    [
        'product_id' => 37592268,
        'quantity' => 1,
        'total' => '29.90'
    ]
];

// Função para criar o pedido
function createOrderWooCommerce($orderData, $orderItems) {
    global $woocommerce_url, $consumer_key, $consumer_secret;

    $nameParts = splitName($orderData['cliente_nome']);

    $data = [
        'payment_method' => 'bacs',
        'payment_method_title' => 'Banco',
        'set_paid' => false,
        'customer_id' => $orderData['customer_id'],
        'billing' => [
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'company' => $orderData['cliente_empresa'],
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
            'company' => $orderData['cliente_empresa'],
            'address_1' => $orderData['cliente_endereco'],
            'city' => $orderData['cliente_cidade'],
            'state' => $orderData['cliente_estado'],
            'postcode' => $orderData['cliente_cep'],
            'country' => $orderData['cliente_pais']
        ],
        'line_items' => $orderItems,
        'status' => 'pending',
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
                'value' => $orderData['celular']
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
    curl_close($ch);

    return json_decode($response, true);
}

// Criar pedido e exibir na tela
echo "\nCriando pedido...\n";
$pedidoResult = createOrderWooCommerce($orderData, $orderItems);
print_r($pedidoResult);

if (isset($pedidoResult['error'])) {
    echo "Erro ao criar pedido: " . $pedidoResult['message'] . "\n";
}

echo '</pre>';