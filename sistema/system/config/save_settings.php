<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');

// Configura o cabeçalho JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados enviados via POST
    $nome_sistema = $_POST['nome_sistema'] ?? '';
    $versao = $_POST['versao'] ?? '';
    $nome_empresa = $_POST['nome_empresa'] ?? '';
    $email_empresa = $_POST['email_empresa'] ?? '';
    $tel_empresa = $_POST['tel_empresa'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_porta = $_POST['smtp_porta'] ?? '';
    $smtp_email = $_POST['smtp_email'] ?? '';
    $smtp_senha = $_POST['smtp_senha'] ?? '';
    $shopify_store = $_POST['shopify_store'] ?? '';
    $shopify_token = $_POST['shopify_token'] ?? '';
    $shopify_url_loja = $_POST['shopify_url_loja'] ?? '';
    $url_sistema = $_POST['url_sistema'] ?? '';
    $pontuacao_maxima = $_POST['pontuacao_maxima'] ?? '';
    $shopify_url_cadastro = $_POST['shopify_url_cadastro'] ?? '';
    $shopify_url_senha = $_POST['shopify_url_senha'] ?? '';
    $endereco_empresa = $_POST['endereco_empresa'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $login_email = $_POST['login_email'] ?? '';
    $senha_email = $_POST['senha_email'] ?? '';
    $whatsapp_empresa = $_POST['whatsapp_empresa'] ?? '';
    $nome_shopify = $_POST['nome_shopify'] ?? '';
    $nome_woo = $_POST['nome_woo'] ?? '';
    $path_sistema  = $_POST['path_sistema'] ?? '';
    $path_mail  = $_POST['path_mail'] ?? '';
    $woocommerce_url = $_POST['woocommerce_url'] ?? '';
    $woocommerce_url_cadastro = $_POST['woocommerce_url_cadastro'] ?? '';
    $consumer_key = $_POST['consumer_key'] ?? '';
    $consumer_secret = $_POST['consumer_secret'] ?? '';
    $descriptions = $_POST['descriptions'] ?? '';
    $keywords = $_POST['keywords'] ?? '';
    $link_sobre = $_POST['link_sobre'] ?? '';
    $politicas_uso = $_POST['politicas_uso'] ?? '';
    $central_id = CENTRAL_ID;
    $habilitar_shopify = $_POST['habilitar_shopify'] ?? '';
    $nome_ead = $_POST['nome_ead'] ?? '';

    // Buscar os valores atuais para system_icon e system_logo
    $querySelect = "SELECT system_icon, system_logo FROM config WHERE id = 1";
    $result = $conn->query($querySelect);

    if ($result && $row = $result->fetch_assoc()) {
        $icon_path = $row['system_icon'];
        $logo_path = $row['system_logo'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados atuais.']);
        exit();
    }

    // Lidar com o upload do ícone do sistema
    if (isset($_FILES['system_icon']) && $_FILES['system_icon']['error'] == 0) {
        $icon_name = $_FILES['system_icon']['name'];
        $icon_tmp = $_FILES['system_icon']['tmp_name'];
        $icon_ext = pathinfo($icon_name, PATHINFO_EXTENSION);
        $icon_path = 'img/' . uniqid('icon_') . '.' . $icon_ext;

        if (!move_uploaded_file($icon_tmp, $icon_path)) {
            echo json_encode(['success' => false, 'error' => 'Erro ao fazer upload do ícone.']);
            exit();
        }
    }

    // Lidar com o upload do logo do sistema
    if (isset($_FILES['system_logo']) && $_FILES['system_logo']['error'] == 0) {
        $logo_name = $_FILES['system_logo']['name'];
        $logo_tmp = $_FILES['system_logo']['tmp_name'];
        $logo_ext = pathinfo($logo_name, PATHINFO_EXTENSION);
        $logo_path = 'img/' . uniqid('logo_') . '.' . $logo_ext;

        if (!move_uploaded_file($logo_tmp, $logo_path)) {
            echo json_encode(['success' => false, 'error' => 'Erro ao fazer upload do logo.']);
            exit();
        }
    }
    
    // Atualizar as configurações no banco de dados
    $query = "UPDATE config SET
        nome_sistema = ?, 
        versao = ?, 
        nome_empresa = ?, 
        email_empresa = ?, 
        endereco_empresa = ?, 
        numero = ?,
        bairro = ?,
        cidade = ?,
        estado = ?,
        cep = ?,
        documento = ?,
        whatsapp = ?, 
        tel_empresa = ?, 
        login_email = ?, 
        senha_email = ?, 
        url_sistema = ?, 
        smtp_host = ?, 
        pontuacao_maxima = ?, 
        whatsapp_empresa = ?, 
        smtp_email = ?, 
        smtp_senha = ?, 
        smtp_porta = ?, 
        shopify_store = ?, 
        shopify_token = ?, 
        shopify_url_loja = ?, 
        shopify_url_cadastro = ?, 
        shopify_url_senha = ?, 
        nome_shopify = ?, 
        nome_woo = ?,
        system_icon = ?, 
        system_logo = ?,
        path_sistema = ?,
        path_mail = ?,
        woocommerce_url = ?,
        woocommerce_url_cadastro = ?,
        consumer_key = ?,
        consumer_secret = ?,
        descriptions = ?,
        keywords = ?,
        link_sobre = ?,
        politicas_uso = ?,
        habilitar_shopify = ?,
        nome_ead = ?
        WHERE central_id = $central_id";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssssssssssssss", 
            $nome_sistema, 
            $versao, 
            $nome_empresa, 
            $email_empresa, 
            $endereco_empresa, 
            $numero,
            $bairro,
            $cidade,
            $estado,
            $cep,
            $documento,
            $whatsapp, 
            $tel_empresa, 
            $login_email, 
            $senha_email, 
            $url_sistema, 
            $smtp_host, 
            $pontuacao_maxima, 
            $whatsapp_empresa, 
            $smtp_email, 
            $smtp_senha, 
            $smtp_porta, 
            $shopify_store, 
            $shopify_token, 
            $shopify_url_loja, 
            $shopify_url_cadastro, 
            $shopify_url_senha,
            $nome_shopify,
            $nome_woo,
            $icon_path, 
            $logo_path,
            $path_sistema,
            $path_mail,
            $woocommerce_url,
            $woocommerce_url_cadastro,
            $consumer_key,
            $consumer_secret,
            $descriptions,
            $keywords,
            $link_sobre,
            $politicas_uso,
            $habilitar_shopify,
            $nome_ead
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    $conn->close();
}
?>
