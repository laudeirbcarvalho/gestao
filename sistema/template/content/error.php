<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .error-code {
            font-size: 10rem;
            font-weight: bold;
            color: #dc3545;
        }
        .error-message {
            font-size: 2rem;
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <span class="error-code">403</span>
                <h1 class="error-message">Acesso Negado</h1>
                <p>Desculpe, você não tem permissão para acessar esta página.</p>
                <a href="/" class="btn btn-primary">Voltar para a página inicial</a>
            </div>
        </div>
    </div>
</body>
</html>