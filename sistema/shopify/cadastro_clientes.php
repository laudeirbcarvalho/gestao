<?php
require_once('../../sistema/db.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

//Função para enviar o e-mail ao cliente após o cadastro
function enviarEmail($email_cliente, $nome_cliente)
{
    // ENVIA O EMAIL AO CLIENTE DE BOAS VINDAS E INSTRUÇÕES PARA ELE INSERIR O LOGIN E SENHA
    require PATH_MAIL . 'PHPMailer.php';
    require PATH_MAIL . 'SMTP.php';
    require PATH_MAIL . 'Exception.php';

    $empresa = NOME_EMPRESA;
    $email_empresa = EMAIL_EMPRESA;
    $operador = $_SESSION["nome"];
    $mailoperador = $_SESSION["email"];
    $whatsappoperador = $_SESSION["whatsapp"];
    $assunto = "Cadastro Realizado - Bem-vindo ao ".NOME_SISTEMA."!";
    $linksenha = SHOPIFY_URL_LOJA."/account/login#recover";

    $mensagem = "
  <html>
  <head>
      <meta charset='UTF-8'>
      <title>Cadastro Realizado no ".NOME_SISTEMA."</title>
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
          <h1>Cadastro Realizado</h1>
          <p>Prezado(a) $nome_cliente,</p>
          <p>É com grande satisfação que informamos que seu cadastro foi realizado! Agora para você aproveitar as vantagens de ser um cliente do nosso ".NOME_SISTEMA.", precisará apenas realizar o cadastro de sua senha em nossa loja.</p>
          <p><strong>Link de Acesso:</strong> $linksenha </p>
          <p> Ao acessar o link acima, insira o e-mail que você se cadastrou ( <b> $email_cliente </b>) e envie a mensagem para receber em seu e-mail as instruções de como realizar o cadastro de sua senha. </p>
          <p><strong>Dúvidas:</strong> Se você tiver alguma dúvida ou precisar de assistência, não hesite em nos contatar.</p>
          <p>Agradecemos por escolher o Salão Franqueado Adlux e estamos ansiosos para atendê-lo!</p>
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
        $mail->Host = HOST_SMTP; // Host do servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_SMTP; // Usuário SMTP
        $mail->Password = SENHA_SMTP; // Senha SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = PORTA_SMTP;

        // Defina o charset para UTF-8
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($email_empresa, $empresa);
        $mail->addAddress($email_cliente, $nome_cliente);
        $mail->addReplyTo($mailoperador, $operador);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;

        // Tenta enviar o e-mail
        $mail->send();

        // Mensagem de sucesso
       // echo json_encode(['success' => false, 'message' => 'E-mail enviado com sucesso.']);

    } catch (Exception $e) {
        // Captura o erro e exibe a mensagem        
      //  echo json_encode(['success' => false, 'message' => 'Erro ao enviar o e-mail.'.$mail->ErrorInfo]);
      //  exit();  // Interrompe o fluxo se o e-mail não puder ser enviado
    }
}

 
$shopify_store = SHOPIFY_STORE;
$access_token = SHOPIFY_TOKEN;

