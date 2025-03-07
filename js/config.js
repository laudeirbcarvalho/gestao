/** Teste de conexão com shopify dentro de Configurações no Sistema */
document.getElementById("test-connection-shopify").addEventListener("click", function () {
  const loadingScreen = document.getElementById("loading");
  loadingScreen.style.display = "flex";

  // Enviar dados para o PHP que já contém as variáveis configuradas
  fetch("sistema/system/config/test_connection_shopify.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({})
  })
    .then(response => {
      if (response.headers.get("Content-Type")?.includes("application/json")) {
        return response.json();
      } else {
        throw new Error("Resposta inválida do servidor. Verifique o endpoint.");
      }
    })
    .then(data => {
      loadingScreen.style.display = "none";
      if (data.success) {
        alert(data.message);
      } else {
        alert("Falha: " + data.message);
      }
    })
    .catch(error => {
      loadingScreen.style.display = "none";
      alert("Erro ao conectar: " + error.message);
    });
});

/** Teste de conexão com woocomerce dentro de Configurações no Sistema */
document.getElementById("test-connection-woo").addEventListener("click", function () {
  const consumerKey = document.getElementById("consumer-key").value;
  const consumerSecret = document.getElementById("consumer-secret").value;

  if (!consumerKey || !consumerSecret) {
      alert("Por favor, insira o Consumer Key e o Consumer Secret.");
      return;
  }

  // Exibir a tela de carregamento
  const loadingScreen = document.getElementById("loading");
  loadingScreen.style.display = "flex";

  fetch("sistema/system/config/test_connection_woocomerce.php", {
      method: "POST",
      headers: {
          "Content-Type": "application/json"
      },
      body: JSON.stringify({
          consumer_key: consumerKey,
          consumer_secret: consumerSecret
      })
  })
  .then(response => response.json())
  .then(data => {
      // Ocultar a tela de carregamento
      loadingScreen.style.display = "none";

      if (data.success) {
          alert(data.message);
      } else {
          alert("Falha: " + data.message);
      }
  })
  .catch(error => {
      // Ocultar a tela de carregamento
      loadingScreen.style.display = "none";
      alert("Erro ao conectar: " + error.message);
  });
});



