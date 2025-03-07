<?php
// Definir cabeçalho para JSON
header('Content-Type: application/json');

// Conectar ao banco de dados (substitua com seus próprios dados de conexão)
require_once('../../sistema/db.php');

// Função para baixar e salvar a imagem na pasta local
function baixarImagemLocal($urlImagem) {
    // Remover a URL base e pegar apenas o nome do arquivo
    $partesUrl = explode('/', $urlImagem);
    $nomeImagem = end($partesUrl);

    // Caminho destino na pasta local
    $caminhoDestino = '../../img/produtos/' . $nomeImagem;

    // Baixar o conteúdo da imagem
    $conteudoImagem = file_get_contents($urlImagem);

    if ($conteudoImagem === false) {
        return [
            'status' => 'erro',
            'mensagem' => 'Falha ao baixar a imagem.',
            'urlImagem' => $urlImagem
        ];
    }

    // Salvar a imagem na pasta local
    if (file_put_contents($caminhoDestino, $conteudoImagem)) {
        return [
            'status' => 'sucesso',
            'mensagem' => 'Upload local da imagem bem-sucedido.',
            'caminhoImagem' => $caminhoDestino
        ];
    } else {
        return [
            'status' => 'erro',
            'mensagem' => 'Falha ao salvar a imagem na pasta.',
            'caminhoImagem' => $caminhoDestino
        ];
    }
}

// Função para atualizar a imagem do produto no banco de dados
function updateProductImage($sku, $image) {
    global $conn;

    $image = str_replace('../../', '', $image);

    // SQL para atualizar a imagem do produto
    $sql_update = "UPDATE produtos SET imagem = ?, atualizado_em = NOW() WHERE sku = ?";
    $stmt_update = $conn->prepare($sql_update);

    $stmt_update->bind_param("ss", $image, $sku);

    // Verificar se a atualização foi bem-sucedida
    if ($stmt_update->execute()) {
        return [
            'status' => 'sucesso',
            'mensagem' => 'Imagem do produto atualizada com sucesso!',
            'sku' => $sku,
            'imagem' => $image
        ];
    } else {
        return [
            'status' => 'erro',
            'mensagem' => 'Falha ao atualizar a imagem do produto.',
            'sku' => $sku
        ];
    }
}

// Verificar se os dados foram enviados via POST
if (isset($_POST['sku']) && isset($_POST['imagem'])) {
    // Obter dados via POST
    $sku = htmlspecialchars($_POST['sku'], ENT_QUOTES, 'UTF-8');
    $imagem = filter_var($_POST['imagem'], FILTER_SANITIZE_URL);

    // Baixar e salvar a imagem na pasta
    $resultadoImagem = baixarImagemLocal($imagem);

    // Verificar se o upload foi bem-sucedido
    if ($resultadoImagem['status'] == 'sucesso') {
        // Atualizar a imagem no banco de dados
        $resultadoAtualizacao = updateProductImage($sku, $resultadoImagem['caminhoImagem']);

        // Retornar o resultado em JSON
        echo json_encode($resultadoAtualizacao);
    } else {
        // Caso de erro no upload
        echo json_encode($resultadoImagem);
    }
} else {
    // Caso os dados necessários não tenham sido enviados via POST
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Dados não recebidos via POST. Verifique os parâmetros enviados.'
    ]);
}
?>
