
//Ativar Cliente no ShopiFy
function ativarCliente(id) {
  // Exibe uma mensagem de confirmação
  if (confirm('Tem certeza de que deseja ativar este cliente?')) {
    // Cria um objeto XMLHttpRequest
    var xhr = new XMLHttpRequest();

    // Configura a requisição
    xhr.open('POST', 'sistema/shopify/update_status_cliente_shopfy.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Define o que acontece quando a resposta é recebida
    xhr.onload = function () {
      if (xhr.status === 200) {
        // Exibe um alerta com o resultado
        alert(xhr.responseText);
        // Aqui você pode adicionar lógica para atualizar a interface, se necessário
      } else {
        alert('Erro ao ativar o cliente.');
      }
    };

    // Envia a requisição com o ID do cliente
    xhr.send('id_shopify=' + id);
  } else {
    // Ação cancelada pelo usuário
    alert('Ação cancelada.');
  }
}

//Lista os pedidos dos clientes na ShopiFy
function pedidosCliente(customer_id) {
  document.getElementById('import-clientes').style.display = 'none';
  document.getElementById('sumir').style.display = 'none';
  document.getElementById('mostrarpedidospendentes').style.display = 'none';

  // Carrega a lista de pedidos do cliente na div "content"
  fetch('sistema/shopify/lista_pedidos_cliente.php?customer_id=' + customer_id)
    .then((response) => response.text())
    .then((data) => {
      document.getElementById('content').innerHTML = data;

      // Adiciona eventos aos botões de ver detalhes e integrar Compre Adlux após carregar o conteúdo
      document.querySelectorAll('.ver-detalhes').forEach((button) => {
        button.addEventListener('click', () => {
          const pedidoId = button.getAttribute('data-pedido');
          verDetalhesPedido(pedidoId);
        });
      });

      document.querySelectorAll('.integrar-compre').forEach((button) => {
        button.addEventListener('click', () => {
          const pedidoId = button.getAttribute('data-pedido');
          integrar_pedido_compre(pedidoId);
        });
      });
    })
    .catch((error) => {
      alert('Erro ao carregar os pedidos: ' + error);
    });
}

//imprime Etiqueta Pedido Shopify
function printEtiquetaShopify(button) {
  var clientId = button.getAttribute('data-client-id');

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "sistema/shopify/gerar_etiqueta.php?client_id=" + clientId, true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      var printWindow = window.open('', '', 'width=600,height=400');
      printWindow.document.write(xhr.responseText);
      printWindow.document.close();
      printWindow.print();  // Imprime a etiqueta
    } else {
      alert('Erro ao buscar os dados do cliente. Status: ' + xhr.status + ', Response: ' + xhr.responseText);
    }
  };
  xhr.send();
}


function excluirCliente(customer_id) {
  if (confirm('Tem certeza que deseja excluir este cliente?')) {
    $.ajax({
      url: 'excluir_cliente_shopify.php',  // URL mantida como você mencionou
      type: 'POST',
      data: {
        customer_id: customer_id,
        action: 'delete'
      },
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert('Cliente excluído com sucesso!');
          location.reload();  // Para recarregar a página e refletir a exclusão
        } else {
          alert('Não é possível excluir o cliente (' + customer_id + ').  Ele possui pedidos associados.');
        }
      },
      error: function () {
        alert('Erro ao excluir o cliente.');
      }
    });
  }
}


$('#form-alterar-cliente').submit(function (event) {
  event.preventDefault(); // Evita o envio padrão do formulário

  alert('Alterou');
  var formData = {
    id_shopify: $('#id_shopify').val(),
    nome_cliente: $('#nome_cliente').val(),
    email_cliente: $('#email_cliente').val(),
    status: $('input[name="status"]:checked').val(),
    data_cadastro: $('#data_cadastro').val(),
    endereco: $('#endereco').val(),
    cidade: $('#cidade').val(),
    estado: $('#estado').val(),
    pais: $('#pais').val(),
    telefone: $('#telefone').val(),
    data_nascimento: $('#data_nascimento').val(),
    cpf: $('#cpf').val(),
    cnpj: $('#cnpj').val(),
    empresa: $('#empresa').val(),
    numero: $('#numero').val(),
    bairro: $('#bairro').val(),
    cep: $('#cep').val(),
    complemento: $('#complemento').val(),

  };

  $.ajax({
    url: 'sistema/shopify/alterar_clientes_shopify.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        alert('Dados do cliente atualizados com sucesso!');
        // Você pode adicionar código aqui para fechar o formulário ou atualizar a tabela de clientes
      } else {
        alert('Erro ao atualizar os dados do cliente: ' + empresa + response.error);
      }
    },
    error: function () {
      alert('Erro ao enviar os dados do cliente.');
    },
  });
});

