<div class="header  d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5><i class="fas fa-award fa-2x"></i> <?php echo $_SESSION['nome']; ?></h5>
      <p class="mb-0"> <em>
          <?php if ($_SESSION["permissao"] == 'admin') {
            echo 'Administrador da Central <b>' . CENTRAL_ID . '</b> <em> ' . CENTRAL_NOME . ' (' . CENTRAL_SUPERUSER . ') </em>';
          } else {
            echo $_SESSION["cargo"];
          } ?>
        </em></p>
    </div>
    <a href="sair.php" class="btn btn-exit">Sair</a>
  </div>