// Recebe os dados do formulário
$id_shopify = uniqid();
$nome = $_POST['nome'];
$email = $_POST['email'];
$status = $_POST['status'];
$indicado = $_POST['indicado'];
$data_cadastro = date('Y-m-d H:i:s');
$data_ativacao = null; // Defina como null ou atribua o valor necessário
$status_atualizacao = 'pendente'; // Status padrão
$endereco = $_POST['endereco'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$pais = $_POST['pais'];
$cep = $_POST['cep'];
$telefone = $_POST['telefone'];
$data_nascimento = $_POST['data_nascimento'];
$cpf = $_POST['cpf'];
$cnpj = $_POST['cnpj'];
$empresa = $_POST['empresa'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];
$bairro = $_POST['bairro'];
$central_id = $_POST['central_id'];

// Verifica se o email já existe no banco de dados
$stmt = $conn->prepare("SELECT * FROM clientes_shopify WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email já cadastrado.']);
    exit;
}

// Insere o cliente no banco de dados
$stmt = $conn->prepare("
INSERT INTO clientes_shopify 
(id_shopify, nome, email, status, indicado, data_cadastro, data_ativacao, status_atualizacao, endereco, cidade, estado, pais, cep, telefone, data_nascimento, cpf, cnpj, numero, complemento, bairro, central_id, empresa)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Vincula os parâmetros
$stmt->bind_param(
    "ssssssssssssssssssssss",
    $id_shopify,
    $nome,
    $email,
    $status,
    $indicado,
    $data_cadastro,
    $data_ativacao,
    $status_atualizacao,
    $endereco,
    $cidade,
    $estado,
    $pais,
    $cep,
    $telefone,
    $data_nascimento,
    $cpf,
    $cnpj,
    $numero,
    $complemento,
    $bairro,
    $central_id,
    $empresa
);

// Executa o comando e verifica o resultado
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro ao inserir cliente no banco de dados: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

// Agora, tenta registrar o cliente na Shopify
$shopify_response = registrarClienteShopify($shopify_store, $access_token, $id_shopify, $nome, $email, $endereco, $cidade, $estado, $pais, $telefone);



if (!$shopify_response['success']) {
    // Se falhar na Shopify, retorna um erro e não faz commit final
    $message = isset($shopify_response['message']) ? json_encode($shopify_response['message']) : 'Erro desconhecido';
    echo json_encode(['success' => false, 'message' => 'Erro ao criar cliente na Shopify: ' . $shopify_store . ' ' . $message]);

    // Se necessário, pode excluir o cliente do banco de dados aqui (rollback)
    $conn->query("DELETE FROM clientes_shopify WHERE email = '$email'");

    $stmt->close();
    $conn->close();
    exit;
} else { // Se a criação na Shopify for bem-sucedida, prossegue
    // Se a criação na Shopify for bem-sucedida, pega o ID retornado
    $shopify_customer_id = $shopify_response['shopify_customer_id'];

    // Atualiza o campo id_shopify na base de dados
    $update_query = "UPDATE clientes_shopify SET id_shopify = '$shopify_customer_id' WHERE email = '$email'"; // Alterado para usar o email como filtro
    if ($conn->query($update_query) === TRUE) {
        $nomeshopify = NOME_SHOPIFY;
        echo json_encode(['success' => true, 'message' => 'Cliente '.$nome.' - '.$email.' cadastrado na '.$nomeshopify.', e-mail enviado com instruções para cadastrar a senha.']);
        // Enviar o e-mail de boas-vindas
        enviarEmail($email, $nome);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o ID do cliente na base de dados.']);
    }


}

$stmt->close();
$conn->close();



// Função para registrar cliente na Shopify
function registrarClienteShopify($shopify_store, $access_token, $id_shopify, $nome, $email, $endereco, $cidade, $estado, $pais, $telefone)
{
    // Mapeamento de países para seus códigos ISO
    $country_map = array(
        'Brasil' => 'BR',
        'United States' => 'US',
        'Canada' => 'CA',
        'United Kingdom' => 'GB',
        // Adicione outros países conforme necessário
    );

    // Verifique se o país fornecido está no mapa e substitua pelo código ISO
    $pais = isset($country_map[$pais]) ? $country_map[$pais] : $pais;

    // URL para a API da Shopify
    $url = "https://$shopify_store/admin/api/2023-07/customers.json";

    $data = array(
        'customer' => array(
            'first_name' => $nome,
            'email' => $email,
            'addresses' => array(
                array(
                    'address1' => $endereco,
                    'city' => $cidade,
                    'province' => $estado,
                    'country' => $pais, // Aqui estamos usando o código do país
                    'phone' => $telefone,
                )
            )
        )
    );

    // Inicia a requisição cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Executa a requisição e captura a resposta
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verifica o código de resposta HTTP
    if ($http_code === 201) { // Código de sucesso na Shopify

        $response_data = json_decode($response, true);
        $shopify_customer_id = $response_data['customer']['id'];

        return array(
            'success' => true,
            'shopify_customer_id' => $shopify_customer_id  // Retorna o ID do cliente
        );
    } else {
        // Se não for sucesso, captura a mensagem de erro
        $error_message = json_decode($response, true)['errors'] ?? 'Erro desconhecido';
        return array('success' => false, 'message' => $error_message);
    }
}




?>