//Importa os pedidos da shopify
// Seleciona o botão
const importPedidosBtn = document.getElementById('import-pedidos');

// Adiciona o event listener ao botão
importPedidosBtn.addEventListener('click', importarPedidosShopify);

// Função para importar os pedidos da Shopify
function importarPedidosShopify() {
  fetch('sistema/shopify/importar_pedidos_shopify.php')
    .then((response) => response.text())
    .then((data) => {
      document.getElementById('content').innerHTML = data;
    })
    .catch((error) => {
      alert('Erro ao importar os pedidos: ' + error);
    });
}

//Importa os clientes da shopify
// Seleciona o botão
const importClientesBtn = document.getElementById('import-clientes');

// Adiciona o event listener ao botão
importClientesBtn.addEventListener('click', importarClientesShopify);

// Função para importar os pedidos da Shopify
function importarClientesShopify() {
  fetch('sistema/shopify/importar_clientes_shopify.php')
    .then((response) => response.text())
    .then((data) => {
      document.getElementById('content').innerHTML = data;
    })
    .catch((error) => {
      alert('Erro ao importar os pedidos: ' + error);
    });
}

// Função para exibir os detalhes do pedido
function verDetalhesPedido(pedidoId) {
  fetch('sistema/shopify/detalhes_pedido_shopify.php?n_pedido=' + pedidoId)
    .then((response) => response.text())
    .then((data) => {
      // Remover modal anterior, se existir
      const existingModal = document.getElementById('detalhesPedidoModal');
      if (existingModal) {
        existingModal.remove();
      }

      // Criar estrutura do modal
      const modalHtml = `
        <div class="modal fade" id="detalhesPedidoModal" tabindex="-1" aria-labelledby="detalhesPedidoLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido #${pedidoId}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                ${data} <!-- Aqui é onde os detalhes do pedido serão exibidos -->
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>
      `;

      // Adicionar o modal ao body
      document.body.insertAdjacentHTML('beforeend', modalHtml);

      // Exibir o modal
      $('#detalhesPedidoModal').modal('show');

      // Remover modal após fechar para evitar acúmulo de elementos
      $('#detalhesPedidoModal').on('hidden.bs.modal', function () {
        $(this).remove();
      });

    })
    .catch((error) => {
      alert('Erro ao carregar os detalhes do pedido: ' + error);
    });
}


//Botão Abrir Formulário Cadastrar Cliente Shopify
$("#cadastrarsalao").click(function () {
  $("#formcadastrosalao").toggle();
  $("#content").toggle();
});

// Botão Cancelar Cadastro
$("#cancelarcadastrarsalao").click(function () {
  $("#formcadastrosalao").hide();
  $("#content").show();
});

