<?php require_once('../db.php');?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Políticas de uso <?=NOME_SISTEMA?> | <?=NOME_EMPRESA?></title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3 {
            color: #333;
        }
        p {
            margin-bottom: 15px;
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
        .container {
            margin-top: 70px; /* Espaçamento para evitar sobreposição com o header */
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="btn btn-primary" onclick="history.back()">Voltar</button>
         
    </div>

    <div class="container">
         <?=POLITICAS_USO?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
