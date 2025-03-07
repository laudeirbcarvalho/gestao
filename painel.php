<?php
require('sistema/template/header/header_painel.php');
require('sistema/template/content/siderbar.php');
require_once('sistema/dashboard/dashboard.php');
include('sistema/protege.php');


//CABEÇALHO DO DASHBOARD
require_once('sistema/template/header/header_dashboard.php');

//DASHBOARD
require_once('sistema/template/content/dashboard/dashboard.php');

//MAPA QUE MOSTRA AS CIDADES COM CLIENTES
// require_once('template/content/mapa.php');     

//USUARIOS DO SISTEMA
require_once('sistema/template/content/system/user/usuarios.php');

//CLIENTES E PEDIDOS DA SHOPIFY
require_once('sistema/template/content/shopify/shopify.php');

//CLIENTES E PEDIDO E PRODUTOS DO WOOCOMERCE 
require_once('sistema/template/content/woocomerce/woocomerce.php');

//FORMULARIO DE CADASTRO DE UM CLIENTE NA SHOPIFY
require_once('sistema/template/content/shopify/form_cadastrar_cliente.php');

//FORMULARIO DE ALTERAÇÃO DE DADOS DO USUARIO LOGADO NO SISTEMA
require_once('sistema/template/content/system/user/editar_usuario_logado.php');

//CRM
require_once('sistema/template/content/ead/ead.php');

//AFILIADOS
require_once('sistema/template/content/afiliados/afiliados.php');

//MARKETPLACES
require_once('sistema/template/content/marketplace/marketplace.php');

//CORPORATIVO 
require_once('sistema/template/content/corporativo/corporativo.php');

//ADM DE CENTRAIS - PESSOAS QUE VÃO ADMINISTRAR O SISTEMA INDEPENDENTEMENTE
require_once('sistema/template/content/system/central/central.php');

//CONFIGURAÇÕES DO SISTEMA
require_once('sistema/template/content/system/config/config.php');

//LOGS DO SISTEMA 
require_once('sistema/template/content/logs/logs.php');

//FOOTER
require('sistema/template/footer/footer_painel.php');

?>