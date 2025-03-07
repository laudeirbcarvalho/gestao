<div id="inicio" class="mb-4">
  <div class="salaofranqueado">
    <h3 class="text-center text-primary mb-4"><?= NOME_SHOPIFY ?></h3>
    <div class="row">
      <!-- Pedidos Shopify -->
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header text-center bg-primary text-white">
            <h5 class="mb-0">Pedidos <?= NOME_SHOPIFY ?></h5>
          </div>
          <div class="card-body">
            <p class="text-center text-muted mb-3">
              <i class="far fa-clock"></i> Atualizado em <b><?= $data_ultimos_pedidos; ?></b>
            </p>
            <div class="row text-center">
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Ano</b></p>
                  <p class="mb-1"><i class="fas fa-hand-holding-usd"></i>
                    <b><?= $total_pedidos_ano; ?></b>
                  </p>
                  <p class="mb-0">
                    <em><?= number_format($total_valor_pedidos_ano, 2, ',', '.'); ?></em>
                    <hr>
                    <button onclick="abrirPopupPedidosPagosAno()"><i class="fas fa-print"></i></button>
                  </p>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Mês</b></p>
                  <p class="mb-1"><i class="fas fa-hand-holding-usd"></i>
                    <b><?= $total_pedidos_mes; ?></b>
                  </p>
                  <p class="mb-0">
                    <em><?= number_format($total_valor_pedidos_mes, 2, ',', '.'); ?></em>
                    <hr>
                    <button onclick="abrirPopupPedidosPagosMes()"><i class="fas fa-print"></i></button>
                  </p>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Dia</b></p>
                  <p class="mb-1"><i class="fas fa-hand-holding-usd"></i>
                    <b><?= $total_pedidos_dia; ?></b>
                  </p>
                  <p class="mb-0">
                    <em><?= number_format($total_valor_pedidos_dia, 2, ',', '.'); ?></em>
                    <hr>
                    <button onclick="abrirPopupPedidosPagosDia()"><i class="fas fa-print"></i></button>
                  </p>
                </div>
              </div>
            </div>
            <hr>

            <?php if (CENTRAL_SUPERUSER == "superuser") { ?>

              <p class="text-center">
                <i class="fas fa-cogs"></i> Integrados no <?= NOME_WOO ?>:
                <b><?= $total_pedidos_integrados; ?></b>
              </p>
              <p class="text-center small">
                <em>
                  Última Integração: <?= $numero_ultimo_pedido; ?> por <?= htmlspecialchars($operador_ultimo_pedido); ?>
                  em
                  <?= (!empty($ultima_data_pedido) ? (new DateTime($ultima_data_pedido))->format('d/m/Y H:i:s') : 'Data indisponível'); ?><br>
                  Cliente: <?= htmlspecialchars($nome_cliente); ?>
                </em>
              </p>
            <?php } ?>
            <p>
            <button class="m-1 w-100 btn btn-primary" onclick="abrirPopup()"><i class="fas fa-print"></i> Relatório de
            Pedidos  </button>
              <button class="m-1 w-100 btn btn-primary" onclick="abrirPopupd()"><i class="fas fa-print"></i> Relatório de
                Pedidos Detalhado </button>
                
              <button class="m-1 w-100 btn btn-primary" onclick="abrirPopupProdutos()"> <i class="fas fa-print"></i>
                Relatório de Produtos Mais Vendidos</button>
            <div id="popup" class="modal">
              <div class="modal-content">
                <span class="close" onclick="fecharPopup()">&times;</span>
                <div id="relatorioContent">
                </div>
              </div>
            </div>


            </p>


          </div>
        </div>
      </div>

      <!-- Clientes Shopify -->
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header text-center bg-success text-white">
            <h5 class="mb-0">Leads <?= NOME_SHOPIFY ?></h5>
          </div>
          <div class="card-body">
            <p class="sumir text-center text-muted mb-3">
              <i class="far fa-clock"></i> Última Atualização: <?= $data_ultimos_clientes; ?>
            </p>
            <div class="row text-center">
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Total Ativo</b></p>

                  <p class="mb-0"><i class="fas fa-user-check"></i> <b><?= $total_clientes_ano; ?>
                    </b> <br> <em class="small text-muted">em <?= date('Y') ?></em> </p>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Ativos Mês</b></p>
                  <p class="mb-0"><i style="color:green" class="fas fa-user-check"></i> <b><?= $total_clientes_mes; ?>
                    </b> <br> <em class="small text-muted">em <?= date('Y') ?></em></p>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <p class="mb-0 text-secondary"><b>Dia</b></p>

                  <p class="mb-0"><i class="fas fa-user-check"></i> <b><?= $total_clientes_dia; ?>
                    </b> <br> <em class="small text-muted">em <?= date('Y') ?></em></p>
                </div>
              </div>
              <div class="col-12">
                <em class="small text-muted"> Newslletter </em> <?= $total_clientes_newsletter ?>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">

                  <p class="mb-0 text-secondary"><b>Lead Em Análise (Ano)</b>
                  <p class="mb-0"><i style="color:silver" class="fas fa-user-check"></i>
                    <b><?= $total_clientes_analise; ?>
                    </b>
                  </p>

                </div>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">

                  <p class="mb-0 text-secondary"><b>Lead Em Análise (Mês)</b>
                  <p class="mb-0"><i style="color:silver" class="fas fa-user-check"></i>
                    <b><?= $total_clientes_analise_mes; ?>
                    </b>
                  </p>

                </div>
              </div>
            </div>
            <hr>
            <em class="small">
              <?= $ultimo_cliente; ?></em>
          </div>
        </div>
      </div>

      <!-- Cidades com salão -->

      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header text-center bg-warning text-white">
            <h5 class="mb-0">Leads interessados <?= NOME_SHOPIFY ?></h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="cidadesTable" class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Total</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- #region Graficos shopify-->

  
    <div class="row mb-4">
      <div class="col-md-6 p-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center">Top 5 Cliente que Mais Compram</h5>
            <canvas id="graficoPedidos"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 p-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center">Top 5 Produtos Mais Vendidos</h5>
            <canvas id="graficoProdutos"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 p-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center">Produtos Mais Vendidos Por Cidade <button id="btnImprimir" class="btn btn-primary">Imprimir Lista</button>
            </h5>
            <div id="map1" style="width: 100%; height: 500px;"></div>
          </div>
        </div>
      </div>
      <div class="col-md-6 p-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center"><?= NOME_SHOPIFY ?> Por Cidade No Brasil</h5>
            <div id="map2" style="width: 100%; height: 500px;"></div>
          </div>
        </div>
      </div>
    </div>
   




