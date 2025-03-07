<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
// Parâmetros do DataTables
$start = $_POST['start'];
$length = $_POST['length'];
$searchValue = $_POST['search']['value'];

// Query principal
$query = "SELECT cidade, estado, COUNT(*) as total_clientes 
          FROM clientes_shopify 
          WHERE (cidade LIKE '%$searchValue%' OR estado LIKE '%$searchValue%')
          AND (cidade != '' AND estado != '') 
          AND central_id = '$sessao_central'
          AND status != 'ativo'
          GROUP BY cidade, estado 
          ORDER BY total_clientes DESC";

// Paginação
$queryLimit = $query . " LIMIT $start, $length";
$result = $conn->query($queryLimit);

// Montar os dados
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Total de registros
$resultTotal = $conn->query("SELECT COUNT(*) as total FROM clientes_shopify WHERE central_id = '$sessao_central' AND cidade != '' AND status != 'ativo' AND estado != ''");
$totalRecords = $resultTotal->fetch_assoc()['total'];

// Total de registros filtrados
$resultFiltered = $conn->query("SELECT COUNT(*) as total 
                                FROM clientes_shopify 
                                WHERE (cidade LIKE '%$searchValue%' OR estado LIKE '%$searchValue%')
                                AND central_id = '$sessao_central'
                                AND status != 'ativo'
                                AND (cidade != '' AND estado != '')");
$totalFiltered = $resultFiltered->fetch_assoc()['total'];

// Retornar os dados no formato esperado pelo DataTables
$response = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalFiltered,
    'data' => $data
];

echo json_encode($response);
?>
 