function saveSettings() {
  // Seleciona o formulário pelo ID
  var form = document.getElementById('settings-form');

  // Verifica se o formulário existe
  if (!form) {
    console.error('Formulário com ID "settings-form" não encontrado!');
    return;
  }

  // Verifica se o formulário é válido (validação HTML5)
  if (!form.checkValidity()) {
    // Exibe as mensagens de validação do navegador
    form.reportValidity();
    return;
  }

  // Coleta os dados do formulário
  var settings = {
    nome_sistema: document.getElementById('system-name') ? document.getElementById('system-name').value : '',
    versao: document.getElementById('system-version') ? document.getElementById('system-version').value : '',
    nome_empresa: document.getElementById('company-name') ? document.getElementById('company-name').value : '',
    email_empresa: document.getElementById('company-email') ? document.getElementById('company-email').value : '',
    endereco_empresa: document.getElementById('company-address') ? document.getElementById('company-address').value : '',
    numero: document.getElementById('company-numero') ? document.getElementById('company-numero').value : '',
    bairro: document.getElementById('company-bairro') ? document.getElementById('company-bairro').value : '',
    cidade: document.getElementById('company-cidade') ? document.getElementById('company-cidade').value : '',
    estado: document.getElementById('company-estado') ? document.getElementById('company-estado').value : '',
    cep: document.getElementById('company-cep') ? document.getElementById('company-cep').value : '',
    documento: document.getElementById('company-documento') ? document.getElementById('company-documento').value : '',
    tel_empresa: document.getElementById('company-phone') ? document.getElementById('company-phone').value : '',
    whatsapp: document.getElementById('whatsapp_empresa') ? document.getElementById('whatsapp_empresa').value : '',
    login_email: document.getElementById('login-email') ? document.getElementById('login-email').value : '',
    senha_email: document.getElementById('smtp-password') ? document.getElementById('smtp-password').value : '',
    smtp_host: document.getElementById('smtp-host') ? document.getElementById('smtp-host').value : '',
    smtp_porta: document.getElementById('smtp-port') ? document.getElementById('smtp-port').value : '',
    smtp_email: document.getElementById('smtp-username') ? document.getElementById('smtp-username').value : '',
    smtp_senha: document.getElementById('smtp-password') ? document.getElementById('smtp-password').value : '',
    shopify_store: document.getElementById('shopify-store') ? document.getElementById('shopify-store').value : '',
    shopify_token: document.getElementById('shopify-token') ? document.getElementById('shopify-token').value : '',
    shopify_url_loja: document.getElementById('shopify-url') ? document.getElementById('shopify-url').value : '',
    shopify_url_cadastro: document.getElementById('shopify-url-cadastro') ? document.getElementById('shopify-url-cadastro').value : '',
    shopify_url_senha: document.getElementById('shopify-url-senha') ? document.getElementById('shopify-url-senha').value : '',
    habilitar_shopify: document.getElementById('habilitar_shopify') ? (document.getElementById('habilitar_shopify').checked ? 'sim' : 'nao') : '',
    url_sistema: document.getElementById('url-sistema') ? document.getElementById('url-sistema').value : '',
    pontuacao_maxima: document.getElementById('max-points') ? document.getElementById('max-points').value : '',
    nome_shopify: document.getElementById('nome-shopify') ? document.getElementById('nome-shopify').value : '',
    nome_woo: document.getElementById('nome-woo') ? document.getElementById('nome-woo').value : '',
    path_sistema: document.getElementById('path-sistema') ? document.getElementById('path-sistema').value : '',
    path_mail: document.getElementById('path-mail') ? document.getElementById('path-mail').value : '',
    woocommerce_url: document.getElementById('woocommerce-url') ? document.getElementById('woocommerce-url').value : '',
    woocommerce_url_cadastro: document.getElementById('woocommerce-url-cadastro') ? document.getElementById('woocommerce-url-cadastro').value : '',
    consumer_key: document.getElementById('consumer-key') ? document.getElementById('consumer-key').value : '',
    consumer_secret: document.getElementById('consumer-secret') ? document.getElementById('consumer-secret').value : '',
    descriptions: document.getElementById('descriptions-site') ? document.getElementById('descriptions-site').value : '',
    keywords: document.getElementById('keywords-site') ? document.getElementById('keywords-site').value : '',
    link_sobre: document.getElementById('link-sobre') ? document.getElementById('link-sobre').value : '',
    politicas_uso: document.getElementById('politicas-uso') ? document.getElementById('politicas-uso').value : '',
    nome_ead: document.getElementById('ead-name') ? document.getElementById('ead-name').value : '',
  };

  // Exibe os dados coletados no console para depuração
  console.log('Dados coletados:', settings);

  // Cria um objeto FormData para enviar arquivos e dados
  var formData = new FormData();

  // Coleta os arquivos de ícone e logo, se existirem
  var iconFile = document.getElementById('system-icon') ? document.getElementById('system-icon').files[0] : null;
  var logoFile = document.getElementById('system-logo') ? document.getElementById('system-logo').files[0] : null;

  // Adiciona os arquivos ao FormData, se existirem
  if (iconFile) {
    formData.append('system_icon', iconFile);
  }

  if (logoFile) {
    formData.append('system_logo', logoFile);
  }

  // Adiciona os dados do formulário ao FormData
  for (var key in settings) {
    if (settings.hasOwnProperty(key)) {
      formData.append(key, settings[key]);
    }
  }

  // Configura a requisição AJAX
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'sistema/system/config/save_settings.php', true);

  // Define a função que será chamada quando a requisição for concluída
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) { // Requisição concluída
      if (xhr.status === 200) { // Resposta OK
        try {
          // Tenta parsear a resposta como JSON
          var response = JSON.parse(xhr.responseText);
          if (response.success) {
            alert('Configurações salvas com sucesso!');
          } else {
            // Exibe erro no console e em um alerta
            console.error('Erro ao salvar configurações:', response.message || 'Erro desconhecido.');
            alert('Erro ao salvar as configurações: ' + (response.message || 'Erro desconhecido.'));
          }
        } catch (e) {
          // Se a resposta não for um JSON válido
          console.error('Resposta inválida do servidor:', xhr.responseText);
          alert('Erro ao processar a resposta do servidor.');
        }
      } else {
        // Se houver erro na requisição
        console.error('Erro na requisição:', xhr.status, xhr.statusText);
        alert('Erro ao salvar as configurações: ' + xhr.status + ' ' + xhr.statusText);
      }
    }
  };

  // Envia os dados via AJAX
  xhr.send(formData);
  console.log('Requisição AJAX enviada');
}

// Exibe a imagem do ícone e logo no formulário de config
function previewImage(inputId, previewId) {
  var file = document.getElementById(inputId).files[0];
  var preview = document.getElementById(previewId);

  if (file) {
    var reader = new FileReader();

    reader.onload = function (e) {
      preview.src = e.target.result; // Define o caminho da imagem
      preview.style.display = 'block'; // Exibe a imagem
    };

    reader.readAsDataURL(file); // Lê o arquivo como URL de dados
  }
}

