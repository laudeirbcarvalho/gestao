<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_shopify = $_POST['id_shopify'];

    // Obtenha os dados do cliente da sua base de dados
    $sql = "SELECT nome, email, status FROM clientes_shopify WHERE id_shopify = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_shopify);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $nome_cliente = $cliente['nome'];
        $email_cliente = $cliente['email'];

        // Informações da loja Shopify
        $shop_url = SHOPIFY_STORE; // Domínio da loja
        $access_token = SHOPIFY_TOKEN; // Token de acesso

        // Dados para a atualização da tag do cliente
        $data = [
            "customer" => [
                "id" => $id_shopify,
                "tags" => "ativo" // Adiciona a tag "ativo"
            ]
        ];

        // Inicializa a requisição cURL
        $ch = curl_init("https://$shop_url/admin/api/2023-01/customers/$id_shopify.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Shopify-Access-Token: ' . $access_token // Adiciona o token de acesso no cabeçalho
        ]);

        // Executa a requisição e obtém a resposta
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verifica se a atualização foi bem-sucedida
        if ($http_code == 200) {
            // Atualiza o status do cliente no banco de dados
            $update_sql = "UPDATE clientes_shopify SET status = 'ativo' , `data_ativacao` = NOW() WHERE id_shopify = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id_shopify);
            if ($update_stmt->execute()) {


                echo "Cliente ativado com Sucesso!";

                

                require PATH_MAIL . 'PHPMailer.php';
                require PATH_MAIL . 'SMTP.php';
                require PATH_MAIL . 'Exception.php';
                
                $empresa = NOME_EMPRESA;
                $email_empresa = EMAIL_EMPRESA;
                $operador = $_SESSION["nome"];
                $mailoperador = $_SESSION["email"];
                $whatsappoperador = $_SESSION["whatsapp"];
                $assunto = "Cadastro Aprovado - Bem-vindo ao ".NOME_SISTEMA."!";
                
                $mensagem = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Cadastro Aprovado</title>
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
                        <h1>Cadastro Aprovado</h1>
                        <p>Prezado(a) $nome_cliente,</p>
                        <p>É com grande satisfação que informamos que seu cadastro foi aprovado! Agora você já pode aproveitar as vantagens de ser um cliente do nosso Salão Franqueado.</p>
                        <p><strong>Desconto Exclusivo:</strong> Como cliente aprovado, você tem direito a um desconto especial em suas compras.</p>
                        <p><strong>Como Comprar:</strong> Visite nosso site ".SHOPIFY_URL_LOJA." clique em fazer login, insira seu login e senha e tenha acesso aos produtos com desconto.</p>
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
                    $mail->Password =  SENHA_SMTP; // Senha SMTP
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
                
                    $mail->send();
                    echo "E-mail enviado com sucesso para $nome_cliente.";
                } catch (Exception $e) {
                    echo "Erro ao enviar o e-mail para $nome_cliente. Mailer Error: {$mail->ErrorInfo}";
                }
                



            } else {
                echo "Cliente ativado na Shopify, mas erro ao atualizar o status no banco de dados.";
            }
            $update_stmt->close();
        } else {
            echo "Erro ao atualizar o cliente na Shopify: " . $response;
        }
    } else {
        echo "Cliente não encontrado na base de dados.";
    }

    $stmt->close();
}
$conn->close();
?>