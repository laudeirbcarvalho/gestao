<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

// Definindo o número de resultados por página
$results_per_page = 5;
$central_id = CENTRAL_ID;
// Verifica se a página atual foi definida, caso contrário, define como 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;
 
// Verifica se há uma busca
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$campo_busca = isset($_GET['campo_busca']) ? $_GET['campo_busca'] : 'nome'; // Valor padrão: busca por nome

// Defina a condição WHERE com base no valor do campo_busca
$where = "LOWER(" . $campo_busca . ") LIKE LOWER(?)"; // Busca na coluna selecionada
$search_param = '%' . strtolower($search) . '%'; // Converte o parâmetro de busca para minúsculas

// Prepara a consulta para contar o total de clientes
$sql_count = "SELECT COUNT(*) AS total FROM clientes_shopify WHERE $where AND central_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("si", $search_param, $central_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_results = $row_count['total'];
$total_pages = ceil($total_results / $results_per_page);

// Prepara a consulta para obter os clientes
$sql = "SELECT c.id_shopify, 
            c.nome, 
            c.email, 
            c.status, 
            c.data_ativacao, 
            c.data_cadastro, 
            c.endereco,
            c.numero, 
            c.bairro,
            c.complemento,
            c.cidade, 
            c.estado, 
            c.pais, 
            c.cep,
            c.telefone, 
            c.data_nascimento,
            c.cpf,
            c.cnpj,
            c.indicado,
            c.central_id,
            c.empresa,
            COUNT(p.id) AS qtd_pedidos, 
            MAX(p.created_at) AS ultimo_pedido, 
            COALESCE(SUM(CASE WHEN p.financial_status = 'paid' THEN p.total_price ELSE 0 END), 0) AS total_pago
        FROM clientes_shopify c
        LEFT JOIN pedidos_shopify p ON c.id_shopify = p.customer_id
        WHERE $where
        AND c.central_id = ?
        GROUP BY c.id_shopify
        ORDER BY c.data_cadastro DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $search_param, $central_id, $start_from, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();


//Conta os usuarios ativos e inativos
$sql_countu = "SELECT status, COUNT(*) AS total FROM clientes_shopify WHERE $where AND central_id = ? GROUP BY status";
$stmt_countu = $conn->prepare($sql_countu);
$stmt_countu->bind_param("si", $search_param, $central_id);
$stmt_countu->execute();
$resultu = $stmt_countu->get_result();

$counts = [
    'ativo'   => 0,
    'inativo' => 0,
];

while ($row = $resultu->fetch_assoc()) {
    // Atribui a contagem de acordo com o status retornado
    if ($row['status'] === 'ativo') {
        $counts['ativo'] = $row['total'];
    } elseif ($row['status'] === 'inativo') {
        $counts['inativo'] = $row['total'];
    }
}




// Obter todas as centrais
$centraisQuery = "SELECT central_id, central_nome FROM central"; // Ajuste para o nome correto da tabela de centrais
$centraisResult = $conn->query($centraisQuery);
$centrais = [];
if ($centraisResult->num_rows > 0) {
    while ($central = $centraisResult->fetch_assoc()) {
        $centrais[] = $central;
    }
}

