<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

// Configura o cabeçalho para JSON
header('Content-Type: application/json');

// Parâmetros da API Shopify
$shopify_store = SHOPIFY_STORE;  // A variável constante definida no db.php
$access_token = SHOPIFY_TOKEN;  // A variável constante definida no db.php

// Endpoint para testar a conexão
$url = "https://$shopify_store/admin/api/2023-04/orders.json?limit=1";
$headers = [
    "X-Shopify-Access-Token: $access_token",
    "Content-Type: application/json"
];

// Testar conexão usando cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success' => false, 'message' => "Erro cURL: $err"]);
    exit();
}

if ($http_code === 200) {
    echo json_encode(['success' => true, 'message' => 'Conexão bem-sucedida com a Shopify!']);
} else {
    echo json_encode(['success' => false, 'message' => "Erro na conexão. Código HTTP: $http_code"]);
}