</div>



<!-- RELATÓRIOS PARA IMPRIMIR -->

<!-- Janela modal para exibir o relatório -->
<div id="relatorioModal" style="display: none;">
  <div id="relatorioContent">
    <h2>Relatório de Pedidos</h2>
    <p>Total de Pedidos: <?php echo $total_pedidos; ?></p>
    <p>Total de Pedidos do Ano Passado: <?php echo $total_pedidos_ano_anterior ?? 0; ?></p>
    <p>Total de Pedidos deste Ano: <?php echo $total_pedidos_ano; ?></p>
    <p>Total de Pedidos deste Mês: <?php echo $total_pedidos_mes; ?></p>
    <p>Total de Pedidos do Dia: <?php echo $total_pedidos_dia; ?></p>
    <p>Total de Pedidos na Semana:</p><br>

  </div>
</div>



<script>
  function abrirPopup() {
    document.getElementById("popup").style.display = "block";

    var conteudo = `
        <h2>Relatório de Pedidos</h2>
        <iframe src="sistema/dashboard/relatorio.php" width="100%" height="500px"></iframe> 
    `;
    document.getElementById("relatorioContent").innerHTML = conteudo;
  }

  function abrirPopupd() {
    document.getElementById("popup").style.display = "block";

    var conteudo = `
        <h2>Relatório de Pedidos Detalhado</h2>
        <iframe src="sistema/dashboard/relatoriod.php" width="100%" height="500px"></iframe> 
    `;
    document.getElementById("relatorioContent").innerHTML = conteudo;
  }

  function abrirPopupProdutos() {
    document.getElementById("popup").style.display = "block";

    var conteudo = `
        <h2>Relatório de Produtos Mais Vendidos</h2>
        <iframe src="sistema/dashboard/relatorio_produtos.php" width="100%" height="500px"></iframe> 
    `;
    document.getElementById("relatorioContent").innerHTML = conteudo;
  }


  function fecharPopup() {
    document.getElementById("popup").style.display = "none";
  }


  function abrirPopupPedidosPagosAno() {
    // Abre uma nova janela popup
    var popup = window.open('', 'Relatório de Pedidos', 'width=800,height=600');

    // Carrega o conteúdo do arquivo print_pedidos_ano.php
    fetch('sistema/dashboard/print_pedidos_pagos_shopify_ano.php')
      .then(response => response.text())
      .then(conteudo => {
        // Escreve o conteúdo no popup
        popup.document.write('<html><head><title>Relatório de Pedidos Pagos no Ano</title></head><body>');
        popup.document.write(conteudo);
        popup.document.write('</body></html>');
        popup.document.close();

        // Imprime o popup
        popup.print();
      });
  }

  function abrirPopupPedidosPagosMes() {
    // Abre uma nova janela popup
    var popup = window.open('', 'Relatório de Pedidos', 'width=800,height=600');

    // Carrega o conteúdo do arquivo print_pedidos_ano.php
    fetch('sistema/dashboard/print_pedidos_pagos_shopify_mes.php')
      .then(response => response.text())
      .then(conteudo => {
        // Escreve o conteúdo no popup
        popup.document.write('<html><head><title>Relatório de Pedidos Pagos no Mês</title></head><body>');
        popup.document.write(conteudo);
        popup.document.write('</body></html>');
        popup.document.close();

        // Imprime o popup
        popup.print();
      });
  }

  function abrirPopupPedidosPagosDia() {
    // Abre uma nova janela popup
    var popup = window.open('', 'Relatório de Pedidos', 'width=800,height=600');

    // Carrega o conteúdo do arquivo print_pedidos_ano.php
    fetch('sistema/dashboard/print_pedidos_pagos_shopify_dia.php')
      .then(response => response.text())
      .then(conteudo => {
        // Escreve o conteúdo no popup
        popup.document.write('<html><head><title>Relatório de Pedidos Pagos no Mês</title></head><body>');
        popup.document.write(conteudo);
        popup.document.write('</body></html>');
        popup.document.close();

        // Imprime o popup
        popup.print();
      });
  }
</script>