// Botão Enviar Cadastro
$('#form-cadastrar-cliente').submit(function (event) {
  event.preventDefault(); // Evita o envio padrão do formulário

  // Captura os dados do formulário
  var formData = {
    id_shopify: $('#id_shopifyCad').val(),
    nome: $('#nomeclienteCad').val(),
    email: $('#emailclienteCad').val(),
    status: $('input[name="status"]:checked').val(),
    indicado: $('input[name="indicado"]:checked').val(),
    endereco: $('#enderecoCad').val(),
    cidade: $('#cidadeCad').val(),
    estado: $('#estadoCad').val(),
    pais: $('#paisCad').val(),
    cep: $('#cepCad').val(),
    telefone: $('#telefoneCad').val(),
    data_nascimento: $('#data_nascimentoCad').val(),
    cpf: $('#cpfCad').val(),
    cnpj: $('#cnpjCad').val(),
    empresa: $('#empresaCad').val(),
    numero: $('#numeroCad').val(),
    complemento: $('#complementoCad').val(),
    bairro: $('#bairroCad').val(),
    central_id: $('#central_id').val(),
  };

  // Requisição AJAX
  $.ajax({
    url: 'sistema/shopify/cadastro_clientes.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        alert(response.message); // Exibe a mensagem correta

        // Limpa o formulário
        $('#form-cadastrar-cliente').trigger('reset');

        // Esconde o formulário e exibe o conteúdo
        $('#form-cadastrar-cliente').hide();
        $('#content').show();
      } else {
        alert('Erro: ' + response.message); // Exibe a mensagem de erro
      }
    },
    error: function (xhr, status, error) {
      console.error('Erro na requisição AJAX:', xhr.responseText);
      alert('Ocorreu um erro ao cadastrar o cliente.'); // Mensagem de erro genérica
    }
  });
});



// JavaScript para alternar a exibição do container dos botões IMPORTAR SHOPIFY
document.getElementById('toggle-import-buttons').addEventListener('click', function () {
  const container = document.getElementById('import-buttons-container');
  container.classList.toggle('d-none'); // Adiciona ou remove a classe d-none
});

// Alteração de imagem
$(document).on('click', '.btn-alterar-imagem', function () {
  var button = $(this); // Botão clicado
  var sku = button.data('sku');
  var novaImagem = prompt('Insira o URL da nova imagem:');



  if (novaImagem) {
    $.ajax({
      url: 'sistema/shopify/alterar_imagem_produto_shopify.php',
      method: 'POST',
      data: {
        sku: sku,
        imagem: novaImagem
      },
      success: function (response) {
        try {
          var result = JSON.parse(response);
          // Localiza ou cria o elemento de retorno
          var retornoDiv = button.closest('tr').find('.retornoimagem');
          if (retornoDiv.length === 0) {
            retornoDiv = $('<td class="retornoimagem"></td>');
            button.closest('tr').append(retornoDiv);
          }

          // Exibe a mensagem de sucesso ou erro
          if (result.success) {
            retornoDiv.html('<div class="alert alert-success">' + result.success + '</div>');
          } else if (result.error) {
            retornoDiv.html('<div class="alert alert-danger">' + result.error + '</div>');
          }
        } catch (e) {
          console.error('Erro ao processar a resposta:', e);
        }
      },
      error: function (xhr) {
        // Localiza ou cria o elemento de retorno
        var retornoDiv = button.closest('tr').find('.retornoimagem');
        if (retornoDiv.length === 0) {
          retornoDiv = $('<td class="retornoimagem"></td>');
          button.closest('tr').append(retornoDiv);
        }

        var errorMessage = xhr.responseJSON && xhr.responseJSON.error
          ? xhr.responseJSON.error
          : 'Erro ao alterar a imagem.';
        retornoDiv.html('<div class="alert alert-danger">' + errorMessage + '</div>');
      }
    });
  }
});


//Altera o cliente de Central

$(document).on("change", ".central-select", function () {
  const clienteId = $(this).data("cliente-id");
  const novaCentralId = $(this).val();
  const novaCentralNome = $(this).find("option:selected").text();

  if (confirm(`Deseja alterar a central do cliente para "${novaCentralNome}"?`)) {
    $.ajax({
      url: "sistema/shopify/alterar_central_cliente_shopify.php", // Endpoint para atualizar o central_id
      type: "POST",
      data: { cliente_id: clienteId, central_id: novaCentralId },
      success: function (response) {
        if (response.success) {
          alert(response.message); // Mensagem de sucesso
          listarClientesAtualizados(); // Atualiza a lista
        } else {
          alert("Erro ao alterar a central: " + response.message); // Mensagem de erro
        }
      },
      error: function () {
        alert("Ocorreu um erro na comunicação com o servidor.");
      }
    });
  } else {
    $(this).val($(this).find(`option:contains("${centralAtualNome}")`).val());
  }
});


