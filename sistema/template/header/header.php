<?php require_once('sistema/db.php');?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?=NOME_SISTEMA?> | <?=NOME_EMPRESA?></title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="manifest" href="manifest.json">
  <link rel="prefetch" href="manifest.json">
  <link rel="icon" href="favicon.png" type="image/x-icon">

  <!-- Meta tags para SEO -->
  <meta name="description"
    content="<?=DESCRICAO?>">
  <meta name="keywords"
    content="<?=PALAVRAS_CHAVE?>">
  <meta name="author" content="laudeirbcarvalho@gmail.com">

  <!-- Meta tags para redes sociais (Open Graph) -->
  <meta property="og:title" content="<?=NOME_SISTEMA?> | <?=NOME_EMPRESA?>">
  <meta property="og:description"
    content="<?=DESCRICAO?>">
  <meta property="og:image" content="<?=URL?>/icone.png">
  <meta property="og:url" content="<?=URL?>">
  <meta property="og:type" content="website">

  <!-- Meta tags para Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?=NOME_SISTEMA?> | <?=NOME_EMPRESA?>">
  <meta name="twitter:description"
    content="<?=DESCRICAO?>">
  <meta name="twitter:image" content="<?=URL?>/icone.png">

  <!-- Meta tags para WhatsApp -->
  <meta property="og:title" content="<?=NOME_SISTEMA?> | <?=NOME_EMPRESA?>">
  <meta property="og:description"
    content="<?=DESCRICAO?>">
  <meta property="og:image" content="<?=URL?>/icone.png">
  <meta property="og:url" content="<?=URL?>">
  <meta property="og:type" content="website">

  <style>
    body {
      background: linear-gradient(to top, #000000, #0000ff);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }



    h1 {
      margin-bottom: 1.5rem;
    }

    .header {
      background-color: white;
      padding: 10px;
      border-bottom: 1px solid #ddd;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header a {
      margin-left: 10px;
      color: #007bff;
      text-decoration: none;
    }

    .header a:hover {
      text-decoration: underline;
    }

    .header .btn {
      margin-left: auto;
    }

    .login-content input {

      padding: 35px;
    }

    .login-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: rgba(255, 255, 255, 0.8);
      padding: 2rem;
      border-radius: 0.5rem;
      width: 30%;
    }

    @media (max-width: 768px) {
      .login-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 255, 255, 0.8);
        padding: 2rem;
        border-radius: 0.5rem;
        width: 100%;
      }


    }

    /* Background Slider */
    .background-slider {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .background-slider .slide {
      position: absolute;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
      opacity: 0;
      animation: fade 15s infinite;
    }

    .background-slider .slide:nth-child(1) {
      animation-delay: 0s;
    }

    .background-slider .slide:nth-child(2) {
      animation-delay: 5s;
    }

    .background-slider .slide:nth-child(3) {
      animation-delay: 10s;
    }

    /* Fade Animation */
    @keyframes fade {

      0%,
      100% {
        opacity: 0;
      }

      33%,
      66% {
        opacity: 1;
      }
    }

    /* Login Container */
  
  </style>
</head>

<body>
  <div class="header">
    <a href="privacy/index.php">Políticas de Uso</a>
    <a target="_blank" href="<?=LINK_SOBRE?>">Sobre o Sistema</a>
    <div style="color:silver">Versão <?=VERSAO?></div>
  </div>