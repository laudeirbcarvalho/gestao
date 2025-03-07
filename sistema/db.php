<?php
session_start();


//ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; // Altere se necessário
$username = "adlux_gestao"; // Altere se necessário
$password = "y!h,g.DfuM&~"; // Altere se necessário
$dbname = "adlux_gestao"; // Altere para o nome do seu banco de dados

//Nome da Loja para o sistema Shopify (fornecedor)
$supplier = "Adlux";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//Registra os Logs do Sistema
$log_file = 'cron_log.txt';
function registrar_log($usuario_id, $acao, $tabela_afetada, $dados_antigos = null, $dados_novos = null, $descricao = null)
{
    global $conn; // Supondo que a conexão já esteja configurada

    // Preparar os dados antigos e novos como JSON, caso existam
    $dados_antigos_json = $dados_antigos ? json_encode($dados_antigos) : null;
    $dados_novos_json = $dados_novos ? json_encode($dados_novos) : null;

    // Preparar a consulta SQL para inserir no log de auditoria
    $sql = "INSERT INTO logs_auditoria (usuario_id, acao, tabela_afetada, dados_antigos, dados_novos, descricao)
            VALUES (?, ?, ?, ?, ?, ?)";

    // Preparar a consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $usuario_id, $acao, $tabela_afetada, $dados_antigos_json, $dados_novos_json, $descricao);

    // Executar a consulta
    $stmt->execute();
}

//Busca as informações de Log de Cron
function obter_ultima_data_execucao($log_file, $tipo)
{
    $ultima_data = 'Data não disponível';

    // Verifica se o arquivo existe e não está vazio
    if (file_exists($log_file) && filesize($log_file) > 0) {
        $linhas = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Filtra as linhas que correspondem ao tipo desejado
        $linhas_filtradas = array_filter($linhas, function ($linha) use ($tipo) {
            return strpos($linha, $tipo) !== false;
        });

        // Se existirem linhas filtradas, pega a última linha
        if (!empty($linhas_filtradas)) {
            $ultima_linha = end($linhas_filtradas);

            // Extrai a data e hora da linha
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $ultima_linha, $matches)) {
                $ultima_data = date("d/m/Y H:i:s", strtotime($matches[1]));
            }
        }
    }

    return $ultima_data;
}

//Saída
$data_ultimos_pedidos = obter_ultima_data_execucao($log_file, 'Pedidos');
$data_ultimos_clientes = obter_ultima_data_execucao($log_file, 'Clientes');
$data_ultimos_estoque = obter_ultima_data_execucao($log_file, 'Estoque');


// CONFIGURAÇÕES DO SISTEMA
if (isset($_SESSION['central_id'])) {
    // Se a sessão já tiver o central_id, usa o valor
    $sessao_central = $_SESSION['central_id'];
} else {
    if (isset($_GET['cron_central'])) {
        $sessao_central = $_GET['cron_central'];
    } else {
        // Lista de domínios permitidos e seus central_id
        $dominios_permitidos = [
            'gestao.adlux.com.br' => 900758, // Código Default para este domínio
            'manaus.adlux.com.br' => 467669, // Código para o domínio de cliente Manaus
            // Adicione mais domínios conforme necessário
        ];

        // Captura o domínio atual
        $dominio_atual = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'gestao.adlux.com.br';
        $dominio_atual = preg_replace('/^www\./', '', $dominio_atual); // Remove "www." do início, se existir

        // Verifica se o domínio está na lista permitida
        if (array_key_exists($dominio_atual, $dominios_permitidos)) {
            $sessao_central = $dominios_permitidos[$dominio_atual]; // Define o central_id correspondente
        } else {
            // Define um central_id padrão para casos de falha
            $sessao_central = 900758; // Código padrão para fallback (gestão)
        }
    }
}


if (isset($dominio_atual)) {
    $dominio_atual = $dominio_atual; // Use a variável
} else {
    $dominio_atual = "";
}



$sql = "SELECT * FROM `config` WHERE central_id = '$sessao_central'"; // Ajuste conforme necessário
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $config = mysqli_fetch_assoc($result);
} else {
    $config = [];
}


$sqlcentral = "SELECT * FROM `central` WHERE central_id = '$sessao_central'"; // Ajuste conforme necessário
$resultcentral = mysqli_query($conn, $sqlcentral);
if ($resultcentral && mysqli_num_rows($resultcentral) > 0) {
    $central = mysqli_fetch_assoc($resultcentral);
} else {
    $central = [];
}

// Função para normalizar o texto
function normalizeText($text)
{
    if (!is_string($text)) {
        return '';
    }
    return trim($text); // Ou outras normalizações necessárias
}

