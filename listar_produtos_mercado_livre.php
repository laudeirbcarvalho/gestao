<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$clientId = '8082294723227702';
$clientSecret = 'nIXRJ8znfYktE1i6QhGXmETv6jO0Kwxa';
$redirectUri = 'https://gestao.adlux.com.br/listar_produtos_mercado_livre.php';
$tokenFile = 'js/access_token.json'; // Arquivo para armazenar o token

// Função para obter o token de acesso
function getAccessToken($clientId, $clientSecret, $code, $redirectUri) {
    $url = "https://api.mercadolibre.com/oauth/token";

    $data = [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $code,
        'redirect_uri' => $redirectUri,
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        die("Erro ao obter o token de acesso: " . error_get_last()['message']);
    }

    $response = json_decode($result, true);
    if (isset($response['access_token'])) {
        return $response;
    }

    die("Erro na resposta da API ao obter o token: " . print_r($response, true));
}

// Função para renovar o token de acesso
function refreshToken($clientId, $clientSecret, $refreshToken) {
    $url = "https://api.mercadolibre.com/oauth/token";

    $data = [
        'grant_type' => 'refresh_token',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        die("Erro ao renovar o token: " . error_get_last()['message']);
    }

    $response = json_decode($result, true);
    if (isset($response['access_token'])) {
        return $response;
    }

    die("Erro ao renovar o token: " . print_r($response, true));
}

// Função para listar os produtos
function listProducts($accessToken) {
    $url = "https://api.mercadolibre.com/users/me/items/search?access_token={$accessToken}";
    $response = @file_get_contents($url);

    if ($response === false) {
        die("Erro ao listar os produtos: " . error_get_last()['message']);
    }

    $data = json_decode($response, true);

    // Verificação de erros na resposta
    if (isset($data['error'])) {
        die("Erro na resposta da API ao listar produtos: " . print_r($data, true));
    }

    if (isset($data['results']) && !empty($data['results'])) {
        foreach ($data['results'] as $itemId) {
            $itemUrl = "https://api.mercadolibre.com/items/{$itemId}?access_token={$accessToken}";
            $itemResponse = @file_get_contents($itemUrl);
            $itemDetails = json_decode($itemResponse, true);

            // Verificação se os detalhes do item existem
            if (isset($itemDetails['title'])) {
                echo "Título: " . htmlspecialchars($itemDetails['title']) . "<br>";
                echo "Preço: R$ " . number_format($itemDetails['price'], 2, ',', '.') . "<br>";
                echo "Estoque: " . htmlspecialchars($itemDetails['available_quantity']) . "<br><br>";
            } else {
                echo "Erro ao carregar os detalhes do produto. <br><br>";
            }
        }
    } else {
        echo "Nenhum produto encontrado.";
    }
}

// Fluxo principal
if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!isset($tokenData['access_token'])) {
        die("Token de acesso não encontrado.");
    }
    $accessToken = $tokenData['access_token'];

    // Verifica se o token expirou
    if (time() >= $tokenData['expires_at']) {
        $newTokenData = refreshToken($clientId, $clientSecret, $tokenData['refresh_token']);
        $newTokenData['expires_at'] = time() + $newTokenData['expires_in'];
        file_put_contents($tokenFile, json_encode($newTokenData));
        $accessToken = $newTokenData['access_token'];
    }
} elseif (isset($_GET['code'])) {
    $tokenData = getAccessToken($clientId, $clientSecret, $_GET['code'], $redirectUri);
    $tokenData['expires_at'] = time() + $tokenData['expires_in'];
    file_put_contents($tokenFile, json_encode($tokenData));
    $accessToken = $tokenData['access_token'];
} else {
    $authUrl = "https://auth.mercadolivre.com.br/authorization?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}";
    header("Location: $authUrl");
    exit;
}

// Listar os produtos
listProducts($accessToken);

?>
