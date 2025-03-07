<?php
require_once('../../../sistema/db.php');
require_once('../../../sistema/protege.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit();
}
 
// Recebe os dados JSON
$data = json_decode(file_get_contents('php://input'), true);

// Valida os dados
if (!isset($data['id'], $data['name'], $data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$userId = $data['id'];
$userName = $data['name'];
$userEmail = $data['email'];
$userPassword = isset($data['password']) && !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null;

 

// Prepara a consulta SQL
if ($userPassword) {
    $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $userName, $userEmail, $userPassword, $userId);
} else {
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $userName, $userEmail, $userId);
}

if ($stmt->execute()) {
    require PATH_MAIL . 'PHPMailer.php';
    require PATH_MAIL . 'SMTP.php';
    require PATH_MAIL . 'Exception.php';

    $empresa = NOME_EMPRESA;
    $email_empresa = EMAIL_EMPRESA;
    $operador = $_SESSION["nome"];
    $mailoperador = $_SESSION["email"];
    $whatsappoperador = $_SESSION["whatsapp"];
    $assunto = "Dados Atualizados - ". NOME_SISTEMA;
    $nome_cliente = $userName;
    $email_cliente = $userEmail;
    $nomesistema = NOME_SISTEMA;

    $mensagem = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Dados Atualizados</title>
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
                background-color: #00F;
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
            .avatar {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                display: block;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Dados Atualizados</h1>

            <p>Prezado(a) $nome_cliente,</p>
            <p>Seus dados foram atualizados com sucesso na $nomesistema.</p>

            <p><strong>Dúvidas:</strong> Se você tiver alguma dúvida ou precisar de assistência, não hesite em contatar $empresa.</p>
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

        $mail->send();

        // Envia e-mail para o administrador
        $adminEmail = EMAIL_EMPRESA; // Substitua pelo e-mail do administrador
        $adminSubject = 'Usuário atualizou seus dados';
        $adminMessage = "O usuário $userName ($userEmail) atualizou seus dados.";

        $mail->clearAddresses();
        $mail->addAddress($adminEmail);
        $mail->Subject = $adminSubject;
        $mail->Body = $adminMessage;
        $mail->send();

        echo json_encode(['success' => true, 'message' => 'Dados do usuário atualizados com sucesso']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar o e-mail para ' . $nome_cliente]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar os dados do usuário']);
}

$stmt->close();
$conn->close();
?>