//LAYOUT DO SISTEMA
define('ICONE', $config['system_icon'] ?? 'default_icon.png');
define('LOGO', $config['system_logo'] ?? 'default_logo.png');
define('DESCRICAO', isset($config['descriptions']) ? normalizeText($config['descriptions']) : null);
define('PALAVRAS_CHAVE', isset($config['keywords']) ? normalizeText($config['keywords']) : null);
define('LINK_SOBRE', $config['link_sobre'] ?? 'https://default-link.com');
define('POLITICAS_USO', $config['politicas_uso'] ?? 'Políticas de uso padrão.');

//PATH DO SISTEMA
define(constant_name: 'PATH', value: $config['path_sistema'] ?? '/default/path/sistema/');
define(constant_name: 'PATH_MAIL', value: $config['path_mail'] ?? 'seuemail@dominio.com');
define(constant_name: 'URL', value: $config['url_sistema'] ?? 'https://seusite.com');
define(constant_name: 'UPLOADS', value: 'img/upload/' ?? 'img/upload/');
//CAMINHO DO SISTEMA
define(constant_name: 'SISTEMA', value: PATH . 'sistema/' ?? 'sistema/');
//CAMINHO DO TEMPLATE
define(constant_name: 'TEMPLATE', value: PATH . 'sistema/template/' ?? 'sistema/template/');



//PONTUAÇÃO MÁXIMA PARA O USUÁRIO GANHAR UM PRÊMIO
define(constant_name: 'PONTUACAO', value: $config['pontuacao_maxima'] ?? null);
//DEFINIÇÕES DO SISTEMA
define(constant_name: 'NOME_SISTEMA', value: $config['nome_sistema'] ?? null);
define(constant_name: 'NOME_EMPRESA', value: $config['nome_empresa'] ?? null);
define(constant_name: 'EMAIL_EMPRESA', value: $config['email_empresa'] ?? null);
define(constant_name: 'WHATSAPP_EMPRESA', value: $config['whatsapp'] ?? null);
define(constant_name: 'TEL_EMPRESA', value: $config['tel_empresa'] ?? null);
define(constant_name: 'ENDERECO_EMPRESA', value: $config['endereco_empresa'] ?? null);
// Validar e definir constantes do array $config
define('NUMERO', $config['numero'] ?? null);
define('BAIRRO', $config['bairro'] ?? null);
define('CIDADE', $config['cidade'] ?? null);
define('ESTADO', $config['estado'] ?? null);
define('CEP', $config['cep'] ?? null);
define('DOCUMENTO', $config['documento'] ?? null);
define('VERSAO', $config['versao'] ?? null);

// DEFINIÇÕES DE ENVIO DE E-MAIL
define('HOST_SMTP', $config['smtp_host'] ?? null);
define('EMAIL_SMTP', $config['smtp_email'] ?? null);
define('SENHA_SMTP', $config['smtp_senha'] ?? null);
define('PORTA_SMTP', $config['smtp_porta'] ?? null);

// DEFINIÇÕES DA SHOPIFY
define('NOME_SHOPIFY', $config['nome_shopify'] ?? null);
define('SHOPIFY_STORE', $config['shopify_store'] ?? null);
define('SHOPIFY_TOKEN', $config['shopify_token'] ?? null);
define('SHOPIFY_URL_LOJA', $config['shopify_url_loja'] ?? null);
define('SHOPIFY_URL_LOJA_REDIRECIONA_CADASTRO', $config['shopify_url_cadastro'] ?? null);
define('SHOPIFY_URL_LOJA_REDIRECIONA_CADASTRA_SENHA', $config['shopify_url_senha'] ?? null);

// DEFINIÇÕES DA WOO
define('NOME_WOO', $config['nome_woo'] ?? null);
define('URL_WOO', $config['woocommerce_url'] ?? null);
define('KEY_WOO', $config['consumer_key'] ?? null);
define('SECRET_WOO', $config['consumer_secret'] ?? null);
define('WOO_URL_LOJA_REDIRECIONA_CADASTRO', $config['woocommerce_url_cadastro'] ?? null);

// Validar e definir constantes do array $central
define('CENTRAL_ID', $central['central_id'] ?? null);
define('CENTRAL_NOME', $central['central_nome'] ?? null);
define('CENTRAL_RESPONSVEL', $central['responsavel'] ?? null);
define('CENTRAL_EMAIL', $central['central_email'] ?? null);
define('CENTRAL_WHATSAPP', $central['central_whatsapp'] ?? null);
define('CENTRAL_SUPERUSER', $central['superuser'] ?? null);

//EAD
define('NOME_EAD', $config['nome_ead'] ?? null);

//Verifica se está logado se não direciona para o login

// Verifica se o usuário está logado e tem permissão para editar
/*
if (strpos($_SERVER['REQUEST_URI'], 'login.php') != true) {

    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . PATH . ' login.php');
        exit();
    }
}
    */

    //Se o cliente não estiver logado redireciona ele para a pagina de login

    // Verifica se o usuário está logado e tem permissão para excluir
 
