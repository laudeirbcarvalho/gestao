<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
//include 'funcoes.php'; // Função para buscar os dados do cliente

// Função para buscar os dados do cliente
function getClientData($clientId) {
  global $conn; // Usando a conexão do db.php

  // Consultar os dados do cliente
  $sql = "SELECT id_shopify, nome, email, status, data_cadastro, data_ativacao, status_atualizacao, endereco, cidade, estado, pais, cep, telefone, data_nascimento, cpf, cnpj 
          FROM clientes_shopify 
          WHERE id_shopify = ?";

  // Preparando a consulta
  if ($stmt = $conn->prepare($sql)) {
      // Bind do parâmetro (id_shopify)
      $stmt->bind_param("i", $clientId);
      $stmt->execute();
      
      // Executar a consulta e obter o resultado
      $result = $stmt->get_result();
      
      // Verificar se o cliente foi encontrado
      if ($result->num_rows > 0) {
          $clientData = $result->fetch_assoc();
          return $clientData;
      } else {
          return null; // Nenhum cliente encontrado
      }
      
      $stmt->close();
  }

  // Caso algo dê errado, retornar null
  return null;
}

// Verificar se o ID do cliente foi passado
if (isset($_GET['client_id'])) {
  $clientId = $_GET['client_id'];
  $clientData = getClientData($clientId);

  if ($clientData) {
      // Gerar a etiqueta com os dados do cliente
      ?>
      <!DOCTYPE html>
      <html lang="pt-br">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Etiqueta Correios</title>
          <style>
              /* Definindo o estilo da etiqueta */
              .label {
                  width: 600px;
                  height: 300px;
                  padding: 20px;
                  font-family: Arial, sans-serif;
                  font-size: 14px;
                  border: 2px solid #000;
              }
              .header {
                  text-align: center;
                  font-size: 18px;
                  font-weight: bold;
                  margin-bottom: 20px;
              }
              .info {
                  margin-bottom: 10px;
              }
              .barcode {
                  margin-top: 20px;
                  text-align: center;
              }
          </style>
          <!-- Incluir a biblioteca JsBarcode -->
          <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
      </head>
      <body>
          <div class="label">
              <div class="header">Etiqueta de Envio - Correios</div>
              
              <!-- Remetente -->
              <div class="info"><strong>Remetente:</strong> <?php echo NOME_EMPRESA; ?></div>
              <div class="info"><strong>Endereço:</strong> <?php echo ENDERECO_EMPRESA . ', ' . NUMERO . ' - ' . CIDADE . ', ' . ESTADO . ' - CEP: ' . CEP; ?></div>
              <div class="info"><strong>Telefone:</strong> <?php echo WHATSAPP_EMPRESA; ?></div>
              <div class="info"><strong>CPF/CNPJ:</strong> <?php echo DOCUMENTO; ?></div>

              <br><br>

              <!-- Destinatário -->
              <div class="info"><strong>Destinatário:</strong> <?php echo $clientData['nome']; ?></div>
              <div class="info"><strong>Endereço:</strong> <?php echo $clientData['endereco'] . ', ' . $clientData['cidade'] . ' - ' . $clientData['estado'] . ', ' . $clientData['pais'] . ' - CEP: ' . $clientData['cep']; ?></div>
              <div class="info"><strong>Telefone:</strong> <?php echo $clientData['telefone']; ?></div>

              <br><br>

              <!-- Código de Barras -->
              <div class="barcode">
                  <!-- Gerando o código de barras com JsBarcode -->
                  <svg id="barcode"></svg>
                  <script>
                      // Gerar o código de barras com JsBarcode
                      JsBarcode("#barcode", "12345678901234567890", {
                          format: "CODE128",
                          lineColor: "#0aa",
                          width: 2,
                          height: 40,
                          displayValue: true
                      });
                  </script>
                  <p>Código de Rastreamento: 12345678901234567890</p>
              </div>
          </div>
      </body>
      </html>
      <?php
  } else {
      echo "Cliente não encontrado!";
  }
} else {
  echo "ID do cliente não fornecido.";
}
?>
