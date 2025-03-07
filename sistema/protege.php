<?php
// proteje.php
@session_set_cookie_params(60);
@session_start();

if (!isset($_SESSION['usuario_id'])) {
   //Quero pegar o endereço do site pelo navegador
   $url = $_SERVER['HTTP_REFERER'];
   $caminho_login =  "$url/login.php";

  // Redireciona para a página de login
  header("Location: " . $caminho_login);
  exit();
}

// Resto do código da página
?>