//Altera os dados do cliente ShopiFy
$nomeshopify = NOME_SHOPIFY;
echo ' 
<div class="container">
    <div class="row">
        <div class="col-12">
            <div id="formalterar">
                <h3>Alterar Dados do Cliente ' . $nomeshopify . '</h3>
                <form id="form-alterar-cliente" action="#" method="post">
                    <div class="form-group">
                        <input type="hidden" class="form-control" id="id_shopify" name="id_shopify" readonly>
                    </div>
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" class="form-control" id="nomecliente" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="emailcliente" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" required>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_ativo" value="ativo">
                            <label class="form-check-label" for="status_ativo">
                                Ativo
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="status_inativo" value="inativo">
                            <label class="form-check-label" for="status_inativo">
                                Inativo
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="hidden" class="form-control" id="data_cadastro" name="data_cadastro" readonly>
                    </div>
                    <div class="form-group">
                        <label for="endereco">Endereço:</label>
                        <input type="text" class="form-control" id="endereco" name="endereco">
                    </div>
                    <div class="form-group">
                        <label for="numero">Número:</label>
                        <input type="text" class="form-control" id="numero" name="numero">
                    </div>
                    <div class="form-group">
                        <label for="complemento">Complemento:</label>
                        <input type="text" class="form-control" id="complemento" name="complemento">
                    </div>
                    <div class="form-group">
                        <label for="bairro">Bairro:</label>
                        <input type="text" class="form-control" id="bairro" name="bairro">
                    </div>
                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" class="form-control" id="cidade" name="cidade">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <input type="text" class="form-control" id="estado" name="estado">
                    </div>
                    <div class="form-group">
                        <label for="cep">Cep:</label>
                        <input type="text" class="form-control" id="cep" name="cep">
                    </div>
                    <div class="form-group">
                        <label for="pais">País:</label>
                        <input type="text" class="form-control" id="pais" name="pais">
                    </div>
                     
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento:</label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF:</label>
                        <input type="text" class="form-control" id="cpf" name="cpf">
                    </div>
                    <div class="form-group">
                        <label for="cnpj">CNPJ:</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj">
                    </div>
                    <div class="form-group">
                        <label for="empresa">EMPRESA:</label>
                        <input type="text" class="form-control" id="empresa" name="empresa">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <button id="cancelar" class="btn btn-secondary">Cancelar</button>
                </form>
                <hr>
            </div>
        </div>
    </div>

    <!-- Formulário de busca -->
    <div class="row">
        <div class="col-12">
            <form method="GET" onsubmit="event.preventDefault(); showUsersShopify(1, this.search.value);">
    <input type="radio" name="busca" value="nome" checked onclick="atualizarPlaceholder(this); document.getElementById("campo_busca").value = this.value;"> Nome 
    <input type="radio" name="busca" value="status" onclick="atualizarPlaceholder(this); document.getElementById("campo_busca").value = this.value;"> Status 
    <input type="radio" name="busca" value="cidade" onclick="atualizarPlaceholder(this); document.getElementById("campo_busca").value = this.value;"> Cidade 
    <br> 
    <div class="input-group"> 
        <input type="text" name="search" class="form-control" placeholder="Buscar por nome" value="">
        <input type="hidden" name="campo_busca" id="campo_busca" value="nome">
        <div class="input-group-append">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </div><br>
</form>

<i class="fa fa-user text-success"></i> Ativo ('. $counts['ativo'] .') <i class="fa fa-user text-danger"></i> Inativo ('. $counts['inativo'] .')
<br><br>

        </div>
    </div>
</div>

