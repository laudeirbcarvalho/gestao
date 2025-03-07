<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');
// Exibir erros para diagnóstico
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

// Receber os parâmetros enviados pelo frontend
$data = json_decode(file_get_contents('php://input'), true);

// Parâmetros de conexão
$woocommerce_url = URL_WOO . '';
$consumer_key = $data['consumer_key'] ?? KEY_WOO;
$consumer_secret = $data['consumer_secret'] ?? SECRET_WOO;

if (!$consumer_key || !$consumer_secret) {
    echo json_encode(['success' => false, 'message' => 'Consumer Key ou Consumer Secret não fornecidos.']);
    exit();
}

// Testar conexão usando cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $woocommerce_url . "?consumer_key=$consumer_key&consumer_secret=$consumer_secret",
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
    echo json_encode(['success' => true, 'message' => 'Conexão bem-sucedida com o WooCommerce!']);
} else {
    $response_data = json_decode($response, true);
    $error_message = $response_data['message'] ?? 'Erro desconhecido';
    echo json_encode(['success' => false, 'message' => $error_message]);
}
