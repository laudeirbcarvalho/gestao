//Grafico no Dashboard
function carregarGrafico() {
  $.ajax({
    url: 'sistema/dashboard/grafico_shopify.php',
    method: 'POST',
    data: { acao: 'carregar_grafico' },
    dataType: 'json',
    success: function (response) {
      var ctx = document.getElementById('graficoPedidos').getContext('2d');

      // Array de cores para as barras
      var cores = [
        'rgba(255, 99, 132, 0.6)', // Vermelho
        'rgba(54, 162, 235, 0.6)', // Azul
        'rgba(255, 206, 86, 0.6)', // Amarelo
        'rgba(75, 192, 192, 0.6)', // Verde
        'rgba(153, 102, 255, 0.6)', // Roxo
        'rgba(255, 159, 64, 0.6)'  // Laranja
      ];

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: response.labels,
          datasets: [{
            label: 'Total de Pedidos',
            data: response.pedidos,
            backgroundColor: cores, // Usa o array de cores aqui
            borderColor: cores.map(cor => cor.replace('0.6', '1')), // Bordas mais escuras
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: { // Adicione esta seção para a legenda interativa
            legend: {
              display: false,
              position: 'right', // Ou 'left', 'top', 'bottom'
              labels: {
                color: 'black' // Cor do texto da legenda
              }
            }
          }
        }
      });
    },
    error: function () {
      alert('Erro ao carregar os dados do gráfico.');
    }
  });
}

// Carrega o gráfico quando a página estiver pronta
$(document).ready(function () {
  carregarGrafico();
});



// Função para carregar os dados do gráfico de produtos via AJAX
function carregarGraficoProdutos() {
  $.ajax({
    url: 'sistema/dashboard/grafico_produtos.php',
    method: 'POST',
    data: { acao: 'carregar_grafico_produtos' },
    dataType: 'json',
    success: function (response) {
      var ctx = document.getElementById('graficoProdutos').getContext('2d');

      // Array de cores para cada fatia
      var cores = [
        'rgba(255, 99, 132, 0.6)', // Vermelho
        'rgba(54, 162, 235, 0.6)', // Azul
        'rgba(255, 206, 86, 0.6)', // Amarelo
        'rgba(75, 192, 192, 0.6)', // Verde
        'rgba(153, 102, 255, 0.6)', // Roxo
        'rgba(255, 159, 64, 0.6)'  // Laranja
      ];

      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: response.labels,
          datasets: [{
            label: 'Quantidade Vendida',
            data: response.quantidades,
            backgroundColor: cores, // Usa o array de cores aqui
            borderColor: cores.map(cor => cor.replace('0.6', '1')), // Bordas mais escuras
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: { // Adicione esta seção para a legenda interativa
            legend: {
              display: true,
              position: 'top', // Ou 'left', 'top', 'bottom'
              labels: {
                color: 'black' // Cor do texto da legenda
              }
            }
          }
        }
      });
    },
    error: function () {
      alert('Erro ao carregar os dados do gráfico de produtos.');
    }
  });
}

// Carrega o gráfico de produtos quando a página estiver pronta
$(document).ready(function () {
  carregarGraficoProdutos();
});


// Mapa 1: Produtos Mais Vendidos
// Wait for the DOM to fully load before running the code
document.addEventListener('DOMContentLoaded', function () {
  // Initialize the map
  var map1 = L.map('map1').setView([-14.2350, -51.9253], 4); // Center of Brazil, zoom level 4

  // Add a tile layer (the map's background)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map1);

  // Make an AJAX request to fetch data
  var xhr1 = new XMLHttpRequest();
  xhr1.open('GET', 'sistema/dashboard/mapa_produtos_vendidos_cidades.php', true); // Path to your PHP script
  xhr1.responseType = 'json'; // Expect JSON data

  xhr1.onload = function () {
    if (xhr1.status === 200) {
      var cidades = xhr1.response;
      console.log(cidades); // Check the data in the browser's console

      if (Array.isArray(cidades) && cidades.length > 0) {
        cidades.forEach(function (cidade) {
          var lat = parseFloat(cidade.lat);
          var lon = parseFloat(cidade.lon);

          if (!isNaN(lat) && !isNaN(lon)) {
            var marker = L.marker([lat, lon]).addTo(map1);

            marker.bindPopup(`<b>${cidade.cidade}</b><br>
                                            Total de Compras: ${cidade.total_compras}<br>
                                            Produto Mais Vendido: ${cidade.produto_mais_vendido || "Nenhum"}`);

          } else {
            console.warn("Coordenadas inválidas para a cidade:", cidade);
          }
        });
      } else {
        console.error("Erro: Resposta do servidor vazia ou inválida:", cidades);
        alert("Erro ao carregar os dados das cidades. Verifique o console.");
      }
    } else {
      console.error("Erro na requisição:", xhr1.status, xhr1.statusText);
      alert("Erro ao carregar os dados das cidades. Verifique o console.");
    }
  };

  xhr1.onerror = function () {
    console.error("Erro de rede ao realizar a requisição.");
    alert("Erro ao carregar os dados das cidades. Verifique o console.");
  };

  xhr1.send();
});


