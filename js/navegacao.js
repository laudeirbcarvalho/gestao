//Inicia o painel

$(document).ready(function () {
  $('#formalterar').hide();
  $('#user-list').hide();
  $('#loading').hide();
  $('#formcadastrosalao').hide();
  $('#user-list').css('display', 'none');
  $('#compreadlux').hide();
  $('#adluxacademy').hide();
  $('#form-alterar-cliente').hide();
  $('#lojaadlux').hide();
  $('#corporativo').hide();
  $('#log').hide();
  $('#marketplce').hide();
  $('#central').hide();
  // $('#inicio').show();

  /*
  // Obtém o nome da página atual
  var currentPage = window.location.pathname.split('/').pop();

  // Verifica se a página é index.php
  if (currentPage === 'painel.php' || currentPage === '') {
    $('#dashboard').show();
  }
*/
});

//Clicar no botão shopify e sumir o class dashboard
/*
$(document).on('click', '#shopify', function () {
  $('.dashboard h3 , p').hide();

});
*/

// Função para mostrar a tela de loading
function showLoading() {
  const loadingScreen = document.getElementById('loading');
  loadingScreen.style.display = 'flex'; // Exibe a tela de loading
}

// Função para ocultar a tela de loading
function hideLoading() {
  const loadingScreen = document.getElementById('loading');
  loadingScreen.style.display = 'none'; // Oculta a tela de loading
}

// Função para mostrar a dashboard
function showDashboardInicial() {
  showLoading(); // Mostra a tela de carregamento

  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 2000); // Simula o tempo de carregamento (2 segundos)

  $('#inicio').show();
  $(
    '#central, #marketplace, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
}

/// Função para mostrar a lista de usuários
function showUsers() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $('#dashboard').hide();
  $('#user-list').show();
  $(
    '#central, #marketplace, #inicio, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
    listarUser(); // Carrega a lista de usuários
}

// Função para mostrar as configurações
function showSettings() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #edit-user, #compreadlux, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#settings').show();
}

// Função para mostrar a edição de usuário
function showEditUser() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #compreadlux, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#edit-user').show();
}

// Função para mostrar Compre Adlux
function showUsersCompre() {
  showLoading(); // Mostra a tela de carregamento
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#compreadlux').show();
}

// Função para mostrar Adlux Academy
function showUsersAcademy() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#adluxacademy').show();
}

// Função para mostrar Loja Adlux
function showUsersLoja() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #corporativo, #log',
  ).hide();
  $('#lojaadlux').show();
}

// Função para mostrar Corporativo Adlux
function showUsersCorporativo() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #lojaadlux, #log',
  ).hide();
  $('#corporativo').show();
}

// Função para mostrar Marketplace
function showUsersMarketplace() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#central, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#marketplace').show();
}

// Função para mostrar a lista de Clientes ShopiFy SALAO FRANQUEADO
function showUsersShopify(page = 1, search = '') {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)
  // Esconde o formulário e outras seções
  $('#inicio').hide();
  $(
    '#central, #marketplace, #inicio, #user-list, #settings, #edit-user, #compreadlux, #adluxacademy, #formalterar, #log',
  ).hide();

  // Exibe a seção "user-list-shopify"
  $('#user-list-shopify').show();

  // Carrega a lista de clientes usando AJAX do jQuery
  $.ajax({
    url: `sistema/shopify/listar_clientes_shopify.php?page=${page}&search=${encodeURIComponent(
      search,
    )}`, // Certifique-se de que o caminho está correto
    method: 'GET',
    success: function (response) {
      // Exibe os dados na div "content"
      $('#content').html(response);
      $('#inicio').hide();
    },
    error: function (xhr, status, error) {
      alert(`Erro ao carregar os clientes: ${error}`);
    },
  });
}

// Função para mostrar Marketplace
function showCentral() {
  showLoading(); // Mostra a tela de carregamento
   
  // Simula uma requisição ou ação (exemplo: carregar dados)
  setTimeout(function () {
    hideLoading(); // Esconde a tela de carregamento
  }, 1000); // Simula o tempo de carregamento (2 segundos)

  $(
    '#marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #lojaadlux, #corporativo, #log',
  ).hide();
  $('#central').show();
}

