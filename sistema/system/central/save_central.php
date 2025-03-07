<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Verifica se o usuário está logado e tem permissão para cadastrar
if (!isset($_SESSION['usuario_id']) || $_SESSION["permissao"] !== "admin") {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

// Configura o cabeçalho para JSON
header('Content-Type: application/json');


function gerarNumeroUnico($conn)
{
    do {
        $numero = random_int(100000, 999999);
        $query = "SELECT COUNT(*) AS total FROM central WHERE central_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $numero);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
    } while ($total > 0);

    return $numero;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não suportado.");
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['central_nome'], $data['responsavel'], $data['central_whatsapp'], $data['central_email'], $data['central_senha'], $data['central_dominio'])) {
        throw new Exception("Dados incompletos.");
    }

    $central_nome = $conn->real_escape_string($data['central_nome']);
    $responsavel = $conn->real_escape_string($data['responsavel']);
    $central_whatsapp = $conn->real_escape_string($data['central_whatsapp']);
    $central_email = $conn->real_escape_string($data['central_email']);
    $central_senha = password_hash($data['central_senha'], PASSWORD_BCRYPT);
    $central_dominio = $conn->real_escape_string($data['central_dominio']);

    // Verifica se o email já existe
    $query_email = "SELECT * FROM central WHERE central_email = ?";
    $stmt_email = $conn->prepare($query_email);
    $stmt_email->bind_param("s", $central_email);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    $central_existente = $result->fetch_assoc();
    $stmt_email->close();

    $mensagem_status = '';

    if ($central_existente) {
        // Verifica se os dados são diferentes
        $dados_diferentes = (
            $central_existente['central_nome'] !== $central_nome ||
            $central_existente['responsavel'] !== $responsavel ||
            $central_existente['central_whatsapp'] !== $central_whatsapp ||
            $central_existente['central_dominio'] !== $central_dominio
        );

        if ($dados_diferentes) {
            // Atualiza os dados da central existente
            $query_update = "UPDATE central SET central_nome = ?, responsavel = ?, central_whatsapp = ?, central_dominio = ? WHERE central_email = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param("sssss", $central_nome, $responsavel, $central_whatsapp, $central_dominio, $central_email);

            if (!$stmt_update->execute()) {
                throw new Exception("Erro ao atualizar central: " . $stmt_update->error);
            }

            $mensagem_status = 'Central atualizada com sucesso.';
        } else {
            $mensagem_status = 'Nenhuma alteração necessária.';
        }
    } else {
        // Insere uma nova central
        $central_id = gerarNumeroUnico($conn);
        $query_insert = "INSERT INTO central (central_id, central_nome, responsavel, central_whatsapp, central_email, central_senha, central_dominio) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);

        if (!$stmt_insert) {
            throw new Exception($conn->error);
        }

        $stmt_insert->bind_param("issssss", $central_id, $central_nome, $responsavel, $central_whatsapp, $central_email, $central_senha, $central_dominio);

        if (!$stmt_insert->execute()) {
            throw new Exception("Erro ao inserir central: " . $stmt_insert->error);
        }

        $mensagem_status = 'Central criada com sucesso.';


        // Insere o usuário
        $query_usuario = "INSERT INTO usuarios (central_id, nome, email, senha, data_cadastro, status, avatar, whatsapp, permissao, cargo) 
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
        $stmt_usuario = $conn->prepare($query_usuario);
        $status = 1; // Ativo
        $avatar = ''; // Padrão, se necessário
        $permissao = 'admin';
        $cargo = 'Administrador';

        if (!$stmt_usuario) {
            throw new Exception($conn->error);
        }

        $stmt_usuario->bind_param("isssissss", $central_id, $responsavel, $central_email, $central_senha, $status, $avatar, $central_whatsapp, $permissao, $cargo);

        if (!$stmt_usuario->execute()) {
            throw new Exception("Erro ao criar usuário: " . $stmt_usuario->error);
        }
        $stmt_usuario->close();
        // Config

        $query_config = "INSERT INTO config (
            central_id, 
            nome_sistema, 
            versao, 
            nome_empresa, 
            email_empresa, 
            endereco_empresa, 
            numero, 
            bairro, 
            cidade, 
            estado, 
            cep, 
            documento, 
            whatsapp, 
            tel_empresa, 
            login_email, 
            senha_email, 
            url_sistema, 
            smtp_host, 
            pontuacao_maxima, 
            whatsapp_empresa, 
            smtp_email, 
            smtp_senha, 
            smtp_porta, 
            shopify_store, 
            shopify_token, 
            shopify_url_loja, 
            shopify_url_cadastro, 
            shopify_url_senha, 
            nome_shopify, 
            nome_woo, 
            system_icon, 
            system_logo, 
            path_sistema, 
            path_mail, 
            woocommerce_url, 
            consumer_key, 
            consumer_secret, 
            descriptions, 
            keywords, 
            link_sobre, 
            politicas_uso
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        // Preparando a declaração
        $stmt_config = $conn->prepare($query_config);

        // Verificando se a preparação foi bem-sucedida
        if (!$stmt_config) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }

        // Atribuindo os valores às variáveis

        $nome_sistema = 'Gestão';
        $versao = '1.0';
        $nome_empresa = 'Nome da Empresa';
        $email_empresa = 'email@daempresa.com';
        $endereco_empresa = '';
        $numero = '';
        $bairro = '';
        $cidade = '';
        $estado = '';
        $cep = '';
        $documento = '';
        $whatsapp = '';
        $tel_empresa = '';
        $login_email = '';
        $senha_email = '';
        $url_sistema = '';
        $smtp_host = '';
        $pontuacao_maxima = NULL;
        $whatsapp_empresa = NULL;
        $smtp_email = NULL;
        $smtp_senha = NULL;
        $smtp_porta = 587;
        $shopify_store = NULL;
        $shopify_token = NULL;
        $shopify_url_loja = NULL;
        $shopify_url_cadastro = NULL;
        $shopify_url_senha = NULL;
        $nome_shopify = 'Shopify';
        $nome_woo = 'WooCommerce';
        $system_icon = '';
        $system_logo = '';
        $path_sistema = '';
        $path_mail = '';
        $woocommerce_url = '';
        $consumer_key = '';
        $consumer_secret = '';
        $descriptions = 'Você recebeu junto com este e-mail um segundo e-mail com o título (Ativação da conta de cliente).';
        $keywords = 'Shopify';
        $link_sobre = '';
        $politicas_uso = '';

        // Associando os valores à consulta usando bind_param
        $stmt_config->bind_param(
            "sssssssssssssssssssssssssssssssssssssssss",
            $central_id,
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
            $system_icon,
            $system_logo,
            $path_sistema,
            $path_mail,
            $woocommerce_url,
            $consumer_key,
            $consumer_secret,
            $descriptions,
            $keywords,
            $link_sobre,
            $politicas_uso
        );

        // Verificando se a execução foi bem-sucedida
        if (!$stmt_config->execute()) {
            throw new Exception("Erro ao criar config: " . $stmt_config->error);
        }

        // Fechando a declaração
        $stmt_config->close();

        // Mensagem de sucesso
        $mensagem_status = "Configuração criada com sucesso!";




    }




    // Enviar e-mail para a Central
    require PATH_MAIL . 'PHPMailer.php';
    require PATH_MAIL . 'SMTP.php';
    require PATH_MAIL . 'Exception.php';

    $empresa = NOME_EMPRESA;
    $email_empresa = EMAIL_EMPRESA;
    $operador = $_SESSION["nome"];
    $mailoperador = $_SESSION["email"];
    $whatsappoperador = $_SESSION["whatsapp"];
    $assunto = "Cadastro de Central Aprovado - Bem-vindo ao " . NOME_SISTEMA . "!";

    $mensagem = "
