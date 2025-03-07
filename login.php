<?php
require_once('sistema/db.php');

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $senha = $_POST['senha'];

  // Prepara e executa a consulta
  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  //Usuario 1 do sistema o que vai administrar tudo
  $idMaster = 1;
  $stmte = $conn->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
  $stmte->bind_param("s", $idMaster);
  $stmte->execute();
  $resulte = $stmte->get_result();

  // Debug: Verifique o número de linhas retornadas
  //error_log("Número de usuários encontrados: " . $result->num_rows);



  if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();

    // Verifica a senha
    if (password_verify($senha, $usuario['senha'])) {
      // Armazena informações do usuário na sessão
      $_SESSION['usuario_id'] = $usuario['id'];
      $_SESSION['nome'] = $usuario['nome'];
      $_SESSION['email'] = $usuario['email'];
      $_SESSION['whatsapp'] = $usuario['whatsapp'];
      $_SESSION['permissao'] = $usuario['permissao'];
      $_SESSION['status'] = $usuario['status'];
      $_SESSION['avatar'] = $usuario['avatar'];
      $_SESSION['cargo'] = $usuario['cargo'];
      $_SESSION['central_id'] = $usuario['central_id'];

      //Armazena os dados do usuario master
      if ($resulte->num_rows > 0) {
        $usuarioe = $resulte->fetch_assoc();

        $_SESSION['usuario_idmaster'] = $usuarioe['id'];
        $_SESSION['nomemaster'] = $usuarioe['nome'];
        $_SESSION['emailmaster'] = $usuarioe['email'];
        $_SESSION['whatsappmaster'] = $usuarioe['whatsapp'];
      }

      // Redireciona para painel.php
      header("Location: painel.php");
      exit();
    } else {
      $erro = "Email ou senha incorretos.";
    }
  } else {
    $erro = "Email ou senha incorretos.";
  }
}

$conn->close();


require_once('sistema/template/header/header.php');

?>




<div class="login-container">

  <div class="background-slider">
    <div class="slide" style="background-image: url('img/ocean1.jpg');"></div>
    <div class="slide" style="background-image: url('img/ocean2.jpg');"></div>
    <div class="slide" style="background-image: url('img/ocean3.jpg');"></div>
  </div>

  <div class="login-content">
    <div class="bg-image d-flex justify-content-center align-items-center">
      <div class="col-3">
        <img width="100" class="img-fluid" src="<?=ICONE?>" alt="<?=NOME_EMPRESA?>" class="logo" />
      </div>
      <div class="col-9">
        <h1> <?=NOME_SISTEMA?></h1>
        <h5><?=NOME_EMPRESA?></h5>
        <h6><?=$dominio_atual?></h6>
      </div>
    </div>

    <?php if (isset($erro)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $erro; ?>
      </div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" name="email" placeholder="Digite seu email" required>
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" class="form-control" name="senha" placeholder="Digite sua senha" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Entrar</button>
    </form>

    <div class="bg-image d-flex justify-content-center align-items-center">
      <img width="100" class="img-fluid" src="<?=LOGO?>" alt="<?=NOME_EMPRESA?>" class="icone" />

    </div>
  </div>
</div>

<?php require('sistema/template/footer/footer.php'); ?>