//LIsta os pedidos pendentes da shopify
document.getElementById('listarpedidospendentes').addEventListener('click', function () {
  const divPedidos = document.getElementById('mostrarpedidospendentes');
  divPedidos.style.display = 'block';  // Garante que a div será visível quando clicar para abrir
  divPedidos.innerHTML = '<p>Carregando pedidos pendentes...</p>';  // Exibe a mensagem de carregamento

  // Faz a requisição dos pedidos pendentes
  fetch('sistema/shopify/listar_pedidos_pendentes.php')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        if (data.data.length > 0) {
          let html = `
                      <table class="table table-bordered table-striped">
                          <thead>
                              <tr>
                                  <th>Nº Pedido</th>
                                  <th>Cliente</th>
                                  <th>Valor Total</th>
                                  <th>Data de Criação</th>
                                  <th>Ações</th> 
                              </tr>
                          </thead>
                          <tbody>`;
          data.data.forEach(pedido => {
            if (pedido.customer_name) {
              html += `
                          <tr>
                              <td>${pedido.n_pedido}</td>
                              <td>${pedido.customer_name || 'Não informado'}</td>
                              <td>R$ ${parseFloat(pedido.total_price).toFixed(2)}</td>
                              <td>${pedido.created_at}</td>
                              <td>
                                  <button class="btn btn-info" onclick="accessCustomer('${pedido.customer_name}')">Acessar Cliente</button>
                                  <button class="btn btn-warning" onclick="abrirModal('${pedido.customer_name}', '${pedido.n_pedido}')">Alterar Status</button>
                              </td>
                          </tr>`;
            }
          });
          html += `
                      </tbody>
                  </table>
                  <button id="closeButton" onclick="closeDiv()">Fechar</button>`;  // Botão de Fechar
          divPedidos.innerHTML = html;
        } else {
          divPedidos.innerHTML = '<p>Nenhum pedido pendente foi encontrado.</p>';
        }
      } else {
        divPedidos.innerHTML = `<p>Erro: ${data.message}</p>`;
      }
    })
    .catch(error => {
      divPedidos.innerHTML = `<p>Erro ao carregar os pedidos pendentes: ${error.message}</p>`;
    });
});

// Função para acessar o cliente e fazer a busca automaticamente
function accessCustomer(customerName) {
  // Faz a busca automaticamente, chamando showUsersShopify com o nome do cliente
  showUsersShopify(1, customerName);  // Chama a função de busca com o nome do cliente
}

// Função para fechar a div
function closeDiv() {
  const div = document.getElementById('mostrarpedidospendentes');
  if (div) {
    div.style.display = 'none';  // Fecha a div
  }
}



//LIsta os pedidos pagos da shopify
document.getElementById('listarpedidospagos').addEventListener('click', function () {
  const divPedidos = document.getElementById('mostrarpedidospagos');
  divPedidos.style.display = 'block';  // Garante que a div será visível quando clicar para abrir
  divPedidos.innerHTML = '<p>Carregando pedidos pagos na shopify...</p>';  // Exibe a mensagem de carregamento

  // Faz a requisição dos pedidos pendentes
  fetch('sistema/shopify/listar_pedidos_pagos.php')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        if (data.data.length > 0) {
          let html = `
                      <table class="table table-bordered table-striped">
                          <thead>
                              <tr>
                                  <th>Nº Pedido</th>
                                  <th>Cliente</th>
                                  <th>Valor Total</th>
                                  <th>Data de Criação</th>
                                  <th>Ações</th> 
                              </tr>
                          </thead>
                          <tbody>`;
          data.data.forEach(pedido => {
            html += `
                          <tr>
                              <td>${pedido.n_pedido}</td>
                              <td>${pedido.customer_name || 'Não informado'}</td>
                              <td>R$ ${parseFloat(pedido.total_price).toFixed(2)}</td>
                              <td>${pedido.created_at}</td>
                              <td>
                                  <button class="btn btn-info" onclick="accessCustomer('${pedido.customer_name}')">Acessar Cliente</button>
                                  <button class="btn btn-warning" onclick="abrirModal('${pedido.customer_name}', '${pedido.n_pedido}')">Alterar Status</button>
                              </td>
                          </tr>`;
          });
          html += `
                      </tbody>
                  </table>
                  <button id="closeButton" onclick="closeDivPago()">Fechar</button>`;  // Botão de Fechar
          divPedidos.innerHTML = html;
        } else {
          divPedidos.innerHTML = '<p>Nenhum pedido pendente foi encontrado.</p>';
        }
      } else {
        divPedidos.innerHTML = `<p>Erro: ${data.message}</p>`;
      }
    })
    .catch(error => {
      divPedidos.innerHTML = `<p>Erro ao carregar os pedidos pendentes: ${error.message}</p>`;
    });
});


