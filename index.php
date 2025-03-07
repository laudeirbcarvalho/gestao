<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Verifica se o dispositivo é mobile
$isMobile = preg_match('/(android|iphone|ipad|ipod|mobile|opera mini|blackberry|webos)/i', $userAgent);

// Verifica se o navegador é Google Chrome
$isChrome = stripos($userAgent, 'Chrome') !== false;

// Se for mobile e não estiver usando Chrome, redireciona para um aviso
if ($isMobile && !$isChrome) {
    header('Location: aviso-navegador.html');
    exit;
}
require_once("login.php");
?>
<!-- 


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso à Central</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body.dark-mode {
            background-color: #343a40;
            color: #f8f9fa;
        }

        .card.dark-mode {
            background-color: #495057;
            color: #f8f9fa;
        }

        .icon-moon {
            display: none;
        }

        body.dark-mode .icon-sun {
            display: none;
        }

        body.dark-mode .icon-moon {
            display: inline-block;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100 dark-mode">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card dark-mode">
                    <div class="card-body">
                        <h5 class="card-title text-center">Acesso à Central</h5>
                        <form method="POST" action="login.php">
                            <input type="hidden" name="acao" value="central"/>
                            <div class="mb-3">
                                <label for="central_id" class="form-label">Código da Central</label>
                                <input type="text" class="form-control" id="central_id" name="central_id" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-secondary">Acessar</button>
                            </div>
                        </form>
                        <div class="mt-3">
                            <button id="theme-toggle" class="btn btn-secondary">
                                <i class="bi bi-sun-fill icon-sun"></i>
                                <i class="bi bi-moon-fill icon-moon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const card = document.querySelector('.card');

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            card.classList.toggle('dark-mode');
        });
    </script>
</body>

</html>

-->