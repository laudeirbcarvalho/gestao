<?php
require 'sistema/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit();
}


$sql = "SELECT id, nome, email, senha, data_cadastro, status, avatar, whatsapp, permissao, cargo
        FROM usuarios
        WHERE nome LIKE ?
        ORDER BY data_cadastro DESC
        LIMIT ?, ?";

if (isset($_GET['search'])) {
    $search_param = '%' . $_GET['search'] . '%'; // Parâmetro de busca (exemplo)
}
$start_from = 0; // Ponto de partida para a paginação (exemplo)
$results_per_page = 10; // Número de resultados por página (exemplo)

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $search_param, $start_from, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();

// O restante do código do painel vai aqui
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Graficos no dashboard-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Mapas no dashboard-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Adicionado jQuery -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Bootstrap CSS 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
-->
    <link rel="stylesheet" href="/css/style.css">




    <title><?= NOME_SISTEMA ?></title>

</head>

<body>


    <!--esta div termina no footer_painel sem este o content fica desalinhado-->
    <div class="content" class="">

        <!-- Ícone do menu hamburguer -->
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>