// Função para fechar a div
function closeDivPago() {
  const div = document.getElementById('mostrarpedidospagos');
  if (div) {
    div.style.display = 'none';  // Fecha a div
  }
}

//Mudar status do pedido do cliente

function abrirModal(customerName, pedidoId) {
  const modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.id = 'statusModal';
  modal.tabIndex = '-1';
  modal.role = 'dialog';
  modal.setAttribute('aria-labelledby', 'statusModalLabel');
  modal.setAttribute('aria-hidden', 'true');

  const modalDialog = document.createElement('div');
  modalDialog.className = 'modal-dialog modal-lg';

  const modalContent = document.createElement('div');
  modalContent.className = 'modal-content';

  // Cabeçalho do Modal
  const modalHeader = document.createElement('div');
  modalHeader.className = 'modal-header bg-primary text-white';

  const title = document.createElement('h5');
  title.className = 'modal-title';
  title.id = 'statusModalLabel';
  title.innerText = `Alterar Status do Pedido ${pedidoId} de ${customerName}`;

  const closeButton = document.createElement('button');
  closeButton.className = 'btn-close';
  closeButton.type = 'button';
  closeButton.setAttribute('data-bs-dismiss', 'modal');
  closeButton.setAttribute('aria-label', 'Fechar');
  closeButton.onclick = fecharModal;

  modalHeader.appendChild(title);
  modalHeader.appendChild(closeButton);

  // Corpo do Modal
  const modalBody = document.createElement('div');
  modalBody.className = 'modal-body';

  // Campo de upload estilizado
  const fileGroup = document.createElement('div');
  fileGroup.className = 'mb-3';

  const fileLabel = document.createElement('label');
  fileLabel.htmlFor = 'comprovantePagamento';
  fileLabel.className = 'form-label fw-bold';
  fileLabel.innerText = 'Anexe o Comprovante de Pagamento (JPG, PNG, PDF)';

  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.className = 'form-control';
  fileInput.id = 'comprovantePagamento';
  fileInput.accept = '.jpg,.jpeg,.png,.pdf';

  fileGroup.appendChild(fileLabel);
  fileGroup.appendChild(fileInput);

  // Campo de nota
  const noteGroup = document.createElement('div');
  noteGroup.className = 'mb-3';

  const noteLabel = document.createElement('label');
  noteLabel.htmlFor = 'nota';
  noteLabel.className = 'form-label fw-bold';
  noteLabel.innerText = 'Adicionar Nota (opcional)';

  const notaTextarea = document.createElement('textarea');
  notaTextarea.className = 'form-control';
  notaTextarea.id = 'nota';
  notaTextarea.rows = '3'; // Defina a altura do campo de texto0 
  notaTextarea.placeholder = 'Digite uma observação sobre o pedido...';

  noteGroup.appendChild(noteLabel);
  noteGroup.appendChild(notaTextarea);

  // Botões estilizados
  const buttonGroup = document.createElement('div');
  buttonGroup.className = 'd-flex justify-content-between mt-4';

  const buttonPago = document.createElement('button');
  buttonPago.className = 'btn btn-success px-4';
  buttonPago.innerHTML = '<i class="fas fa-check-circle"></i> Mudar para Pago';

  buttonPago.onclick = function () {
    const file = fileInput.files[0];
    const nota = notaTextarea.value; // Obtém o valor da nota

    //Obriga a ter um arquivo
    //if (file) {
    alterarStatus('paid', customerName, pedidoId, file, nota);
    //} else {
    //  alert("Por favor, insira o comprovante de pagamento.");
    // }
  };

  const buttonCancelado = document.createElement('button');
  buttonCancelado.className = 'btn btn-danger px-4';
  buttonCancelado.innerHTML = '<i class="fas fa-times-circle"></i> Mudar para Cancelado';

  buttonCancelado.onclick = function () {
    const nota = notaTextarea.value; // Obtém o valor da nota
    alterarStatus('canceled', customerName, pedidoId, null, nota);
  };

  buttonGroup.appendChild(buttonPago);
  buttonGroup.appendChild(buttonCancelado);

  modalBody.appendChild(fileGroup);
  modalBody.appendChild(noteGroup);
  modalBody.appendChild(buttonGroup);

  // Montagem do Modal
  modalContent.appendChild(modalHeader);
  modalContent.appendChild(modalBody);
  modalDialog.appendChild(modalContent);
  modal.appendChild(modalDialog);
  document.body.appendChild(modal);

  // Exibir modal
  new bootstrap.Modal(modal).show();

  // Remover modal ao fechar
  modal.addEventListener('hidden.bs.modal', fecharModal);
}