// Função para mostrar os logs
function showLog(page = 1) {
  // Esconde todas as outras seções
  $(
    '#central, #marketplace, #inicio, #dashboard, #user-list, #user-list-shopify, #settings, #edit-user, #compreadlux, #adluxacademy, #lojaadlux',
  ).hide();

  // Exibe a seção de logs
  $('#log').show();

  // Faz a requisição AJAX para buscar os logs
  $.ajax({
    url: 'log_sistema.php', // Arquivo PHP que retorna os logs
    method: 'GET',
    dataType: 'json',
    data: { page: page }, // Passa a página para a consulta
    success: function (data) {
      // Limpar os logs antigos na interface (se existirem)
      $('#log').html('');

      // Verifica se a resposta contém logs ou se está vazio
      if (data.message && data.message === 'Nenhum log encontrado.') {
        $('#log').html('<p>Nenhum log encontrado.</p>');
      } else {
        // Cria uma tabela para exibir os logs
        var logTable =
          '<table class="log-table table table-striped table-bordered"><thead><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Tabela</th><th>Descrição</th><th>Dados Antigos</th><th>Dados Novos</th></tr></thead><tbody>';

        // Loop através dos dados de logs e preencher a tabela
        data.logs.forEach(function (log) {
          logTable += '<tr>';
          logTable += '<td>' + log.data_hora + '</td>';
          logTable += '<td>' + log.usuario_nome + '</td>';
          logTable += '<td>' + log.acao + '</td>';
          logTable += '<td>' + log.tabela + '</td>';
          logTable += '<td>' + log.descricao + '</td>';
          logTable += '<td>' + formatJson(log.dados_antigos) + '</td>';
          logTable += '<td>' + formatJson(log.dados_novos) + '</td>';
          logTable += '</tr>';
        });

        logTable += '</tbody></table>';
        $('#log').html(logTable);

        // Adicionando a paginação
        var pagination = '<nav><ul class="pagination justify-content-center">';
        for (var i = 1; i <= data.totalPages; i++) {
          pagination +=
            '<li class="page-item ' +
            (i === data.currentPage ? 'active' : '') +
            '"><a class="page-link" href="javascript:void(0);" onclick="showLog(' +
            i +
            ')">' +
            i +
            '</a></li>';
        }
        pagination += '</ul></nav>';
        $('#log').append(pagination);
      }
    },
    error: function (xhr, status, error) {
      console.log('Erro ao buscar logs: ' + error);
      $('#log').html('<p>Erro ao carregar os logs.</p>');
    },
  });

  // Função para formatar os dados JSON
  function formatJson(jsonData) {
    if (jsonData && typeof jsonData === 'object') {
      return JSON.stringify(jsonData, null, 2); // Formata para exibição
    }
    return jsonData;
  }
}

//OUTRAS FUNÇÕES UTILITÁRIAS

// Função para o menu hamburguer
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('open');
}

document.querySelectorAll('.sidebar .nav-link').forEach((link) => {
  link.addEventListener('click', () => {
    // Só fecha se a sidebar estiver visível no mobile
    if (
      window.innerWidth <= 768 &&
      document.getElementById('sidebar').classList.contains('open')
    ) {
      toggleSidebar();
    }
  });
});

//Botão Voltar para pafina anterior
function goBack() {
  window.history.back();
}


//Janela modal do comprovante
function abrirModalComprovante(modalId, imagemSrc) {
  var modal = document.getElementById(modalId);
  var img = document.getElementById("img-" + modalId);
  img.src = imagemSrc; // Define a imagem no modal
  modal.style.display = "flex";
}

function fecharModalComprovante(modalId) {
  document.getElementById(modalId).style.display = "none";
}

// Fecha o modal ao clicar fora da imagem
window.onclick = function(event) {
  var modais = document.querySelectorAll(".custom-modal");
  modais.forEach(function(modal) {
      if (event.target === modal) {
          modal.style.display = "none";
      }
  });
};
