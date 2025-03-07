<!-- agenda.php -->
<!-- agenda.php -->
<div class="container">
    <h3>Agenda</h3>
    <div class="d-flex flex-column gap-2">
        <button class="btn btn-success mb-2" id="btnCadastrarEvento">Cadastrar Evento</button>
        <button class="btn btn-info mb-2">Listar Evento</button>
    </div>

    <!-- Formulário de Cadastro (Oculto inicialmente) -->
    <div id="formCadastroEvento" class="mt-3 d-none">
        <h4>Cadastrar Evento</h4>
        <form id="eventoForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nomeEvento">Nome do Evento</label>
                <input type="text" class="form-control" id="nomeEvento" name="nomeEvento" required>
            </div>

            <div class="form-group">
                <label for="dataInicialEvento">Data Inicial do Evento</label>
                <input type="date" class="form-control" id="dataInicialEvento" name="dataInicialEvento" required>
            </div>

            <div class="form-group">
                <label for="dataFinalEvento">Data Final do Evento</label>
                <input type="date" class="form-control" id="dataFinalEvento" name="dataFinalEvento" required>
            </div>

            <div class="form-group">
                <label for="valorEvento">Valor do Evento</label>
                <input type="number" class="form-control" id="valorEvento" name="valorEvento" required>
            </div>

            <div class="form-group">
                <label for="descricaoEvento">Descrição do Evento</label>
                <textarea class="form-control" id="descricaoEvento" name="descricaoEvento" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="logoEvento">Logo do Evento</label>
                <input type="file" class="form-control-file" id="logoEvento" name="logoEvento">
            </div>

            <div class="form-group">
                <label for="bannerEvento">Banner de Fundo do Evento</label>
                <input type="file" class="form-control-file" id="bannerEvento" name="bannerEvento">
            </div>

            <button type="submit" class="btn btn-primary mt-3">Salvar Evento</button>
            <button type="button" class="btn btn-secondary mt-3" id="btnCancelar">Cancelar</button>
        </form>
    </div>
</div>


<script>
    $(document).ready(function () {
        // Exibe o formulário quando clicar em "Cadastrar Evento"
        $("#btnCadastrarEvento").click(function () {
            $("#formCadastroEvento").removeClass("d-none");
        });

        // Oculta o formulário quando clicar em "Cancelar"
        $("#btnCancelar").click(function () {
            $("#formCadastroEvento").addClass("d-none");
        });

        // Envia o formulário usando AJAX
        $("#eventoForm").submit(function (e) {
            e.preventDefault(); // Evita o recarregamento da página

            // Cria um objeto FormData para enviar arquivos
            var formData = new FormData(this);

            $.ajax({
                type: "POST",
                url: "sistema/ead/cadastrar_evento.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    alert(response); // Exibe a resposta do PHP
                    $("#formCadastroEvento").addClass("d-none"); // Oculta o formulário após o cadastro
                    $("#eventoForm")[0].reset(); // Reseta os campos do formulário
                },
                error: function () {
                    alert("Erro ao cadastrar o evento. Tente novamente.");
                }
            });
        });
    });

</script>