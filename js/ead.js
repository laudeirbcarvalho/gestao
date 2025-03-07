document.addEventListener("DOMContentLoaded", function () {
    const leadForm = document.getElementById('leadForm');

    if (leadForm) { // Verifica se o formulário existe
        leadForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');

            // Mostra o loading no botão
            submitButton.disabled = true;
            submitButton.textContent = "Enviando...";

            fetch(form.action, {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message); // Exibe mensagem de sucesso
                        form.reset(); // Limpa o formulário
                    } else {
                        alert(data.message); // Exibe mensagem de erro
                    }
                })
                .catch(error => {
                    alert("Ocorreu um erro ao enviar o formulário.");
                    console.error(error);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = "Enviar";
                });
        });
    }
});

$(document).ready(function () {
    $(".listarosleads").click(function () {
        // Exibe uma mensagem de carregamento enquanto processa a requisição
        $(".resultadoleads").html("<p>Carregando os leads...</p>");
        
        // Faz a requisição ao arquivo PHP
        $.ajax({
            url: "sistema/ead/lista_leads.php", // Arquivo que retorna a lista de leads
            type: "GET", // Método de requisição
            success: function (data) {
                // Insere o resultado na div com a classe resultadoleads
                $(".resultadoleads").html(data);
            },
            error: function (xhr, status, error) {
                // Exibe uma mensagem de erro caso algo dê errado
                $(".resultadoleads").html("<p>Ocorreu um erro ao listar os leads.</p>");
                console.error("Erro:", error);
            }
        });
    });
});

//Agenda
// Usando jQuery para carregar o arquivo agenda.php quando o modal é aberto
$('#agendaModal').on('show.bs.modal', function () {
    $('#agendaContent').load('sistema/template/content/ead/agenda.php');
  });
  
//Formulario de cadastro de evento
$(document).ready(function () {
    // Quando o botão "Cadastrar Evento" for clicado, exibe o formulário
    $("#btnCadastrarEvento").click(function () {
      $("#formCadastroEvento").removeClass("d-none");
    });
  
    // Quando o botão "Cancelar" for clicado, esconde o formulário
    $("#btnCancelar").click(function () {
      $("#formCadastroEvento").addClass("d-none");
    });
  
    // Simulação do envio do formulário (pode ser adaptado para AJAX)
    $("#eventoForm").submit(function (e) {
      e.preventDefault(); // Evita o recarregamento da página
      alert("Evento cadastrado com sucesso!");
      $("#formCadastroEvento").addClass("d-none"); // Oculta o formulário após o cadastro
      $("#eventoForm")[0].reset(); // Reseta os campos do formulário
    });
  });
  
  