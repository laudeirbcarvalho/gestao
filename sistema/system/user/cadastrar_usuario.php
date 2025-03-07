<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $data_cadastro = date('Y-m-d H:i:s'); // Data e hora atuais
    $status = 'ativo'; // Exemplo de status
    $whatsapp = $_POST['whatsapp'];
    $permissao = $_POST['permissao'];
    $cargo = $_POST['cargo']; // Corrigido para $cargo

    // Resgata o central_id da sessão
    $central_id = $_SESSION['central_id']; // Certifique-se de que o central_id está na sessão

    // Verifica se o email já existe no banco de dados
    $sqlCheckEmail = "SELECT COUNT(*) as count FROM usuarios WHERE email = ?";
    $stmtCheckEmail = $conn->prepare($sqlCheckEmail);
    $stmtCheckEmail->bind_param("s", $email);
    $stmtCheckEmail->execute();
    $resultCheckEmail = $stmtCheckEmail->get_result();
    $rowCheckEmail = $resultCheckEmail->fetch_assoc();

    if ($rowCheckEmail['count'] > 0) {
        // Se o email já existir, retorna uma resposta JSON de erro
        echo json_encode(['success' => false, 'message' => 'O email já está cadastrado.']);

        // Registra no log
        registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo
        ], 'Tentativa de cadastro com e-mail duplicado (' . $email . ').');

        exit; // Impede o cadastro
    }

    // Processa o upload da imagem de avatar
    $avatar = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../../../' . UPLOADS . 'avatares/'; // Diretório onde as imagens serão salvas
        $extensao = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION); // Pega a extensão do arquivo
        $nomeArquivo = $central_id . '-' . str_replace(' ', '-', $nome) . '.' . $extensao; // Compõe o nome do arquivo
        $uploadFile = $uploadDir . $nomeArquivo;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
            $avatar = $uploadFile;
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem de avatar']);

            // Registra no Log 
            registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
                'nome' => $nome,
                'email' => $email,
                'cargo' => $cargo
            ], 'Tentou Criar um novo usuário, mas a imagem (' . $avatar . ') não foi enviada.');

            exit;
        }
    } else {
        $avatar = $uploadDir . 'avatardefault.jpg';
    }

    // Limpa o link para salvar o caminho correto do avatar no banco de dados
    $avatar = str_replace("../../../", "", $avatar);

    // Prepara a consulta SQL para inserir os dados na tabela usuarios
    $sql = "INSERT INTO usuarios (central_id, nome, email, senha, data_cadastro, status, avatar, whatsapp, permissao, cargo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $central_id, $nome, $email, $senha, $data_cadastro, $status, $avatar, $whatsapp, $permissao, $cargo);
    // Executa a consulta
    if ($stmt->execute()) {

        //ENVIA O EMAIL
        require PATH_MAIL . 'PHPMailer.php';
        require PATH_MAIL . 'SMTP.php';
        require PATH_MAIL . 'Exception.php';

        $empresa = NOME_EMPRESA;
        $nomesistema = NOME_SISTEMA;
        $urlsistema = URL;
        $email_empresa = EMAIL_EMPRESA;
        $operador = $_SESSION["nome"];
        $mailoperador = $_SESSION["email"];
        $whatsappoperador = $_SESSION["whatsapp"];
        $assunto = "Cadastro Registrado - Bem-vindo ao $nomesistema !";
        $nome_cliente = $nome;
        $email_cliente = $email;



        $mensagem = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Cadastro Liberado</title>
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
                <h1>Cadastro Liberado</h1>
                
                <p>Prezado(a) $nome_cliente,</p>
                <p>É com grande satisfação que informamos que seu cadastro foi registrado! <b>Agora você já pode ajudar Adlux na Gestão.</b></p>

                <p><strong>Baixe o APP:</strong> Solicite ao Depto de TI para lhe informar como baixar o App $nomesistema na PlayStore ou App Store.</p>
                <p><strong>Acesse:</strong> Acesse pelo navegador $urlsistema.</p>
                <p><strong>Dúvidas:</strong> Se você tiver alguma dúvida ou precisar de assistência, não hesite em contatar o $email_empresa .</p>
                <p>Seja bem-vindo e aproveite todos os benefícios de trabalhar com organização!</p>
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
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar o e-mail para ' . $nome_cliente]);
            //Registra no Log 
            registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
                'nome' => $nome,
                'email' => $email,
                'cargo' => $cargo
            ], 'Tentou Criar um novo usuário, mas e-mail (' . $email_cliente . ') não foi enviada para o cliente (' . $nome_cliente . ').');
        }
        echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso!']);

        //Registra no Log 
        registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo
        ], 'Criou um novo usuário (' . $email_cliente . ') não foi enviada para o cliente (' . $nome_cliente . ').');
    } else {
        // Se ocorrer um erro, retorna uma resposta JSON de erro
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário: ' . $stmt->error]);
        //Registra no Log 
        registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo
        ], 'Tentou criar um novo usuário (' . $email_cliente . ') não foi enviada para o cliente (' . $nome_cliente . '), erro ' . $stmt->error . '.');
    }


    // Fecha a conexão
    $stmt->close();
    $stmtCheckEmail->close();
    $conn->close();
} else {
    // Se a requisição não for POST, retorna uma resposta JSON de erro
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido']);
    //Registra no Log 
    registrar_log($_SESSION['usuario_id'], 'criar', 'usuarios', null, [
        'nome' => $nome,
        'email' => $email,
        'cargo' => $cargo
    ], 'Tentou criar um novo usuário (' . $email_cliente . ') não foi enviada para o cliente (' . $nome_cliente . '), erro : Método de requisição inválido.');
}