<script>
 
 $(document).ready(function () {
 
  // Evento de clique no botão
  $(".alterarDadosUsuarioShopify").click(function () {
  
  const customer_id = $(this).data("id"); 
 

  

    $("#formalterar").show();
    $("#form-alterar-cliente").show();
    $("#mostrarpedidospendentes").hide();

    // Faz a chamada AJAX
    $.ajax({
      url: "sistema/shopify/alterar_clientes_shopify.php",
      type: "POST",
      data: { customer_id: customer_id },
      dataType: "json",
      success: function (data) {
        // Preenche os campos do formulário com os dados do cliente
        $("#id_shopify").val(data.id_shopify);
        $("#nomecliente").val(data.nome);
        $("#emailcliente").val(data.email);        
        $(`input[name="status"][value="${data.status}"]`).prop("checked", true);
        $("#data_cadastro").val(data.data_cadastro);
        $("#endereco").val(data.endereco);
        $("#numero").val(data.numero);
        $("#complemento").val(data.complemento);
        $("#bairro").val(data.bairro);
        $("#cep").val(data.cep);
        $("#cidade").val(data.cidade);
        $("#estado").val(data.estado);
        $("#pais").val(data.pais);
        $("#telefone").val(data.telefone);
        $("#data_nascimento").val(data.data_nascimento);
        $("#cpf").val(data.cpf);
        $("#cnpj").val(data.cnpj);
        $("#empresa").val(data.empresa);
      },
      error: function () {
        alert("Erro ao carregar os dados do cliente no sistema.");
      },
    });


     $("#form-alterar-cliente")[0].scrollIntoView({
      behavior: "smooth",
    });

    // Enviar formulário para alterar dados do cliente
    $("#form-alterar-cliente").submit(function(event) {
      event.preventDefault(); // Impede envio padrão do formulário
      

          
                 var formData = {
                    id_shopify: $("#id_shopify").val(),
                    nome_cliente: $("#nomecliente").val(),
                    email_cliente: $("#emailcliente").val(),
                    telefone: $("#telefone").val(),
                    status: $("input[name=status]:checked").val(),
                    data_cadastro: $("#data_cadastro").val(),
                    endereco: $("#endereco").val(),
                    numero: $("#numero").val(),
                    complemento: $("#complemento").val(),
                    bairro: $("#bairro").val(),
                    cep: $("#cep").val(),
                    cidade: $("#cidade").val(),
                    estado: $("#estado").val(),
                    pais: $("#pais").val(),
                    telefone: $("#telefone").val(),
                    data_nascimento: $("#data_nascimento").val(),
                    cpf: $("#cpf").val(),
                    cnpj: $("#cnpj").val(),
                    empresa: $("#empresa").val(),
                };


                // Enviar os dados para o PHP via AJAX
                $.ajax({
                    url: "sistema/shopify/alterar_clientes_shopify.php", // O arquivo PHP que vai processar os dados
                    type: "POST", // Método de envio
                    data: formData, // Dados a serem enviados
                    dataType: "json", // Esperamos uma resposta JSON
                    success: function(response) {
                    // Resposta do PHP
                    if (response.success) {
                        alert("Dados do cliente atualizados com sucesso!");
                        
                        // Fechar o formulário
                        $("#formalterar").hide();
                        $("#form-alterar-cliente").hide();

                        // Atualiza a lista de clientes (caso haja uma função showUsersShopify)
                        showUsersShopify(1, ""); 
                    } else {
                        alert("Erro ao atualizar os dados do cliente: " + response.error);
                    }
                    },
                    error: function() {
                    alert("Erro ao enviar os dados do cliente.");
                    }
                });
 
     });

  });

  // Evento para cancelar a edição
  $("#cancelar").click(function (event) {
    event.preventDefault(); // Impede o envio do formulário
    

    $("#formalterar").hide();
    $("#form-alterar-cliente").hide();


     showUsersShopify(1, "");

    $("#form-alterar-cliente")[0].scrollIntoView({
      behavior: "smooth",
    });
  });

  $("#form-alterar-cliente")[0].scrollIntoView({
    behavior: "smooth",
  });

 


});



 

</script>
 
';




