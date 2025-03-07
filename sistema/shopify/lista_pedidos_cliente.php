<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
// Verifica se o pedido já foi integrado
function get_pedido_integrado_info($pedido_id)
{
    global $conn;

    // Primeiro, recupera as informações do pedido
    $sql = "SELECT status, data_hora, operador 
            FROM woo_comerce_integra 
            WHERE n_pedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status = $row['status'];
        $data_hora = date('d/m/Y H:i:s', strtotime($row['data_hora']));
        $operador = $row['operador'];

        

        // Verifica se o status é elegível para alteração e atualiza o pedido
        if ($status === 'pendente') { // Ajuste conforme o status esperado para alterar
            $update_sql = "UPDATE pedidos_shopify 
                           SET financial_status = 'paid' 
                           WHERE n_pedido = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("s", $pedido_id);

            if ($update_stmt->execute()) {
                // Alteração foi bem-sucedida
                $status = 'pago'; // Atualiza a variável local também, se necessário
            } else {
                // Lidar com erros na execução da consulta
                error_log("Erro ao atualizar o pedido: " . $update_stmt->error);
            }

            $update_stmt->close();
        }

        return array('status' => $status, 'data_hora' => $data_hora, 'operador' => $operador);
    } else {
        return null;
    }
}


// Função para listar os pedidos de um cliente do banco de dados
function list_orders_by_customer($customer_id)
{
    global $conn;

    // Busca o nome do cliente na tabela clientes_shopify
    $sql = "SELECT id_shopify,nome,telefone FROM clientes_shopify WHERE id_shopify = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $nome_cliente = $cliente['nome'];
        $whatsapp = $cliente['telefone'];
        $id_customer_shopify = $cliente['id_shopify'];
    } else {
        $nome_cliente = "Cliente desconhecido";
    }

    if ($whatsapp) {
        // Remover caracteres não numéricos, como espaços, parênteses e traços
        $whatsapptratar = preg_replace('/\D/', '', $whatsapp);
        
        // Adicionar o código do país (55 para Brasil)
        $whatsapp = 'https://api.whatsapp.com/send?phone=55' . $whatsapptratar;  // Link para o WhatsApp sem HTML
    }

    // Busca os pedidos do cliente na tabela pedidos_shopify
    $sql = "SELECT * FROM pedidos_shopify WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $html = "<button class='btn btn-primary mt-3' onclick='showUsersShopify()'>Voltar</button>";
        $html .= "<h5 class='w-100'>Pedidos de:  $nome_cliente <em class='small'> ( $id_customer_shopify ) </em></h5> ";
        $html .= "<table class='table table-striped table-bordered'>";
        $html .= "<thead><tr><th>Nº Pedido</th><th>Data</th><th>Valor Total</th><th>Status</th><th>Ações</th></tr></thead>";
        $html .= "<tbody>";

        $total_pedidos = 0;

        while ($order = $result->fetch_assoc()) {
            // Define a cor da badge com base no status financeiro
            // $badge_class = $order['financial_status'] === 'paid' ? 'badge-success' : 'badge-warning';

            $badge_class = '';
            $comprovante = "";
            $mensagemwhats = '';
            $statuspagamento = "";
            $btwhatsapp = '';
            $pedido_number = $order['n_pedido'];
             

            // Verifica o status e define a classe de acordo
            if ($order['financial_status'] === 'paid') {
                $badge_class = 'badge-success'; // Verde para "pago"
                $statuspagamento = "Alterado para Pago pelo Operador";
                $mensagemwhats = "Olá $nome_cliente, seu pedido $pedido_number, foi alterado para pago pelo Operador, podemos conversar sobre o mesmo?";

                if (!empty($order['comprovante'])) {
                    $modalId = "modalComprovante" . $order['n_pedido']; // ID único para cada modal
                    $file_extension = strtolower(pathinfo($order['comprovante'], PATHINFO_EXTENSION));

                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        // Se for imagem, abre o modal
                        $comprovante = '
                        <button type="button" class="badge badge-primary" onclick="abrirModalComprovante(\'' . $modalId . '\', \'' . $order['comprovante'] . '\')">
                            Ver Comprovante
                        </button>
                
                        <!-- Modal Customizado -->
                        <div id="' . $modalId . '" class="custom-modal">
                            <div class="custom-modal-content">
                                <span class="close-btn" onclick="fecharModalComprovante(\'' . $modalId . '\')">&times;</span>
                                <img id="img-' . $modalId . '" src="' . $order['comprovante'] . '" alt="Comprovante" class="img-fluid">
                            </div>
                        </div>';
                    } elseif ($file_extension === 'pdf') {
                        // Se for PDF, botão para download/abertura em nova aba
                        $comprovante = '
                        <a href="' . $order['comprovante'] . '" target="_blank" class="badge badge-success">
                            Baixar Comprovante (PDF)
                        </a>';
                    }
                }



            } elseif ($order['financial_status'] === 'canceled') {
                $badge_class = 'badge-danger'; // Vermelho para "cancelado"
                $statuspagamento = "Cancelado pelo Operador";
                $mensagemwhats = "Olá $nome_cliente, seu pedido $pedido_number, foi Cancelado pelo Operador, podemos conversar sobre o mesmo?";

            } elseif ($order['financial_status'] === 'paid_shopify') {
                $badge_class = 'badge-success';
                $statuspagamento = "Pago na Shopify";
                $mensagemwhats = "Olá $nome_cliente, seu pedido $pedido_number, foi Pago direto pelo site, podemos conversar sobre o mesmo?";
            } else {
                $badge_class = 'badge-warning'; // Amarelo para outros status
                // Adiciona o botão "Enviar Comprovante"
                $statuspagamento = "Aguardando Pagamento";
                $mensagemwhats = "Olá $nome_cliente, seu pedido $pedido_number, está Aguardando o Pagamento, podemos conversar sobre o mesmo?";
                $comprovante = '<br>
                <button class="badge badge-danger" onclick="abrirModal(\'' . $nome_cliente . '\', \'' . $order['n_pedido'] . '\')">Enviar Comprovante / Mudar Status</button>';

            }

            $nota = "";
            if (isset($order['nota'])) {
                // Remove todas as tags HTML e deixa apenas o texto puro
                $nota = strip_tags($order['nota']);

                // Expressão regular para remover também o link, caso tenha sobrado
                $nota = preg_replace('/https?:\/\/\S+/', '', $nota);
            }

            $btwhatsapp = '<a target="_blank" href="'.$whatsapp.'&text='.urlencode($mensagemwhats).'"> Envie uma mensagem pelo Whatsapp</a>';$btwhatsapp = '<a target="_blank" href="'.$whatsapp.'&text='.urlencode($mensagemwhats).'" class="w-100 btn btn-success">
            <i class="fab fa-whatsapp"></i> Envie uma mensagem pelo Whatsapp
           </a>';


            $html .= "<tr>";
            $html .= "<td>{$order['n_pedido']}</td>";
            $html .= "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
            $html .= "<td>R$ " . number_format($order['total_price'], 2, ',', '.') . "</td>";
            $html .= "<td><span class='badge $badge_class'>" . ucfirst($statuspagamento) . "</span> " . $comprovante . " <br> <small class='text-muted'>nota: " . $nota . "</small> </td>";
            $html .= "<td>";
            $html .= $btwhatsapp;
            if ($order['financial_status'] != 'canceled') {
               $html .= "<button href='#' onclick='printEtiquetaShopify(this)' class='w-100 btn btn-sm btn-warning' data-client-id='{$order["customer_id"]}'><i class='fas fa-print'></i> Etiqueta</button> ";
            }
            $html .= "<button id='detalhes-pedido-{$order['order_id']}' class='w-100 btn btn-sm btn-primary ver-detalhes' data-pedido='{$order['order_id']}'><i class='fas fa-eye'></i> Detalhes do Pedido</button> ";

            // Verifica se o pedido já foi integrado

            if ($order['financial_status'] == 'canceled') {

            } else {

                $integrado_info = get_pedido_integrado_info($order['n_pedido']);
                if ($integrado_info) {
                    $html .= "<button id='integrar-pedido-{$order['order_id']}' class='w-100 btn btn-sm btn-success' data-pedido='{$order['order_id']}'>
        {$integrado_info['data_hora']} - Integrado por {$integrado_info['operador']}
    </button>";
                } else {
                    $nomewoo = NOME_WOO;
                    $html .= "<button id='integrar-pedido-{$order['order_id']}' class='w-100 btn btn-sm btn-secondary integrar-compre' data-pedido='{$order['order_id']}'>Integrar $nomewoo</button>";
                }
            }

            

            $html .= "</td>";
            $html .= "</tr>";

            $total_pedidos += $order['total_price'];
        }

        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "<div class='w-100 d-block p-2 bg-dark text-white'>Total dos Pedidos: R$ <b> " . number_format($total_pedidos, 2, ',', '.') . "</b></div>";
    } else {
        $html = "
        <div class='alert alert-info'>Nenhum pedido encontrado para este cliente.</div>
        <a href='#' onclick='showUsersShopify()' class='btn btn-primary'>Voltar</a>
        ";
    }

    return $html;
}

// Verifica se o ID do cliente foi enviado
if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
    echo list_orders_by_customer($customer_id);
} else {
    echo "<div class='alert alert-warning'>Nenhum cliente selecionado.</div>";
}
?>