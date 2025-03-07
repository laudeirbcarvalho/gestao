<?php
// Conexão ao banco
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Função para enviar email
require PATH_MAIL . 'PHPMailer.php';
require PATH_MAIL . 'SMTP.php';
require PATH_MAIL . 'Exception.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = $_POST['cliente_id'] ?? null;
    $centralId = $_POST['central_id'] ?? null;

    if ($clienteId && $centralId) {
        // Verificar se o central_id existe e obter o email da central
        $stmtCentral = $conn->prepare("SELECT central_nome, central_email FROM central WHERE central_id = ?");
        $stmtCentral->bind_param("i", $centralId);
        $stmtCentral->execute();
        $result = $stmtCentral->get_result();
        $centralData = $result->fetch_assoc();
        $stmtCentral->close();

        if (!$centralData) {
            echo json_encode(['success' => false, 'message' => 'Central não encontrada.']);
            exit;
        }

        $centralNome = $centralData['central_nome'];
        $centralEmail = $centralData['central_email'];

         // Buscar o nome do cliente baseado no clienteId
         $stmtCliente = $conn->prepare("SELECT nome, email FROM clientes_shopify WHERE id_shopify = ?");
         $stmtCliente->bind_param("i", $clienteId);
         $stmtCliente->execute();
         $resultCliente = $stmtCliente->get_result();
         $clienteData = $resultCliente->fetch_assoc();
         $stmtCliente->close();
 
         if (!$clienteData) {
             echo json_encode(['success' => false, 'message' => 'Cliente não encontrado.']);
             exit;
         }
 
         $clienteNome = $clienteData['nome'];
         $clienteEmail = $clienteData['email'];

        // Atualizar a tabela clientes_shopify
        $stmt = $conn->prepare("UPDATE clientes_shopify SET central_id = ? WHERE id_shopify = ?");
        $stmt->bind_param("ii", $centralId, $clienteId);

        if ($stmt->execute()) {
            // Configurar informações do e-mail
            $empresa = NOME_EMPRESA;
            $email_empresa = EMAIL_EMPRESA;
            $operador = $_SESSION["nome"];
            $mailoperador = $_SESSION["email"];
            $whatsappoperador = $_SESSION["whatsapp"];
            $assunto = "Alteração de Central - Cliente Atualizado";

            $mensagem = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Alteração de Central</title>
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
                        background-color: #007BFF;
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
                    <h1>Alteração de Central</h1>
                    <p>Olá,</p>
                    <p>A central do cliente <strong>$clienteNome</strong> foi alterada para <strong>$centralNome</strong>.</p>
                    <p>Por favor, tome as providências necessárias para a administração deste cliente.</p>
                    <p>Este cliente se cadastrou no sistema mas ele deve ser atendido por você.</p>
                    <p>Acesse seu gestor para mais informações.</p>
                    <p>Atenciosamente,<br>$operador<br>
                    Equipe $empresa<br><br>
                    E-mail de Contato: $mailoperador<br>
                    Whatsapp: $whatsappoperador
                    </p>
                </div>
            </body>
            </html>
            ";

            // Configurar o PHPMailer
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
                $mail->addAddress($centralEmail, $centralNome);
                $mail->addReplyTo($mailoperador, $operador);

                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $mensagem;

                $mail->send();
                echo json_encode(['success' => true, 'message' => 'Central alterada com sucesso e e-mail enviado!']);
            } catch (Exception $e) {
                echo json_encode(['success' => true, 'message' => 'Central alterada, mas o e-mail não pôde ser enviado.', 'error' => $mail->ErrorInfo]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar a central.', 'error' => $conn->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    }

    $conn->close();
}
?>
