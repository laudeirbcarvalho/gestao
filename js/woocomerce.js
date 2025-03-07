//Atualiza o botão após fazer a integração do pedido com o woocmerce
function atualizarBotaoIntegracao(pedidoId, status, dataHora, operador) {
  const botaoIntegracao = document.getElementById(`integrar-pedido-${pedidoId}`);

  if (status === 'integrado') {
      botaoIntegracao.classList.remove('btn-secondary');
      botaoIntegracao.classList.add('btn-success');
      botaoIntegracao.innerHTML = `Pedido Integrado em ${dataHora}`;
      botaoIntegracao.disabled = true; // Desabilita o botão
  } else {
      botaoIntegracao.classList.remove('btn-success');
      botaoIntegracao.classList.add('btn-secondary');
      botaoIntegracao.innerHTML = 'Integrar Compre Adlux';
      botaoIntegracao.disabled = false; // Habilita o botão
  }
}

// Integração do Pedido no WooCommerce
// Integração do Pedido no WooCommerce
function integrar_pedido_compre(pedidoId) {
    if (!pedidoId) {
        alert('ID do pedido não fornecido!');
        return;
    }

    // Exibe o carregamento
    exibirLoading();

    // Chamar o arquivo criar_pedidos_woocomerce.php primeiro
    fetch('sistema/woocomerce/criar_pedidos_woocomerce.php?pedido_id=' + pedidoId)
        .then(response => {
            // Verifica se a resposta é JSON válido
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.statusText);
            }
            return response.json(); // Espera uma resposta JSON
        })
        .then(data => {
            console.log(data); // Log para depuração
            if (data.error) {
                throw new Error('Erro ao criar pedido: ' + data.message);
            } else {
                alert(data.message); // Mensagem de sucesso

                // Agora que o pedido foi criado com sucesso, chamamos integra_woocomerce.php
                return fetch('sistema/woocomerce/integra_woocomerce.php?pedido_id=' + pedidoId);
            }
        })
        .then(response => {
            // Verifica se a resposta é JSON válido
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.statusText);
            }
            return response.text(); // Processa a resposta da integração
        })
        .then(data => {
            console.log(data); // Log para depuração
            if (data.includes("Pedido já integrado anteriormente")) {
                const integradoInfo = data.split(":")[1].trim();
                const [dataHora, operador] = integradoInfo.split("Pedido Integrado");
                atualizarBotaoIntegracao(pedidoId, 'integrado', dataHora.trim(), operador.trim());
                alert(data);
            } else if (data.includes("Parâmetro 'pedido_id' não foi fornecido na URL") ||
                       data.includes("Pedido não encontrado na tabela pedidos_shopify")) {
                alert(data);
            } else {
                // Pedido integrado com sucesso
                atualizarBotaoIntegracao(pedidoId, 'integrado', '', '');
                alert('Pedido integrado com sucesso no sistema! Pedido ID: ' + pedidoId);
            }
        })
        .catch(error => {
            console.error('Erro durante o processo:', error);
            alert('Erro ao processar o pedido: ' + error.message);
        })
        .finally(() => {
            // Remover o carregamento somente após todas as operações
            removerLoading();
        });
}



// Exibe o indicador de carregamento
function exibirLoading() {
  if (!$('#loading-overlay').length) {
      $('body').append('<div id="loading-overlay">Carregando Aguarde criar o pedido no woocomerce...</div>');
  }
}

// Remove o indicador de carregamento
function removerLoading() {
  $('#loading-overlay').remove();
}

// Indicador de carregamento (CSS inline)
$(document).ready(function () {
  $('body').append(`
      <style>
          #loading-overlay {
              position: fixed;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background: rgba(0, 0, 0, 0.7);
              color: white;
              display: flex;
              justify-content: center;
              align-items: center;
              font-size: 24px;
              z-index: 9999;
          }
      </style>
  `);
});

// Listar Produtos Compre Adlux
$('#loading').hide();
$('#mostrarProdutos').on('click', function () {
  $('#loading').show(); // Exibe "Loading..."
  $('#listar_produtos_compre').empty(); // Limpa conteúdo anterior

  $.ajax({
      url: 'sistema/woocomerce/importar_produtos_woocommerce.php',
      method: 'GET',
      success: function (data) {
          $('#listar_produtos_compre').html(data);
      },
      error: function () {
          $('#listar_produtos_compre').html('<p style="color: red;">Erro ao carregar produtos.</p>');
      },
      complete: function () {
          $('#loading').hide(); // Esconde "Loading..." após a conclusão
      }
  });
});

// Listar Produtos
$('#listarProdutos').on('click', function () {
  $('#loading').show(); // Exibe "Loading..."
  $('#listar_produtos_compre').empty(); // Limpa conteúdo anterior
  $('.painelcompre').hide(); // Esconde o painel do compre

  $.ajax({
      url: 'sistema/woocomerce/listar_produtos.php',
      method: 'GET',
      success: function (data) {
          $('#listar_produtos_compre').html(data);
      },
      error: function () {
          $('#listar_produtos_compre').html('<p style="color: red;">Erro ao carregar produtos.</p>');
      },
      complete: function () {
          $('#loading').hide(); // Esconde "Loading..." após a conclusão
      }
  });
});
