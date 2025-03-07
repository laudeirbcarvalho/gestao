<?php
require_once('sistema/db.php');

// Consulta para obter os salões ativos com os dados completos de endereço
$query_clientes = "SELECT central_id, nome, cidade, estado, endereco, pais, cep, telefone, latitude, longitude FROM clientes_shopify WHERE status='ativo'";
$result_clientes = $conn->query($query_clientes);

// Função para obter latitude e longitude via API
function obter_latitude_longitude($endereco) {
    $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($endereco) . '&format=json&addressdetails=1&limit=1';

    // Configurar o cabeçalho User-Agent para evitar bloqueio
    $options = [
        "http" => [
            "header" => "User-Agent: www.adlux.com.br (contato@adlux.com.br)"
        ]
    ];
    $context = stream_context_create($options);

    // Fazer a requisição
    $response = file_get_contents($url, false, $context);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data[0])) {
            // Retorna latitude e longitude
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }
    }
    return null;  // Caso não consiga obter dados
}

// Loop para verificar e atualizar latitude e longitude
if ($result_clientes->num_rows > 0) {
    while ($row_cliente = $result_clientes->fetch_assoc()) {
        $central_id = $row_cliente['central_id'];
        $nome_salao = $row_cliente['nome'];
        $endereco = $row_cliente['endereco'];
        $cidade = $row_cliente['cidade'];
        $estado = $row_cliente['estado'];
        $pais = $row_cliente['pais'];
        $cep = $row_cliente['cep'];
        $telefone = $row_cliente['telefone'];
        $latitude = $row_cliente['latitude'];
        $longitude = $row_cliente['longitude'];

        // Verificar se a latitude e longitude estão vazias
        if (empty($latitude) || empty($longitude)) {
            // Concatenar o endereço completo para enviar à API
            $endereco_completo = $nome_salao . ", " . $endereco . ", " . $cidade . ", " . $estado . ", " . $pais;

            // Obter latitude e longitude via API
            $localizacao = obter_latitude_longitude($endereco_completo);
            if ($localizacao) {
                $latitude = $localizacao['latitude'];
                $longitude = $localizacao['longitude'];

                // Atualizar a latitude e longitude no banco de dados
                $update_query = "UPDATE clientes_shopify SET latitude = ?, longitude = ? WHERE central_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ddi", $latitude, $longitude, $central_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa com Localização</title>

    <!-- Incluindo o Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" 
    async defer></script>

    <style>
        /* Definindo o tamanho do mapa */
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>Mapa com Localização dos Salões</h1>

    <?php
    // Exibir o mapa para cada cliente, se a latitude e longitude estiverem preenchidas
    if ($result_clientes->num_rows > 0) {
        while ($row_cliente = $result_clientes->fetch_assoc()) {
            $latitude = $row_cliente['latitude'];
            $longitude = $row_cliente['longitude'];
            $nome_salao = $row_cliente['nome'];

            // Se latitude e longitude estão presentes, exibir o mapa
            if (!empty($latitude) && !empty($longitude)) {
                echo "<h3>Localização do Salão: $nome_salao</h3>";
                echo "<div id='map' data-lat='$latitude' data-lng='$longitude'></div>";
            }
        }
    }
    ?>

    <script>
        // Função para inicializar o mapa
        function initMap() {
            // Pega os dados do mapa
            const maps = document.querySelectorAll('#map');
            maps.forEach(function(map) {
                const latitude = parseFloat(map.getAttribute('data-lat'));
                const longitude = parseFloat(map.getAttribute('data-lng'));

                // Cria o mapa com o ponto central
                const location = { lat: latitude, lng: longitude };
                const mapObj = new google.maps.Map(map, {
                    center: location,
                    zoom: 15
                });

                // Cria um marcador
                const marker = new google.maps.Marker({
                    position: location,
                    map: mapObj,
                    title: 'Localização do Salão'
                });
            });
        }
    </script>
</body>
</html>