// Exibe os resultados
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {


        if ($row['status'] === 'ativo') {
            $iconeuser =  '<i class="fa fa-user text-success"></i>'; // ícone verde para ativo
        } else {
            $iconeuser =  '<i class="fa fa-user text-danger"></i>';  // ícone vermelho para inativo
        }
        


        $data_ativacao_formatada = !empty($row['data_ativacao']) ? date("d/m/Y H:i", strtotime($row['data_ativacao'])) : 'Data não disponível';
        $data_cadastro_formatada = !empty($row['data_cadastro']) ? date("d/m/Y H:i", strtotime($row['data_cadastro'])) : 'Data não disponível';
        $data_nascimento_formatada = !empty($row['data_nascimento']) ? date("d/m/Y", strtotime($row['data_nascimento'])) : 'Data não informada';


        $nomeFormatado = isset($row['nome']) && trim($row['nome']) !== ''
            ? ucwords(strtolower($row['nome']))
            : 'NEWSLETTER';

        $qtd_pedidos = $row['qtd_pedidos'];
        $dataUltimoPedido = !empty($row['ultimo_pedido']) ? date("d/m/Y H:i", strtotime($row['ultimo_pedido'])) : 'Sem pedidos';



        // Cálculo dos pontos
        $totalPago = $row['total_pago'];
        $pontosAcumulados = floor($totalPago); // 1 ponto por real
        $pontosFaltando = max(PONTUACAO - $pontosAcumulados, 0);


        $endpedido = ($qtd_pedidos > 0)
            ? '<br> <small>  <em style="color: black;">Último ' . $dataUltimoPedido . '</em> <br>   </small> '
            : ' ';

        if ($row['status'] != "") {
            echo '<div class="card w-100 mb-3">';
            echo '    <div  data-toggle="collapse" href="#collapse-' . htmlspecialchars($row['id_shopify']) . '" class="card-header">';
            echo '<a class="collapsed card-link">
                    <div class="row align-items-center">
                        <!-- Coluna 1: Nome -->
                        <div class="col-md-3 col-sm-6 mb-2">
                           '. $iconeuser .' <b style=color:black>' . $nomeFormatado . '</b> </br>
                           
 ' . htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') . ' <br>
                            <div class="w-10 small">   (' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . ') </div>
                            
                        </div>

                        <!-- Coluna 2: Quantidade de Pedidos -->
                        <div class="col-md-2 col-sm-6 mb-2">
                            (<b>' . $qtd_pedidos . '</b> <em>pedidos</em>) ' . $endpedido . '
                        </div>

                        <!-- Coluna 3: Pontos Acumulados -->
                        <div class="col-md-3 col-sm-6 mb-2">
                            Pontos Acumulados: <b>' . $pontosAcumulados . '</b>
                        </div>

                        <!-- Coluna 4: Pontos Faltando -->
                        <div class="col-md-4 col-sm-6 mb-2">
                            Pontos Faltando para o Prêmio: <b>' . $pontosFaltando . '</b>
                        </div>

                        <!-- Coluna 5: Data Cadastro -->
                        <div class="col-md-4 col-sm-6 mb-2">
                            Cadastro: <b>' . $data_cadastro_formatada . '</b> <br> 
                            Cidade: <b>' . htmlspecialchars($row['cidade']) . ' - ' . htmlspecialchars($row['estado']) . ' </b>
                        </div>

                    </div>
                </a>';

            echo '    </div>';
            echo '    <div id="collapse-' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . '" class="collapse">';
            echo '        <div class="card-body">';
            echo '            <p class="card-text"><strong>ID Shopify:</strong> ' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Email:</strong> ' . htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Tel:</strong> ' . htmlspecialchars($row['telefone'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Endereço:</strong> ' . htmlspecialchars($row['endereco'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($row['numero'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($row['complemento'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($row['bairro'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($row['cidade']) . ' - ' . htmlspecialchars($row['estado'] ?? '', ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($row['pais'] ?? '', ENT_QUOTES, 'UTF-8') . ', Cep: ' . htmlspecialchars($row['cep'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Documento:</strong> CPF: ' . htmlspecialchars($row['cpf'] ?? '', ENT_QUOTES, 'UTF-8') . ' CNPJ: ' . htmlspecialchars($row['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Empresa:</strong> ' . htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Aniversário:</strong> ' . htmlspecialchars($data_nascimento_formatada ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            echo '            <p class="card-text"><strong>Lead:</strong> ' . htmlspecialchars($row['indicado'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';

            if ($row['status'] === 'inativo') {
                $linksenhashopify = SHOPIFY_URL_LOJA . "/account/login#recover";
                echo '            <p class="card-text"><strong>Status:</strong> <b style="color:red;"> ' . htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') . ' | Link para cadastrar a senha do cliente <a target="blank_" href="' . $linksenhashopify . '"> Cadastrar a Senha do Cliente </a> </b> <em style="color:silver; font-seze=10px;"> ( se o cliente não viu o seu e-mail <b> ' . htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') . '</b>, você deve acesar este link e REPOR A PALAVRA-PASSE e entrar em contato com o cliente para ele acessar seu e-mail e cadastrar sua nova senha. ) </em> </p>';
                echo '            <p class="card-text"><strong>Data de Cadastro:</strong> ' . htmlspecialchars($data_cadastro_formatada ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            } else {
                echo '            <p class="card-text"><strong>Status:</strong> <b style="color:green;"> ' . htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') . '</b></p>';
                if (!empty($row['data_ativacao'])) {
                    echo '            <p class="card-text"><strong>Data de Cadastro:</strong> ' . htmlspecialchars($data_cadastro_formatada ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '            <p class="card-text"><strong>Data de Ativação:</strong> ' . htmlspecialchars($data_ativacao_formatada ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
                }
            }


            echo '<p class="card-text"><strong>Central:</strong>';
            echo '<select class="form-control central-select" data-cliente-id="' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . '">';
            foreach ($centrais as $central) {
                $selected = ($central['central_id'] == $row['central_id']) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($central['central_id'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($central['central_nome'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
            echo '</select>';
            echo '<br><br><br>';
            echo '</p>';




            if (!empty($row['telefone'])) {
                $telefone = preg_replace('/\D/', '', $row['telefone']); // Remove caracteres não numéricos
                $mensagem = urlencode("Olá " . $nomeFormatado . ", tudo bem? Gostaríamos de falar com você sobre o " . NOME_SHOPIFY . ".");
                echo '<a href="https://api.whatsapp.com/send?phone=55' . $telefone . '&text=' . $mensagem . '" target="_blank" class="btn btn-success w-100 mb-3">';
                echo '<i class="fab fa-whatsapp"></i> Enviar WhatsApp';
                echo '</a>';
            }




            // Adiciona o botão para ativar o cliente
            if ($row['status'] === 'inativo') {
                echo '            <button class="btn btn-success w-100 mb-3" onclick="ativarCliente(' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . ')">Ativar</button>';
            }

            echo '            <button class="btn btn-info w-100 mb-3" onclick="pedidosCliente(' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . ')">Ver Pedidos</button>';
            echo '            <button class="btn btn-warning w-100 mb-3 alterarDadosUsuarioShopify"  data-id="' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . '">Alterar Dados</button>';
            if ($_SESSION['permissao'] == "admin") {
                echo '        <button type="button" class="btn btn-danger w-100 mb-3" onclick="excluirCliente(' . htmlspecialchars($row['id_shopify'] ?? '', ENT_QUOTES, 'UTF-8') . ')">Excluir Cliente</button>';
            }
            echo '        </div>';
            echo '    </div>';
            echo '</div>';
        }
    }
} else {
    echo '<div class="alert alert-warning" role="alert">Nenhum cliente encontrado.</div>';
}

// Paginação
echo '<nav aria-label="Page navigation">';
echo '    <ul class="pagination justify-content-center">';

// Definir quantas páginas exibir antes e depois da página atual
$range = 2; // Exibir 2 páginas antes e 2 depois da página atual

// Exibir o botão "Primeira"
if ($page > 1) {
    echo '        <li class="page-item"><a class="page-link" href="#" onclick="showUsersShopify(1, \'' . $search . '\')">Primeira</a></li>';
}

// Exibir o botão "Anterior"
if ($page > 1) {
    echo '        <li class="page-item"><a class="page-link" href="#" onclick="showUsersShopify(' . ($page - 1) . ', \'' . $search . '\')">Anterior</a></li>';
}

// Exibir páginas ao redor da página atual
for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
    echo '        <li class="page-item' . ($i == $page ? ' active' : '') . '">';
    echo '            <a class="page-link" href="#" onclick="showUsersShopify(' . $i . ', \'' . $search . '\')"> ' . $i . '</a>';
    echo '        </li>';
}

// Exibir o botão "Próxima"
if ($page < $total_pages) {
    echo '        <li class="page-item"><a class="page-link" href="#" onclick="showUsersShopify(' . ($page + 1) . ', \'' . $search . '\')">Próxima</a></li>';
}

// Exibir o botão "Última"
if ($page < $total_pages) {
    echo '        <li class="page-item"><a class="page-link" href="#" onclick="showUsersShopify(' . $total_pages . ', \'' . $search . '\')">Última</a></li>';
}

echo '    </ul>';
echo '</nav>';

echo '<script src="js/navegacao.js"></script>';
echo '<script src="js/shopify.js"></script>';


// Fecha a conexão
$conn->close();
