<?php
require_once('sistema/db.php');
$evento    = $_GET["evento"] ?? null;//Titulo do Evento
$parametro = $_GET["parametro"] ?? null;//Parametro para diferenciar os eventos
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Captação de Leads <?= NOME_EMPRESA ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    .form-container {
      width: 100%;
      max-width: 500px;
      background:rgba(255, 255, 255, 0.23);
      padding: 50px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .form-container img{
      width: 100%;
    }
    .fundo{
      background-image: url('/img/upload/leads/fundo.jpeg');
      background-size: cover;

    }
    .form-label{
      font-weight: bold;
    }
  </style>
</head>

<body class="fundo">

  <div class="form-container">
    <img src="/img/upload/leads/logo.png" alt="" class="src">
    <h2 class="text-center mb-4"><?= $evento ?></h2>
    <form id="leadForm" method="POST" action="process_lead.php">
      <input type="hidden" id="campanha" name="campanha" value="<?= $evento ?>">

      <div class="mb-3">
        <label for="nome" class="form-label">Nome:</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>

      <div class="mb-3">
        <label for="whatsapp" class="form-label">WhatsApp:</label>
        <input type="text" class="form-control" id="whatsapp" name="whatsapp" required>
      </div>

      <div class="mb-3">
        <label for="cidade" class="form-label">Cidade:</label>
        <input type="text" class="form-control" id="cidade" name="cidade" required>
      </div>

      <div class="mb-3">
        <label for="estado" class="form-label">Estado:</label>
        <select class="form-select" id="estado" name="estado" required>
          <option value="">Selecione o estado</option>
          <option value="AC">AC</option>
          <option value="AL">AL</option>
          <option value="AP">AP</option>
          <option value="AM">AM</option>
          <option value="BA">BA</option>
          <option value="CE">CE</option>
          <option value="DF">DF</option>
          <option value="ES">ES</option>
          <option value="GO">GO</option>
          <option value="MA">MA</option>
          <option value="MT">MT</option>
          <option value="MS">MS</option>
          <option value="MG">MG</option>
          <option value="PA">PA</option>
          <option value="PB">PB</option>
          <option value="PR">PR</option>
          <option value="PE">PE</option>
          <option value="PI">PI</option>
          <option value="RJ">RJ</option>
          <option value="RN">RN</option>
          <option value="RS">RS</option>
          <option value="RO">RO</option>
          <option value="RR">RR</option>
          <option value="SC">SC</option>
          <option value="SP">SP</option>
          <option value="SE">SE</option>
          <option value="TO">TO</option>
        </select>
      </div>

      <input type="hidden" id="central_id" name="central_id" value="<?= CENTRAL_ID ?>">

      <button type="submit" class="btn btn-success w-100">Enviar</button>
      <div id="loading" class="mt-3 text-center text-success" style="display: none;">Enviando...</div>
      <div id="status" class="mt-3 text-center"></div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/leads.js"></script>
</body>

</html>