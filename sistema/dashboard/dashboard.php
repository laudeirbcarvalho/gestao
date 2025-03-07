<?php
###DASHBORD INICIO###
// Últimos Pedidos
$result_pedidos = $conn->query("SELECT 
    COUNT(*) AS total_pedidos, 
    SUM(ps.total_price) AS total_valor
FROM pedidos_shopify ps
JOIN clientes_shopify cs ON ps.customer_id = cs.id_shopify
WHERE cs.central_id = '$sessao_central';
");
$data_pedidos = $result_pedidos->fetch_assoc();
$total_pedidos = $data_pedidos['total_pedidos'] ?? 0;
$total_valor_pedidos = $data_pedidos['total_valor'] ?? 0;

// Pedidos Pendentes
$result_pedidos_pendentes = $conn->query("SELECT COUNT(*) AS total_pedidos_pendentes
FROM pedidos_shopify ps
JOIN clientes_shopify cs ON ps.customer_id = cs.id_shopify
WHERE ps.financial_status = 'pending'
AND cs.central_id = '$sessao_central';
;
");
$data_pedidos_pendentes = $result_pedidos_pendentes->fetch_assoc();
$total_pedidos_pendentes = $data_pedidos_pendentes['total_pedidos_pendentes'] ?? 0;

// Pedidos Pagos na Shopify
$result_pedidos_pagos = $conn->query("
    SELECT COUNT(*) AS total_pedidos_pagos 
FROM pedidos_shopify ps
LEFT JOIN woo_comerce_integra wci ON ps.order_id = wci.order_id 
JOIN clientes_shopify cs ON ps.customer_id = cs.id_shopify
WHERE ps.financial_status = 'paid_shopify'       
AND (wci.status IS NULL OR wci.status != 'integrado')
AND cs.central_id = '$sessao_central'
");

$data_pedidos_pagos = $result_pedidos_pagos->fetch_assoc();
$total_pedidos_pagos_shopify = $data_pedidos_pagos['total_pedidos_pagos'] ?? 0;



// Pedidos pagos do Ano
$result_pedidos_ano = $conn->query("SELECT 
    COUNT(DISTINCT w.n_pedido) AS total_pedidos_ano, 
    SUM(p.total_price) AS total_valor_ano
FROM woo_comerce_integra w
LEFT JOIN pedidos_shopify p ON w.n_pedido = p.n_pedido
LEFT JOIN clientes_shopify c ON p.customer_id = c.id_shopify
WHERE w.status = 'integrado' 
  AND YEAR(w.data_hora) = YEAR(CURDATE())
  AND (p.financial_status = 'paid' OR p.financial_status = 'paid_shopify')
  AND c.central_id = '$sessao_central';");
$data_pedidos_ano = $result_pedidos_ano->fetch_assoc();
$total_pedidos_ano = $data_pedidos_ano['total_pedidos_ano'] ?? 0;
$total_valor_pedidos_ano = $data_pedidos_ano['total_valor_ano'] ?? 0;

// Pedidos pagos do Mês
$result_pedidos_mes = $conn->query("SELECT 
    COUNT(DISTINCT w.n_pedido) AS total_pedidos_mes, 
    SUM(p.total_price) AS total_valor_mes
FROM woo_comerce_integra w
LEFT JOIN pedidos_shopify p ON w.n_pedido = p.n_pedido
LEFT JOIN clientes_shopify c ON p.customer_id = c.id_shopify
WHERE w.status = 'integrado' 
  AND YEAR(w.data_hora) = YEAR(CURDATE())
  AND MONTH(w.data_hora) = MONTH(CURDATE())
  AND (p.financial_status = 'paid' OR p.financial_status = 'paid_shopify')
  AND c.central_id = '$sessao_central';");
$data_pedidos_mes = $result_pedidos_mes->fetch_assoc();
$total_pedidos_mes = $data_pedidos_mes['total_pedidos_mes'] ?? 0;
$total_valor_pedidos_mes = $data_pedidos_mes['total_valor_mes'] ?? 0;

// Pedidos pagos do Dia
$result_pedidos_dia = $conn->query("SELECT 
    COUNT(DISTINCT w.n_pedido) AS total_pedidos_dia, 
    SUM(p.total_price) AS total_valor_dia
FROM woo_comerce_integra w
LEFT JOIN pedidos_shopify p ON w.n_pedido = p.n_pedido
LEFT JOIN clientes_shopify c ON p.customer_id = c.id_shopify
WHERE w.status = 'integrado' 
  AND DATE(w.data_hora) = CURDATE()
  AND (p.financial_status = 'paid' OR p.financial_status = 'paid_shopify')
  AND c.central_id = '$sessao_central';");
$data_pedidos_dia = $result_pedidos_dia->fetch_assoc();
$total_pedidos_dia = $data_pedidos_dia['total_pedidos_dia'] ?? 0;
$total_valor_pedidos_dia = $data_pedidos_dia['total_valor_dia'] ?? 0;

// Últimos Clientes
$result_clientes = $conn->query("SELECT COUNT(*) as total_clientes FROM clientes_shopify  WHERE central_id = '$sessao_central'");
$data_clientes = $result_clientes->fetch_assoc();
$total_clientes = $data_clientes['total_clientes'] ?? 0;
$total_clientes = str_pad($total_clientes, 2, '0', STR_PAD_LEFT);

// Clientes do Ativo
$result_clientes_ano = $conn->query("SELECT COUNT(*) as total_clientes_ano FROM clientes_shopify WHERE status = 'ativo' AND central_id = '$sessao_central'");
$data_clientes_ano = $result_clientes_ano->fetch_assoc();
$total_clientes_ano = $data_clientes_ano['total_clientes_ano'] ?? 0;
$total_clientes_ano = str_pad($total_clientes_ano, 2, '0', STR_PAD_LEFT);

 
// Clientes do Mês
$result_clientes_mes = $conn->query("SELECT COUNT(*) as total_clientes_mes FROM clientes_shopify WHERE status = 'ativo' AND central_id = '$sessao_central' AND MONTH(data_cadastro) = MONTH(CURDATE()) AND YEAR(data_cadastro) = YEAR(CURDATE())");
$data_clientes_mes = $result_clientes_mes->fetch_assoc();
$total_clientes_mes = $data_clientes_mes['total_clientes_mes'] ?? 0;
$total_clientes_mes = str_pad($total_clientes_mes, 2, '0', STR_PAD_LEFT);

// Clientes do Mês do ano anterior
$result_clientes_mes_ano_anterior = $conn->query("SELECT COUNT(*) as total_clientes_mes_ano_anterior FROM clientes_shopify WHERE central_id = '$sessao_central' AND MONTH(data_cadastro) = MONTH(CURDATE()) AND YEAR(data_cadastro) = YEAR(CURDATE()) - 1");
$data_clientes_mes_ano_anterior = $result_clientes_mes_ano_anterior->fetch_assoc();
$total_clientes_mes_ano_anterior = $data_clientes_mes_ano_anterior['total_clientes_mes_ano_anterior'] ?? 0;
$total_clientes_mes_ano_anterior = str_pad($total_clientes_mes_ano_anterior, 2, '0', STR_PAD_LEFT);



// Clientes do Dia
$result_clientes_dia = $conn->query("SELECT COUNT(*) as total_clientes_dia FROM clientes_shopify WHERE status='ativo' AND central_id = '$sessao_central' AND DATE(data_cadastro) = CURDATE()");
$data_clientes_dia = $result_clientes_dia->fetch_assoc();
$total_clientes_dia = $data_clientes_dia['total_clientes_dia'] ?? 0;
$total_clientes_dia = str_pad($total_clientes_dia, 2, '0', STR_PAD_LEFT);

// Clientes Ativos Total
$result_clientes_ativos = $conn->query("SELECT COUNT(*) as total_clientes_ativos FROM clientes_shopify WHERE status='ativo' AND central_id = '$sessao_central'");
$data_clientes_ativos = $result_clientes_ativos->fetch_assoc();
$total_clientes_ativos = $data_clientes_ativos['total_clientes_ativos'] ?? 0;
$total_clientes_ativos = str_pad($total_clientes_ativos, 2, '0', STR_PAD_LEFT);

//Clientes Ativos no Mês
$result_clientes_ativos_mes = $conn->query("
    SELECT COUNT(*) as total_clientes_ativos 
    FROM clientes_shopify 
    WHERE status='ativo' 
    AND central_id = '$sessao_central' 
    AND MONTH(data_ativacao) = MONTH(CURDATE()) 
    AND YEAR(data_ativacao) = YEAR(CURDATE())
");

$data_clientes_ativos_mes = $result_clientes_ativos_mes->fetch_assoc();
$total_clientes_ativos_mes = $data_clientes_ativos_mes['total_clientes_ativos'] ?? 0;
$total_clientes_ativos_mes = str_pad($total_clientes_ativos_mes, 2, '0', STR_PAD_LEFT);


// Clientes em Analise Total Geral
$result_clientes_analise = $conn->query("SELECT COUNT(*) as total_clientes_analise FROM clientes_shopify WHERE status='inativo' AND central_id = '$sessao_central'");
$data_clientes_analise = $result_clientes_analise->fetch_assoc();
$total_clientes_analise = $data_clientes_analise['total_clientes_analise'] ?? 0;
$total_clientes_analise = str_pad($total_clientes_analise, 2, '0', STR_PAD_LEFT);

//Clientes em Analise Mes Atual
$result_clientes_analise_mes = $conn->query("
    SELECT COUNT(*) as total_clientes_analise_mes 
    FROM clientes_shopify 
    WHERE central_id = '$sessao_central' AND status='inativo' 
    AND MONTH(data_cadastro) = MONTH(CURDATE()) 
    AND YEAR(data_cadastro) = YEAR(CURDATE())
");

$data_clientes_analise_mes = $result_clientes_analise_mes->fetch_assoc();
$total_clientes_analise_mes = $data_clientes_analise_mes['total_clientes_analise_mes'] ?? 0;
$total_clientes_analise_mes = str_pad($total_clientes_analise_mes, 2, '0', STR_PAD_LEFT);


// Clientes do Ano no anterior
$result_clientes_dia_ano_anterior = $conn->query("SELECT COUNT(*) as total_clientes_dia_ano_anterior FROM clientes_shopify WHERE DATE(data_cadastro) = CURDATE()  AND YEAR(data_cadastro) = YEAR(CURDATE()) - 1");
$data_clientes_dia_ano_anterior = $result_clientes_dia_ano_anterior->fetch_assoc();
$total_clientes_dia_ano_anterior = $data_clientes_dia_ano_anterior['total_clientes_dia_ano_anterior'] ?? 0;
$total_clientes_dia_ano_anterior = str_pad($total_clientes_dia_ano_anterior, 2, '0', STR_PAD_LEFT);


//Newslletter
$result_clientes_newsletter = $conn->query("
    SELECT COUNT(*) as total_clientes_newsletter 
    FROM clientes_shopify 
    WHERE status LIKE '%newsletter%'
    AND central_id = '$sessao_central'
");

$data_clientes_newsletter = $result_clientes_newsletter->fetch_assoc();
$total_clientes_newsletter = $data_clientes_newsletter['total_clientes_newsletter'] ?? 0;
$total_clientes_newsletter = str_pad($total_clientes_newsletter, 2, '0', STR_PAD_LEFT);


//Ultimo cliente cadastrado na shopify
$result_ultimo_cliente = $conn->query("
    SELECT nome, email, status, data_cadastro
    FROM clientes_shopify 
    WHERE central_id = '$sessao_central' 
    ORDER BY data_cadastro DESC 
    LIMIT 1
");

// Processa o resultado
if ($result_ultimo_cliente && $result_ultimo_cliente->num_rows > 0) {
    $ultimo_cliente = $result_ultimo_cliente->fetch_assoc();

    // Verifica o nome e status
    $valors = $ultimo_cliente['status'] ?? '';
    $status = strtolower($valors);
    $nome_cliente_sh = !empty($ultimo_cliente['nome'])
        ? htmlspecialchars($ultimo_cliente['nome'])
        : htmlspecialchars($ultimo_cliente['email']);

    if (empty($nome_cliente_sh) || strpos($status, 'newsletter') !== false) {
        $nome_cliente_sh = 'Newsletter ' . htmlspecialchars($ultimo_cliente['email']);
    } else {
        $nome_cliente_sh = $ultimo_cliente['status'];
    }

    // Formata a data de cadastro
    $data_cadastro = !empty($ultimo_cliente['data_cadastro'])
        ? (new DateTime($ultimo_cliente['data_cadastro']))->format('d/m/Y H:i:s')
        : 'Data indisponível';

    // Exibe o cliente e a data
    $ultimo_cliente = "<p class='text-center'>O último cliente cadastrado é:  $nome_cliente_sh  em  $data_cadastro.</p>";
} else {
    $ultimo_cliente = "<p class='text-center'>Nenhum cliente cadastrado encontrado.</p>";
}




// Últimos Estoques
$result_estoques = $conn->query("SELECT COUNT(*) as total_produtos, SUM(estoque) as total_estoque FROM produtos");
$data_estoques = $result_estoques->fetch_assoc();
$total_produtos = $data_estoques['total_produtos'] ?? 0;
$total_estoque = $data_estoques['total_estoque'] ?? 0;

// Resumo de Pedidos Integrados
$query = "
    SELECT 
    (SELECT COUNT(*) FROM woo_comerce_integra WHERE status = 'integrado' AND YEAR(data_hora) = YEAR(CURDATE())) AS total_pedidos,
    w.n_pedido, 
    w.operador, 
    w.data_hora, 
    p.customer_id, 
    c.nome AS nome_cliente,
    c.central_id
FROM woo_comerce_integra w
LEFT JOIN pedidos_shopify p ON w.n_pedido = p.n_pedido
LEFT JOIN clientes_shopify c ON p.customer_id = c.id_shopify
WHERE  w.status = 'integrado' AND YEAR(w.data_hora) = YEAR(CURDATE()) 
ORDER BY w.data_hora DESC
LIMIT 1;
";

$resultado_integracao = $conn->query($query);

if ($resultado_integracao && $resultado_integracao->num_rows > 0) {
    $resumo_integracao = $resultado_integracao->fetch_assoc();
    $total_pedidos_integrados = $resumo_integracao['total_pedidos'] ?? 0;
    $numero_ultimo_pedido = $resumo_integracao['n_pedido'] ?? 'N/A';
    $operador_ultimo_pedido = $resumo_integracao['operador'] ?? 'N/A';
    $ultima_data_pedido = $resumo_integracao['data_hora'] ?? 'N/A';
    $nome_cliente = $resumo_integracao['nome_cliente'] ?? 'N/A';
} else {
    $total_pedidos_integrados = 0;
    $numero_ultimo_pedido = 'N/A';
    $operador_ultimo_pedido = 'N/A';
    $ultima_data_pedido = 'N/A';
    $nome_cliente = 'N/A';
}

 
 

?>