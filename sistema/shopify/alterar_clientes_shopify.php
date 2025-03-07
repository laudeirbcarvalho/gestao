<?php
require_once('../../sistema/db.php');
require_once('../../sistema/protege.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica se é uma requisição de busca dos dados do cliente
    if (isset($_POST['customer_id'])) {
        $customer_id = $_POST['customer_id'];

        // Busca os dados do cliente na tabela clientes_shopify
        $sql = "SELECT * FROM clientes_shopify WHERE id_shopify = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response = array(
                    'id_shopify' => $row['id_shopify'],
                    'nome' => $row['nome'],
                    'email' => $row['email'],
                    'status' => $row['status'],
                    'data_cadastro' => $row['data_cadastro'],
                    'endereco' => $row['endereco'],
                    'numero' => $row['numero'],
                    'bairro' => $row['bairro'],
                    'complemento' => $row['complemento'],
                    'cep' => $row['cep'],
                    'cidade' => $row['cidade'],
                    'estado' => $row['estado'],
                    'pais' => $row['pais'],
                    'telefone' => $row['telefone'],
                    'data_nascimento' => !empty($row['data_nascimento']) ? date('Y-m-d', strtotime($row['data_nascimento'])) : null,
                    'cpf' => $row['cpf'],
                    'cnpj' => $row['cnpj'],
                    'empresa' => $row['empresa']
                );

                echo json_encode($response);

            } else {
                echo json_encode(array('error' => 'Cliente não encontrado.'));
            }
            $stmt->close();
        } else {
            echo json_encode(array('error' => 'Erro na preparação da consulta.'));
        }
    }
    // Verifica se é uma requisição de atualização dos dados do cliente
    else {// Atualizar os dados do cliente
        $id_shopify = $_POST['id_shopify'] ?? null;
        $nome = $_POST['nome_cliente'] ?? null;
        $email = $_POST['email_cliente'] ?? null;
        $status = $_POST['status'] ?? null;
        $data_cadastro = $_POST['data_cadastro'] ?? null;
        $endereco = $_POST['endereco'] ?? null;
        $numero = $_POST['numero'] ?? null;
        $bairro = $_POST['bairro'] ?? null;
        $cep = $_POST['cep'] ?? null;
        $complemento = $_POST['complemento'] ?? null;
        $cidade = $_POST['cidade'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $pais = $_POST['pais'] ?? null;
        $telefone = $_POST['telefone'] ?? null;
        $data_nascimento = $_POST['data_nascimento'] ?? null;
        $cpf = $_POST['cpf'] ?? null;
        $cnpj = $_POST['cnpj'] ?? null;
        $empresa = $_POST['empresa'] ?? null;
    
        if (!$id_shopify) {
            echo json_encode(['success' => false, 'error' => 'ID do cliente é obrigatório.']);
            exit;
        }
    
        $sql = "UPDATE clientes_shopify SET 
                nome = ?, email = ?, status = ?, data_cadastro = ?, endereco = ?, 
                numero = ?, bairro = ?, cep = ?, complemento = ?, cidade = ?, 
                estado = ?, pais = ?, telefone = ?, data_nascimento = ?, cpf = ?, 
                cnpj = ?, empresa = ? WHERE id_shopify = ?";
    
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            $stmt->bind_param("ssssssssssssssssss", $nome, $email, $status, $data_cadastro, $endereco, $numero, $bairro, $cep, $complemento, $cidade, $estado, $pais, $telefone, $data_nascimento, $cpf, $cnpj, $empresa, $id_shopify);
    
            if ($stmt->execute()) {
                $mensagem_sistema = "Dados atualizados no banco de dados com sucesso.";
    
                // Atualizar Shopify
                $resultado_shopify = ['success' => false, 'message' => 'Função não encontrada.'];
                if (function_exists('atualizarDadosClienteShopify')) {
                    $resultado_shopify = atualizarDadosClienteShopify($id_shopify, $nome, $email, $endereco, $cidade, $estado, $pais, $telefone);
                }
    
                if ($resultado_shopify['success']) {
                    echo json_encode([
                        'success' => true,
                        'mensagem_sistema' => $mensagem_sistema,
                        'mensagem_shopify' => "Dados atualizados na Shopify com sucesso."
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensagem_sistema' => $mensagem_sistema,
                        'error_shopify' => 'Erro ao atualizar os dados na Shopify: ' . $resultado_shopify['message']
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Erro ao atualizar os dados do cliente no banco de dados.']);
            }
            $stmt->close();
        } else {
            echo json_encode(array('error' => 'Erro na preparação da consulta para atualização.'));
        }
    }
}

function atualizarDadosClienteShopify($id_shopify, $nome, $email, $endereco, $cidade, $estado, $pais, $telefone)
{
    global $shopify_store, $access_token;

    $url = "https://$shopify_store/admin/api/2023-07/customers/$id_shopify.json";

    $data = array(
        'customer' => array(
            'id' => $id_shopify,
            'first_name' => $nome,
            'email' => $email,
            'addresses' => array(
                array(
                    'address1' => $endereco,
                    'city' => $cidade,
                    'province' => $estado,
                    'country' => $pais,
                    'phone' => $telefone,
                )
            )
        )
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return array('success' => true);
    } else {
        $error_message = json_decode($response, true)['errors'] ?? 'Erro desconhecido';
        return array('success' => false, 'message' => $error_message);
    }
}
?>