<?php
require_once('sistema/db.php');
// Ativar a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

//Função para enviar o e-mail ao cliente após o cadastro
function enviarEmail($email_cliente, $nome_cliente, $empresa, $email_empresa, $tel_empresa, $whats_empresa)
{
    // ENVIA O EMAIL AO CLIENTE DE BOAS VINDAS E INSTRUÇÕES PARA ELE INSERIR O LOGIN E SENHA
    require '/home/adlux/gestao.adlux.com.br/phpmailer/src/PHPMailer.php';
    require '/home/adlux/gestao.adlux.com.br/phpmailer/src/SMTP.php';
    require '/home/adlux/gestao.adlux.com.br/phpmailer/src/Exception.php';

    
    $email_empresa = $email_empresa;
    $operador = "Robô Automação $empresa";
    $mailoperador = $email_empresa;
    $whatsappoperador = $whats_empresa;
    $assunto = "Cadastro Realzado - Bem-vindo ao " . $empresa . "!";
    $linksenha = SHOPIFY_URL_LOJA_REDIRECIONA_CADASTRA_SENHA;

    $mensagem = "
  <html>
  <head>
      <meta charset='UTF-8'>
      <title>Cadastro Realizado no " . $empresa . "</title>
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
          <p>É com grande satisfação que informamos que seu cadastro foi realizado! Agora para você aproveitar as vantagens de ser um cliente do nosso " . NOME_SISTEMA . ", precisará apenas realizar o cadastro de sua senha em nossa loja.</p>
          <p><strong>Link de Acesso:</strong> $linksenha </p>
          <p> Ao acessar o link deste outro e-mail, insira sua senha, confirme a senha e receberá uma outra mensagem em seu e-mail com o titulo ( Confirmação da conta de cliente ). <br> Feito isso basta aguardar a liberação de acesso para você já ver os preços e comprar seus produtos. </p>
          <p><strong>Dúvidas:</strong> Se você tiver alguma dúvida ou precisar de assistência, não hesite em nos contatar.</p>
          <p>Agradecemos por escolher o " . $empresa . " e estamos ansiosos para atendê-lo!</p>
          <p>Atenciosamente, 
          Equipe $empresa<br><br>
          E-mail de Contato: $email_empresa <br>
          Whatsapp: $whats_empresa
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
$id_shopify = bin2hex(random_bytes(16)); 
$nome = $_POST['nome'];
$email = $_POST['email'];
$status = $_POST['status'];
$indicado = isset($_POST['indicado']) ? $_POST['indicado'] : 'Loja Virtual';
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
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];
$bairro = $_POST['bairro'];
$central_id = $_POST['central_id'];

$empresa = $_POST['empresa'];
$email_empresa = $_POST["email_empresa"];
$tel_empresa = $_POST["tel_empresa"];
$whats_empresa = $_POST["whats_empresa"];




//echo "AQUI". $central_id;

// Verifica se o email já existe no banco de dados
$stmt = $conn->prepare("SELECT * FROM clientes_shopify WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Resposta com o erro, com um alerta em JavaScript
    echo '<script type="text/javascript">
            alert("Email já cadastrado.");
            window.history.back();
          </script>';
    exit;
}


// Verificar se o id_shopify já existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM clientes_shopify WHERE id_shopify = ?");
$stmt->bind_param("s", $id_shopify);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Se o ID já existir, gere um novo ID
    $id_shopify = bin2hex(random_bytes(16)); // Gera um novo ID aleatório
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
        //echo json_encode(['success' => true, 'message' => 'Cliente '.$nome.' - '.$email.' cadastrado na Shopify, e-mail enviado com instruções para cadastrar a senha.']);


        // Enviar o e-mail de boas-vindas
        enviarEmail($email, $nome, $empresa, $email_empresa, $tel_empresa, $whats_empresa);
        $redirect = SHOPIFY_URL_LOJA_REDIRECIONA_CADASTRO;
        header("Location: $redirect");
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

    // URL para a API da Shopify REST para criar o cliente
    $url_rest = "https://$shopify_store/admin/api/2024-10/customers.json";

    // Dados para criar o cliente
    $data_rest = array(
        'customer' => array(
            'first_name' => $nome,
            'email' => $email,
            'addresses' => array(
                array(
                    'address1' => $endereco,
                    'city' => $cidade,
                    'province' => $estado,
                    'country' => $pais,
                    'phone' => $telefone,
                )
            )
        )
    );

    // Envia a requisição para criar o cliente
    $ch = curl_init($url_rest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_rest));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 201) { // Sucesso na criação do cliente
        $response_data = json_decode($response, true);
        $shopify_customer_id = $response_data['customer']['id'];

        // Formata o ID no formato GraphQL
        $graph_id = "gid://shopify/Customer/$shopify_customer_id";

        // URL para a API GraphQL
        $url_graphql = "https://$shopify_store/admin/api/2024-10/graphql.json";

        // Query GraphQL para enviar o convite
        $query = '
        mutation CustomerSendAccountInviteEmail($customerId: ID!) {
            customerSendAccountInviteEmail(customerId: $customerId) {
                customer {
                    id
                }
                userErrors {
                    field
                    message
                }
            }
        }';

        $variables = array(
            'customerId' => $graph_id
        );

        // Dados da requisição GraphQL
        $data_graphql = array(
            'query' => $query,
            'variables' => $variables
        );

        // Envia a requisição para o GraphQL
        $ch_graphql = curl_init($url_graphql);
        curl_setopt($ch_graphql, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_graphql, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch_graphql, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Shopify-Access-Token: $access_token"
        ));
        curl_setopt($ch_graphql, CURLOPT_POSTFIELDS, json_encode($data_graphql));

        $response_graphql = curl_exec($ch_graphql);
        $http_code_graphql = curl_getinfo($ch_graphql, CURLINFO_HTTP_CODE);
        curl_close($ch_graphql);

        if ($http_code_graphql === 200) { // Sucesso no envio do convite
            $response_data_graphql = json_decode($response_graphql, true);

            if (empty($response_data_graphql['data']['customerSendAccountInviteEmail']['userErrors'])) {
                return array(
                    'success' => true,
                    'shopify_customer_id' => $shopify_customer_id,
                    'message' => 'Convite enviado com sucesso!'
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $response_data_graphql['data']['customerSendAccountInviteEmail']['userErrors'][0]['message']
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => 'Erro ao enviar o convite via GraphQL.'
            );
        }
    } else {
        $error_message = json_decode($response, true)['errors'] ?? 'Erro desconhecido';
        return array('success' => false, 'message' => $error_message);
    }
}
