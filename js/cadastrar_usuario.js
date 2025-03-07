

// Hide the form initially
$('#formularioDeCadastro').hide();

 $('#btnCadastrar').click(function(){

  $('#formularioDeCadastro').toggle();

 });
 
 // Cadastrar novo usuário
 document.getElementById('formularioDeCadastro').addEventListener('submit', function(event) {
     event.preventDefault(); // Impede o envio do formulário

     // Aqui você pode adicionar a lógica para enviar os dados do formulário para o servidor usando AJAX
     const formData = new FormData(this);

     fetch('cadastrar_usuario.php', {
             method: 'POST',
             body: formData
         })
         .then(response => {
             if (!response.ok) {
                 throw new Error('Erro na requisição: ' + response.statusText);
             }
             return response.json();
         })
         .then(data => {
             if (data.success) {
                 alert('Usuário cadastrado com sucesso!');
                 // Limpar o formulário
                 this.reset();
                 // Ocultar o formulário
                 document.querySelector('#formularioDeCadastro').style.display = 'none';
                 // Atualizar a lista de usuários
                 loadUserList();
             } else {
                 alert('Erro ao cadastrar usuário: ' + data.message);
             }
         })
         .catch(error => {
             console.error('Erro:', error);
             alert('Erro ao cadastrar usuário: ' + error.message);
         });
 });

//Excluir usuario
document.addEventListener('DOMContentLoaded', function() {
  // Adicionar evento de clique aos botões de exclusão
  document.querySelectorAll('.delete-button').forEach(function(button) {
      button.addEventListener('click', function() {
    
          const userId = this.getAttribute('data-id');
          if (confirm('Tem certeza de que deseja excluir este usuário?')) {
              excluirUsuario(userId);
          }
      });
  });
});
function excluirUsuario(userId) {
  fetch('excluir_usuario.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'id=' + encodeURIComponent(userId)
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert('Usuário excluído com sucesso!');
          // Atualizar a lista de usuários
          loadUserList();
      } else {
          alert('Erro ao excluir usuário: ' + data.message);
      }
  })
  .catch(error => {
      console.error('Erro:', error);
      alert('Erro ao excluir usuário: ' + error.message);
  });
}

 // Função para carregar a lista de usuários
 function loadUserList() {
     fetch('listar_usuarios.php') // Certifique-se de que o caminho está correto
         .then(response => response.text())
         .then(data => {
             document.getElementById('listar-usuarios').innerHTML = data;
         })
         .catch(error => {
             console.error('Erro ao carregar a lista de usuários:', error);
         });
 } 