<div id="user-list-shopify" class="user-list">
    <h3 class="text-center" id="sumir"><?= NOME_SHOPIFY ?></h3>
    <div class="d-flex flex-column align-items-center w-100">
        <!-- Botão para alternar a exibição dos botões de importação -->
        <button class="btn btn-secondary w-100 mb-2 fw-bold d-flex align-items-center justify-content-center text-center" id="toggle-import-buttons">
            Importar <?= NOME_SHOPIFY ?>
        </button>

        <!-- Botão "Cadastrar Salão Franqueado" -->
        <button class="btn btn-success w-100 mb-2 fw-bold d-flex align-items-center justify-content-center text-center" id="cadastrarsalao">
            Cadastrar <?= NOME_SHOPIFY ?>
        </button>

        <!-- Botão para listar pedidos pendentes -->
        <button class="btn btn-warning w-100 mb-2 fw-bold d-flex align-items-center justify-content-center text-center" id="listarpedidospendentes">
            Listar Pedidos <b> Pendentes </b> <?= NOME_SHOPIFY ?> | Total ( <?= $total_pedidos_pendentes ?> )
        </button>

        <!-- Botão para listar pedidos pagos na shopify -->
        <button class="btn btn-success w-100 mb-2 fw-bold d-flex align-items-center justify-content-center text-center" id="listarpedidospagos">
            Listar Pedidos <b> Pagos na Shopify </b> <?= NOME_SHOPIFY ?> | Total ( <?= $total_pedidos_pagos_shopify ?> )
        </button>

        <!-- Div para exibir os pedidos -->
        <div id="mostrarpedidospendentes" class="mt-3"></div>
        <div id="mostrarpedidospagos" class="mt-3"></div>

        <!-- Container dos botões de importação, inicialmente oculto -->
        <div id="import-buttons-container" class="d-none mt-2">
            <button class="btn btn-primary w-100 mb-2" id="import-pedidos">
                IMPORTAR PEDIDOS <?= NOME_SHOPIFY ?> <br> (Última execução automática: <?php echo htmlspecialchars($data_ultimos_pedidos); ?>)
            </button>

            <button class="sumir btn btn-secondary w-100 mb-2" id="import-clientes">
                IMPORTAR CLIENTES <?= NOME_SHOPIFY ?> <br> (Última execução automática: <?php echo htmlspecialchars($data_ultimos_clientes); ?>)
            </button>
        </div>
    </div>

    <br><br>

    <div id="content" class="row justify-content-center align-items-center h-100">
        <!-- O conteúdo da lista de clientes será carregado aqui -->
    </div>

    <ul id="clientes">
        <!-- A lista de clientes será preenchida aqui -->
        <?php // include 'listar_clientes_shopify.php'; ?>
    </ul>
</div>
