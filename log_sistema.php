<?php
require_once('sistema/db.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "Acesso negado.";
    exit();
}

// Pegando os parâmetros de busca e paginação
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 2;  // Número de logs por página
$offset = ($page - 1) * $limit;

// Consulta para pegar os logs de auditoria com busca e paginação
$sql = "SELECT la.id, u.nome AS usuario_nome, la.acao, la.tabela_afetada, la.dados_antigos, la.dados_novos, la.data_hora, la.descricao
        FROM logs_auditoria la
        JOIN usuarios u ON la.usuario_id = u.id
        WHERE (u.nome LIKE ? OR la.tabela_afetada LIKE ? OR la.descricao LIKE ?)
        ORDER BY la.data_hora DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

// Verifique se a consulta preparou corretamente
if ($stmt === false) {
    die('Erro na preparação da consulta: ' . $conn->error);
}

// Adicionando o parâmetro de busca
$searchParam = "%$search%";
$stmt->bind_param("ssssi", $searchParam, $searchParam, $searchParam, $limit, $offset);

// Execute a consulta
$stmt->execute();

if ($stmt->errno) {
    die('Erro na execução da consulta: ' . $stmt->error);
}

// Associar os resultados
$stmt->bind_result($log_id, $usuario_nome, $acao, $tabela, $dados_antigos, $dados_novos, $data_hora, $descricao);

// Criando um array para armazenar os logs
$logs = array();

while ($stmt->fetch()) {
    $logs[] = array(
        'id' => $log_id,
        'usuario_nome' => $usuario_nome,
        'acao' => $acao,
        'tabela' => $tabela,
        'dados_antigos' => json_decode($dados_antigos), // Verifique se os dados estão em formato JSON
        'dados_novos' => json_decode($dados_novos),     // Verifique se os dados estão em formato JSON
        'data_hora' => $data_hora,
        'descricao' => $descricao
    );
}

// Calcular o número total de logs para a paginação
$sqlCount = "SELECT COUNT(*) FROM logs_auditoria la
             JOIN usuarios u ON la.usuario_id = u.id
             WHERE (u.nome LIKE ? OR la.tabela_afetada LIKE ? OR la.descricao LIKE ?)";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("sss", $searchParam, $searchParam, $searchParam);
$stmtCount->execute();
$stmtCount->bind_result($totalLogs);
$stmtCount->fetch();

// Número total de páginas
$totalPages = ceil($totalLogs / $limit);

// Retorna os dados dos logs em formato JSON
echo json_encode([
    'logs' => $logs,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
?>
