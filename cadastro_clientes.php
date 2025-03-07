<?php
require_once('sistema/db.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro <?=NOME_SHOPIFY?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('<?=URL?>/img/fundo_cadastro_clientes.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh; /* Garante altura mínima para centralização */
            margin: 0;
            display: flex;
            justify-content: left; /* Centraliza horizontalmente */
            align-items: flex-start; /* Inicia o formulário no topo */
        }
        .auth-container {
            max-width: 600px;
            padding: 20px;
            width: 90%;
        }
        .auth-section {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 30px;
        }
        .auth-section h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
            text-align: center;
        }
        .auth-section input:not([type="radio"]) {
            margin-bottom: 15px;
        }
        .auth-section button {
            width: 100%;
            padding: 12px;
            background-color: #000;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .auth-section label {
            display: block;
            margin-bottom: 5px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .auth-container {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-section">
       
            <h2>Cadastro Salão Franqueado Adlux</h2>            
            <form action="<?=URL?>/cadastro_clientes_form.php" method="POST">
                <input type="hidden" name="id_shopify">
                <input type="text" name="nome" placeholder="Nome Completo" required class="form-control">
                <input type="email" name="email" placeholder="E-mail" required class="form-control">
                <input type="text" name="endereco" placeholder="Endereço Completo" required class="form-control">
                <input type="text" name="numero" placeholder="Número" required class="form-control">
                <input type="text" name="complemento" placeholder="Complemento" class="form-control">
                <input type="text" name="bairro" placeholder="Bairro" required class="form-control">
                <input type="text" name="cidade" placeholder="Cidade" required class="form-control">
                <input type="text" name="estado" placeholder="Estado" required class="form-control">
                <input type="text" name="pais" value="Brasil" required class="form-control">
                <input type="text" name="cep" placeholder="CEP" required class="form-control">
                <input type="text" name="telefone" placeholder="Telefone" required class="form-control">
                <input type="date" name="data_nascimento" placeholder="Data de Nascimento" class="form-control">
                <input type="text" name="cpf" placeholder="CPF" required class="form-control">
                <input type="text" name="cnpj" placeholder="CNPJ" class="form-control">
                <input type="text" name="empresa" placeholder="Nome Salão" class="form-control">
                <label>Como ficou sabendo?</label>
                <label><input type="radio" name="indicado" value="instagram"> Instagram</label>
                <label><input type="radio" name="indicado" value="facebook"> Facebook</label>
                <label><input type="radio" name="indicado" value="landingpage"> Landingpage</label>
                <label><input type="radio" name="indicado" value="lojavirtual"> Loja Virtual</label>
                <label><input type="radio" name="indicado" value="google"> Google</label>
                <label><input type="radio" name="indicado" value="indicado"> Indicação</label>
                <button type="submit" class="btn btn-dark">Cadastrar</button>
                <input type="hidden" name="central_id" id="central_id" value="<?=CENTRAL_ID?>">
                <input type="hidden" name="empresa" id="central_id" value="<?=NOME_EMPRESA?>">
                <input type="hidden" name="email_empresa" id="central_id" value="<?=EMAIL_EMPRESA?>">
                <input type="hidden" name="tel_empresa" id="central_id" value="<?=TEL_EMPRESA?>">
                <input type="hidden" name="whats_empresa" id="central_id" value="<?=WHATSAPP_EMPRESA?>">
                
                <input type="hidden" name="status" id="status_inativo" value="inativo" checked>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>