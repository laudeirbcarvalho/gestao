<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json'); // Força o retorno como JSON
ob_clean();

 
// Sanitiza e valida o pedido_id
$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);
if (!$pedido_id) {
  echo json_encode(['error' => true, 'message' => 'Pedido ID inválido ou não fornecido.']);
  exit();
}

// Buscar o pedido no banco de dados
$sql = "SELECT order_id,n_pedido FROM pedidos_shopify WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $pedido = $result->fetch_assoc();
  $order_id = $pedido['order_id'];
  $numerodopedido = $pedido['n_pedido'];

  // Insere os dados na tabela desejada
  $operador = $_SESSION['nome']; // Usuário logado
  $status = 'integrado'; // Status desejado
  $data_hora = date('Y-m-d H:i:s'); // Data e hora atual

  // Inserção na tabela
  $insert_sql = "INSERT INTO woo_comerce_integra (n_pedido, order_id, status, data_hora, operador) 
                 VALUES (?, ?, ?, ?, ?)";
  
  $stmt_insert = $conn->prepare($insert_sql);
  $stmt_insert->bind_param("sisss", $numerodopedido,$order_id, $status, $data_hora, $operador);

  if ($stmt_insert->execute()) {
    echo json_encode(['error' => false, 'message' => 'Pedido inserido com sucesso!']);
  } else {
    echo json_encode(['error' => true, 'message' => 'Erro ao inserir pedido.']);
  }

  $stmt_insert->close();
} else {
  echo json_encode(['error' => true, 'message' => 'Pedido não encontrado.']);
}

$stmt->close();
$conn->close();
?>
