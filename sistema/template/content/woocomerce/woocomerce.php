<div id="compreadlux" class="settings" style="display: none;">

  <h3 class="text-center"><?= NOME_WOO ?> </h3>

  <div class="container my-3">


    <ul class="list-group painelcompre">

      <?php
      if (CENTRAL_SUPERUSER == "superuser") {
        if ($_SESSION["permissao"] == "admin") { ?>
          <li class="list-group-item d-flex align-items-center">
            <i class="bi bi-list-ul mr-2"></i>
            <a class="btn btn-success w-100" href="#" id="mostrarProdutos"> Importar / Atualizar Produtos da Fábrica Para
              <?= NOME_WOO ?> <em>(woocomerce)</em>
            </a>
          </li>
        <?php }
      } ?>

      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-list-ul mr-2"></i>
        <a class="btn btn-primary w-100" href="#" id="listarProdutos"> Listar Produtos
        </a>
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-list-ul mr-2"></i>
        Listar clientes
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-box-seam mr-2"></i>
        Listar pedidos
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-tag mr-2"></i>
        Ver tabela de preços
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-graph-up mr-2"></i>
        Total de compras por cliente / estado / dia - mês -ano
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-bar-chart-line mr-2"></i>
        Ranking de produtos mais comprados
      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-percent mr-2"></i>
        Lista de promoções
      </li>
    </ul>

  </div>






  <div id="listar_produtos_compre"></div>

</div>