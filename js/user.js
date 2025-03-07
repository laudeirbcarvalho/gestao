// Lista os usuários quando clica no botão
function listarUser() {
    $.ajax({
        url: "sistema/system/user/listar_usuarios.php", // Arquivo PHP que retorna os usuários
        type: "GET",
        dataType: "html",
        beforeSend: function() {
            $("#listar-usuarios").html("<p>Carregando usuários...</p>");
        },
        success: function(response) {
            // Adiciona um parâmetro único ao URL das imagens para evitar cache
            const updatedResponse = response.replace(
                /<img[^>]+src="([^">]+)"/g, // Procura todas as tags <img>
                (match, src) => {
                    const uniqueParam = '?v=' + new Date().getTime(); // Adiciona um timestamp único
                    return match.replace(src, src + uniqueParam); // Atualiza o src da imagem
                }
            );

            $("#listar-usuarios").html(updatedResponse); // Exibe a lista de usuários atualizada
        },
        error: function() {
            $("#listar-usuarios").html("<p class='text-danger'>Erro ao carregar usuários.</p>");
        }
    });
}

// Hide the form initially
$('#formularioDeCadastro').hide();

$('#btnCadastrar').click(function () {

    $('#formularioDeCadastro').toggle();
     

});

// Cadastrar novo usuário
document.getElementById('formularioDeCadastro').addEventListener('submit', function (event) {
    event.preventDefault(); // Impede o envio do formulário

    // Aqui você pode adicionar a lógica para enviar os dados do formulário para o servidor usando AJAX
    const formData = new FormData(this);

    fetch('sistema/system/user/cadastrar_usuario.php', {
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
                showUsers();
                

            } else {
                alert('Erro ao cadastrar usuário: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao cadastrar usuário: ' + error.message);
        });

        showUsers();
});

// Deleta um Usuario
$(document).on('click', '.deletaruser', function () {
    const userId = this.getAttribute('data-id');

    if (confirm('Tem certeza que deseja excluir este usuário?')) {
        fetch('sistema/system/user/excluir_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuário excluído com sucesso!');
                // Aqui você pode atualizar a lista de usuários ou redirecionar para outra página
                showUsers();
            } else {
                alert('Erro ao excluir usuário.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir usuário.');
        });
    }
});

//Editar Usuario ( Popula os inputs com os dados do usuario)
$(document).on('click', '.edit-button', function () {
 
    const userId = $(this).data('id');
    const userNome = $(this).data('nome');
    const userEmail = $(this).data('email');
    const userWhatsapp = $(this).data('whatsapp');
    const userPermissao = $(this).data('permissao');
    const userCargo = $(this).data('cargo');
    const userAvatar = $(this).data('avatar');


    $('#edit-id').val(userId);
    $('#edit-nome').val(userNome);
    $('#edit-email').val(userEmail);
    $('#edit-whatsapp').val(userWhatsapp);
    $('#edit-permissao').val(userPermissao);
    $('#edit-cargo').val(userCargo);
    $('#edit-avatar').val(userAvatar);

    $('#edit-form-container').show();
    $("#btnCadastrar").hide();
    $('#formularioDeCadastro').hide();

    $('#edit-form-container')[0].scrollIntoView({
        behavior: 'smooth'
    });

    $('#formularioDeCadastro').hide();
});

//Altera os dados do usuario
$('#edit-form').on('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);
     
    fetch('sistema/system/user/alterar_usuario.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuário atualizado com sucesso!');
                $('#formularioDeCadastro').hide();
                // Lista os usuários atualizados
                showUsers();
            } else {
                alert('Erro ao atualizar usuário: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar usuário.');
        });
});

$('#cancel-edit').on('click', function () {
    $('#edit-form-container').hide();
    $("#btnCadastrar").show();
    
});


// Função para carregar a lista de usuários
function loadUserList() {
    fetch('sistema/system/user/listar_usuarios.php') // Certifique-se de que o caminho está correto
        .then(response => response.text())
        .then(data => {
            document.getElementById('listar-usuario-atualizado').innerHTML = data;
        })
        .catch(error => {
            console.error('Erro ao carregar a lista de usuários:', error);
        });
} 

//Função para alterar os dados do usuario logado
document.addEventListener('DOMContentLoaded', function () {
    // Função para validar a senha
    function validatePassword(password) {

        // Se a senha estiver vazia, retorna null
        if (password === '') {
            return null;
        }
        
        // Critérios de validação
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        if (password.length < minLength) {
            return 'A senha deve ter pelo menos 8 caracteres.';
        }
        if (!hasUpperCase) {
            return 'A senha deve conter pelo menos uma letra maiúscula.';
        }
        if (!hasLowerCase) {
            return 'A senha deve conter pelo menos uma letra minúscula.';
        }
        if (!hasNumber) {
            return 'A senha deve conter pelo menos um número.';
        }
        if (!hasSpecialChar) {
            return 'A senha deve conter pelo menos um caractere especial.';
        }

        return null; // Senha válida
    }

    // Função para salvar os dados do usuário
    window.saveUserData = function () {
        const userId = document.getElementById('user-id').value;
        const userName = document.getElementById('user-name').value;
        const userEmail = document.getElementById('user-email').value;
        const userPassword = document.getElementById('user-password').value;
        const userPasswordConfirm = document.getElementById('user-password-confirm').value;

        // Validação básica
        if (userPassword !== userPasswordConfirm) {
            alert('As senhas não coincidem.');
            return;
        }

        // Validação da senha
        const passwordError = validatePassword(userPassword);
        if (passwordError) {
            alert(passwordError);
            return;
        }

        // Cria um objeto com os dados do formulário
        const formData = {
            id: userId,
            name: userName,
            email: userEmail,
            password: userPassword
        };

        // Envia os dados para o servidor
        fetch('sistema/system/user/update_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Dados do usuário atualizados com sucesso!');
                    // Recarrega a página ou redireciona para outra página
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar os dados do usuário.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar os dados do usuário.');
            });
    };

    // Função para mostrar o dashboard (exemplo)
    window.showDashboard = function () {
        // Implementação para mostrar o dashboard
       // document.getElementById('edit-user').style.display = 'none';
        // Mostrar o dashboard ou outra seção
    };
});