<html>
<head>
    <meta charset='UTF-8'>
    <title>Cadastro de Central Aprovado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        p {
            line-height: 1.6;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Cadastro de Central Aprovado</h1>
        <p>Prezado(a) $central_nome,</p>
        <p>É com grande satisfação que informamos que o cadastro da sua Central Administrativa foi aprovado! Agora você pode gerenciar e organizar suas operações com total autonomia.</p>
        <p><strong>Sobre a Central Administrativa:</strong> Nosso sistema permite gerenciar diversos painéis, cada um com suas próprias configurações e informações. Cada central possui um <strong>ID único</strong>, chamado de <strong>central_id</strong>, que assegura exclusividade e segurança para suas operações.</p>
        <p><strong>Como Funciona:</strong> 
        O <strong>central_id</strong> da sua nova central foi gerado automaticamente e é um número único de 6 dígitos. Este ID garante que todas as suas configurações, dados e senhas estejam segregados e protegidos de outras centrais.</p>
        <p><strong>Próximos Passos:</strong> 
        Acesse sua Central Administrativa no sistema " . NOME_SISTEMA . " e utilize seu painel exclusivo para configurar e gerenciar suas operações.</p>
        <p><strong>Dúvidas:</strong> Caso precise de assistência ou tenha alguma dúvida, nossa equipe está à disposição para ajudar.</p>
        <p>Agradecemos por escolher nosso sistema e esperamos que a experiência com sua nova Central Administrativa seja excepcional!</p>
        <p>Atenciosamente,<br>$operador<br>
        Equipe $empresa<br><br>
        E-mail de Contato: $mailoperador<br>
        Whatsapp: $whatsappoperador
        </p>
    </div>
</body>
</html>
";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = HOST_SMTP;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_SMTP;
        $mail->Password = SENHA_SMTP;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = PORTA_SMTP;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($email_empresa, $empresa);
        $mail->addAddress($central_email, $responsavel);
        $mail->addReplyTo($mailoperador, $operador);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;

        $mail->send();
        echo json_encode(['success' => true, 'message' => "$mensagem_status E-mail enviado para $responsavel."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => "Erro ao enviar o e-mail: {$mail->ErrorInfo}"]);
    }
} catch (Exception $e) {
    error_log("Erro no cadastro da central: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}


