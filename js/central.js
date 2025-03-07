//CENTRAL
//Cadastrar
function saveCentral() {
    const responsavel = document.getElementById("responsavel-name").value;
    const central_email = document.getElementById("central-email").value;
    const central_senha = document.getElementById("central-senha").value;
    const central_dominio = document.getElementById("central-dominio").value;
    const central_nome = document.getElementById("central-name").value;
    const central_whatsapp = document.getElementById("central-whatsapp").value;
  
    if (!responsavel || !central_email || !central_senha || !central_dominio || !central_nome || !central_whatsapp) {
        alert("Por favor, preencha todos os campos.");
        return;
    }
  
    fetch('sistema/system/central/save_central.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            responsavel,
            central_email,
            central_senha,
            central_dominio,
            central_nome,
            central_whatsapp
        }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Central salva com sucesso!");
                listarCentrais();
            } else {
                alert("Erro ao salvar central: " + (data.error || "Erro desconhecido."));
            }
        })
        .catch(error => {
            console.error("Erro ao salvar central:", error);
            alert("Ocorreu um erro inesperado.");
        });
  }
  //Alterar
  
    // Delegação de eventos para os botões "Alterar"
    $('#central-list-container').on('click', '.btn-alterar', function() {
        // Recupera os dados dos atributos data-*
        const id = $(this).data('id');
        const name = $(this).data('name');
        const responsavel = $(this).data('responsavel');
        const whatsapp = $(this).data('whatsapp');
        const email = $(this).data('email');
        const dominio = $(this).data('dominio');
  
        // Preenche o formulário com os dados
        $('#central-name').val(name);
        $('#responsavel-name').val(responsavel);
        $('#central-whatsapp').val(whatsapp);
        $('#central-email').val(email);
        $('#central-dominio').val(dominio);
  
        // Exibe o formulário (caso esteja oculto) e rola para ele
        $('#central-form').show();
        $('html, body').animate({
            scrollTop: $('#central-form').offset().top
        }, 500);
    });
   
  
  // Função para excluir central
  function deleteCentral(central_id) {
      if (confirm("Tem certeza que deseja excluir esta central?")) {
          fetch('sistema/system/central/excluir_central.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify({ central_id: central_id }),
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert("Central excluída com sucesso!");
                  listarCentrais(); // Atualiza a lista após excluir
              } else {
                  alert("Erro ao excluir central: " + data.error);
              }
          })
          .catch(error => {
              console.error("Erro ao excluir central:", error);
              alert("Ocorreu um erro ao tentar excluir a central.");
          });
      }
  }
  
  
  function listarCentrais() {
      fetch('sistema/system/central/listar_central.php')
          .then(response => response.text())
          .then(data => {
              document.getElementById('central-list-container').innerHTML = data;
          })
          .catch(error => {
              console.error("Erro ao listar centrais:", error);
              document.getElementById('central-list-container').innerHTML = '<p class="text-danger">Erro ao carregar a lista de centrais.</p>';
          });
  }
  
  document.addEventListener("DOMContentLoaded", function () {
      listarCentrais(); // Carrega a lista assim que a página é carregada
  });