// Função para fechar o modal
function fecharModal() {
  $('#statusModal').modal('hide'); // Fecha o modal corretamente
  $('.modal-backdrop').remove(); // Remove o fundo escuro
  document.body.classList.remove('modal-open'); // Remove a classe que impede o scroll
  document.getElementById('statusModal').remove(); // Remove o modal do DOM
}


// Função para alterar o status do pedido
function alterarStatus(status, customerName, pedidoId, file, nota) {
  if (!pedidoId) {
    alert("ID do pedido não encontrado.");
    return;
  }

  const formData = new FormData(); // Usando FormData para enviar arquivo
  formData.append('pedido_id', pedidoId);
  formData.append('status', status);
  formData.append('nota', nota);
  if (file) {
    formData.append('comprovante', file); // Adiciona o arquivo ao FormData
  }

  fetch('sistema/shopify/mudar_status_pedido.php', {
    method: 'POST',
    body: formData // Envia o FormData
  })
    .then(response => response.text())
    .then(data => {
      alert(data); // Exibe a resposta da operação
      fecharModal(); // Fecha o modal após a alteração
    })
    .catch(error => {
      console.error('Erro:', error);
      alert("Houve um erro ao atualizar o status.");
    });
}


//Função para imprimir o pedido em detalhes do pedido shopify

function printPage() {
  var printContent = document.getElementById('printArea').innerHTML;
  var originalContent = document.body.innerHTML;

  var printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write('<html><head><title>Imprimir Pedido</title>');

  // Adiciona o CSS do Bootstrap e outros estilos
  printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
  printWindow.document.write('<style> @media print { body { font-family: Arial, sans-serif; color: #000; } .btn, a, hr { display: none; } #printArea { margin: 0; padding: 10px; } h2, h3 { text-align: center; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #000; padding: 8px; text-align: left; } th { background-color: #f0f0f0; } } </style>');

  printWindow.document.write('</head><body>');
  printWindow.document.write(printContent);
  printWindow.document.write('</body></html>');

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}


 

/*
Consulta o rastreamento para pegar o codigo de barras
<?php
function consultarRastreamento($numeroRastreamento) {
    $url = "http://webservice.correios.com.br/service/rastrear/rastrear.php?objetos=" . $numeroRastreamento;

    // Inicializa a requisição cURL
    $ch = curl_init();
    
    // Configurações para a requisição cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Executa a requisição
    $response = curl_exec($ch);
    
    // Verifica se houve erro na requisição
    if (curl_errno($ch)) {
        echo 'Erro na requisição cURL: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);

    // Retorna a resposta da API
    return simplexml_load_string($response);
}

// Exemplo de uso
$numeroRastreamento = "SEU_NUMERO_DE_RASTREAMENTO"; // Substitua pelo número de rastreamento real
$dadosRastreamento = consultarRastreamento($numeroRastreamento);

if ($dadosRastreamento) {
    // Exibe as informações de rastreamento (exemplo)
    echo "<pre>";
    print_r($dadosRastreamento);
    echo "</pre>";
} else {
    echo "Não foi possível consultar o rastreamento.";
}
?>

*/







