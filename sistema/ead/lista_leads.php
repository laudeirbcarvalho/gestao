<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Substitua CENTRAL_ID pelo valor real ou variável que você está utilizando.
$central_id = CENTRAL_ID; // Exemplo de central_id (adicione o valor correto)

$sql = "SELECT id, nome, email, whatsapp, cidade, estado, campanha, created_at FROM leads WHERE central_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $central_id);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se há resultados
if ($result->num_rows > 0) {
    echo '<h4 class="text-center my-4">Lista de Leads</h4>';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead>
            <tr>
                <th>Data do Cadastro</th>
                <th>Nome</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>Campanha</th>
                <th>Ações</th>
            </tr>
          </thead>';
    echo '<tbody>';

    // Exibe os dados
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nome']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['whatsapp']) . '</td>';
        echo '<td>' . htmlspecialchars($row['cidade']) . '</td>';
        echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
        echo '<td>' . htmlspecialchars($row['campanha']) . '</td>';
        echo '<td>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#crmModal" 
                        data-id="' . $row['id'] . '" data-nome="' . htmlspecialchars($row['nome']) . '">
                    Acessar CRM
                </button>
              </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p class="text-center">Nenhum lead encontrado.</p>';
}

$stmt->close();
$conn->close();
?>

<!-- Bootstrap CSS e JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal do Bootstrap -->
<div class="modal fade" id="crmModal" tabindex="-1" aria-labelledby="crmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crmModalLabel">Detalhes do CRM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="crmContent" class="text-center">
                    <p>Carregando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Script para carregar o conteúdo dinâmico no Modal -->
<script>
$(document).ready(function() {
    $('#crmModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nome = button.data('nome');

        $('#crmModalLabel').text('Detalhes do CRM de ' + nome);
        $('#crmContent').html('<p class="text-center">Carregando...</p>');

        $('#crmContent').load('sistema/template/content/ead/crm.php?id=' + id, function(response, status, xhr) {
            if (status == "error") {
                $('#crmContent').html('<p class="text-danger">Erro ao carregar o conteúdo.</p>');
            }
        });
    });

    $('#crmModal').on('hidden.bs.modal', function() {
        $('#crmContent').html('<p>Carregando...</p>');
    });
});
</script>