// Mapa 2: Salões Franqueados
document.addEventListener('DOMContentLoaded', function () { // Novo ouvinte de evento DOMContentLoaded
  var map2 = L.map('map2').setView([-14.2350, -51.9253], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map2);

  var xhr2 = new XMLHttpRequest();
  xhr2.open('GET', 'sistema/dashboard/salao_franqueado_brasil.php', true);
  xhr2.responseType = 'json';

  xhr2.onload = function () {
    if (xhr2.status === 200) {
      var saloes = xhr2.response;
      if (Array.isArray(saloes) && saloes.length > 0) {
        saloes.forEach(function (salao) {
          var lat = parseFloat(salao.lat);
          var lon = parseFloat(salao.lon);

          if (!isNaN(lat) && !isNaN(lon)) {
            var marker2 = L.marker([lat, lon]).addTo(map2); // Variável marker2
            marker2.bindPopup(`<b>${salao.cidade}</b> <br> 
                                                       Quant.: ${salao.quantidade_clientes}<br>
                                                       <!--Central: ${salao.central_nome}-->
                                                       <a href="detalhes_central.php?id=${salao.central_id}">Detalhes</a>`); // Popup específico para salões
          } else {
            console.warn("Coordenadas inválidas para o salão:", salao);
          }
        });
      } else {
        console.error("Erro: Resposta do servidor vazia ou inválida para salões:", saloes);
        alert("Erro ao carregar os dados dos salões. Verifique o console.");
      }
    } else {
      console.error("Erro na requisição para salões:", xhr2.status, xhr2.statusText);
      alert("Erro ao carregar os dados dos salões. Verifique o console.");
    }
  };

  xhr2.onerror = function () {
    console.error("Erro de rede ao realizar a requisição para salões.");
    alert("Erro ao carregar os dados dos salões. Verifique o console.");
  };

  xhr2.send();
});

/** Lista de cidades com clientes shopify */
$(document).ready(function () {
  $('#cidadesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'sistema/shopify/cidades_com_clientes_shopify.php', // Caminho do arquivo PHP que retorna os dados
      type: 'POST'
    },
    columns: [{
      data: 'cidade'
    },
    {
      data: 'estado'
    },
    {
      data: 'total_clientes'
    }
    ],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json",
      paginate: {
        previous: "<", // Texto do botão "Anterior"
        next: ">" // Texto do botão "Próximo"
      }
    },
    pagingType: "simple_numbers", // Limita a navegação a números essenciais
    pageLength: 3,
    lengthMenu: [3, 10, 25, 50]
  });
});


document.getElementById('btnImprimir').addEventListener('click', function () {
  // Fazer requisição AJAX para o arquivo print_produtos_cidades.php
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'sistema/dashboard/print_produtos_cidades.php', true);
  xhr.responseType = 'text'; // Espera uma resposta em formato HTML

  xhr.onload = function () {
    if (xhr.status === 200) {
      // Se a resposta for bem-sucedida, abrir uma janela para impressão
      var content = xhr.responseText;

      // Criar uma nova janela de impressão
      var printWindow = window.open('', '', 'height=500, width=800');

      // Definir o conteúdo da página de impressão
      printWindow.document.write('<html><head><title>Lista de Produtos por Cidade</title>');
      printWindow.document.write('<style>body { font-family: Arial, sans-serif; }</style>');
      printWindow.document.write('</head><body>');
      printWindow.document.write(content);
      printWindow.document.write('</body></html>');

      // Esperar a página ser carregada e disparar o comando de impressão
      printWindow.document.close();
      printWindow.print();
    } else {
      alert('Erro ao carregar os dados para impressão.');
    }
  };

  xhr.onerror = function () {
    alert('Erro de rede ao tentar carregar os dados para impressão.');
  };

  xhr.send();
});

//Muda o nome dentro do input do buscar clientes shopify

function atualizarPlaceholder(radio) {
    const inputBusca = document.querySelector('input[name="search"]');
    if (radio.value === "nome") {
        inputBusca.placeholder = "Buscar por nome";
    } else if (radio.value === "status") {
        inputBusca.placeholder = "Buscar por status";
    } else if (radio.value === "cidade") {
        inputBusca.placeholder = "Buscar por cidade";
    }
}

// Atualizar o placeholder inicial quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    const radioSelecionado = document.querySelector('input[name="busca"]:checked');
    atualizarPlaceholder(radioSelecionado);
});
