<?php
require_once('../../db.php');
require_once('../../../sistema/protege.php');


// Consulta para buscar as centrais cadastradas
$query = "SELECT id, central_id, central_nome, responsavel, central_whatsapp, central_email, superuser, central_dominio FROM central";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Central</th>';
    echo '<th>Responsável</th>';
    echo '<th>Email</th>';
    echo '<th>Ações</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        $mensagem = ($row['superuser'] == "superuser") ? 'Consegue ver a central e administrar.' : '';
        
        echo '<tr>';
        echo '<td><b>' . $row['central_id'] . '</b></td>';
        echo '<td>' . $row['central_nome'] . '<br><em>(' . $row['superuser'] . ') '.$mensagem.'</em> <hr> 
        <b>Trabalhos Automação: </b> <em>* configurar no Cpanel/hospedagem * fora do servidor do sistema usar a url completa</em> 
        <hr> <b> Atualiza estoque na Shopify: </b> <br>  /usr/bin/php -q /home/adlux/gestao.adlux.com.br/cron_shopify_estoque.php?cron_central=' . $row['central_id'] . '
        <hr><b> Atualiza os pedido da Shopify no sistema (global) : </b> <br>  /usr/bin/php -q /home/adlux/gestao.adlux.com.br/cron_shopify_pedidos.php 
        </td>';
        echo '<td>' . $row['responsavel'] . '<br><em>' . $row['central_whatsapp'] . '</em></td>';
        echo '<td>' . $row['central_email'] . ' <br>' . $row['central_dominio'] . '</td>';
        echo '<td>';
        echo '<button class="btn btn-warning btn-sm btn-alterar" 
                    data-id="' . $row['central_id'] . '" 
                    data-name="' . htmlspecialchars($row['central_nome'], ENT_QUOTES) . '" 
                    data-responsavel="' . htmlspecialchars($row['responsavel'], ENT_QUOTES) . '" 
                    data-whatsapp="' . htmlspecialchars($row['central_whatsapp'], ENT_QUOTES) . '" 
                    data-email="' . htmlspecialchars($row['central_email'], ENT_QUOTES) . '" 
                    data-dominio="' . htmlspecialchars($row['central_dominio'], ENT_QUOTES) . '">
                Alterar
              </button>';
        echo '<button class="btn btn-danger btn-sm" onclick="deleteCentral(' . $row['central_id'] . ')">Excluir</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p class="text-muted">Nenhuma central cadastrada.</p>';
}

$conn->close();
?>
 