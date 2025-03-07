<div id="settings" class="settings" style="display: none;">
    <div id="response-message" class="d-none"></div>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-cog"></i> Configurações do Sistema</h5>
      </div>
      <div class="card-body">
        <form id="settings-form" class="needs-validation" novalidate>



          <!--Configurações da Empresa-->
          <div class="row bg-info text-black">
            <div class="col-md-12">
              <h2 class="text-center">Configurações da Empresa</h2>
            </div>
            <div class="col-md-6 mb-3">
              <label for="company-name" class="form-label">Nome da Empresa</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="company-name" value="<?php echo $config['nome_empresa']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-email" class="form-label">Email da Empresa</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="company-email"
                  value="<?php echo $config['email_empresa']; ?>" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-address" class="form-label">Endereço da Empresa</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-address"
                  value="<?php echo $config['endereco_empresa']; ?>" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="whatsapp" class="form-label">Número</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-numero" value="<?php echo $config['numero']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-address" class="form-label">Bairro</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-bairro" value="<?php echo $config['bairro']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="whatsapp" class="form-label">Cidade</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-cidade" value="<?php echo $config['cidade']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-address" class="form-label">Estado</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-estado" value="<?php echo $config['estado']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="whatsapp" class="form-label">Cep</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="company-cep" value="<?php echo $config['cep']; ?>" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-address" class="form-label">Documento</label>
              <div class="input-group">
                <span class="input-group-text"><i class="far fa-id-card"></i></span>
                <input type="text" class="form-control" id="company-documento"
                  value="<?php echo $config['documento']; ?>" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="whatsapp" class="form-label">WhatsApp</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                <input type="text" class="form-control" id="whatsapp_empresa" value="<?php echo $config['whatsapp']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="company-phone" class="form-label">Telefone da Empresa</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" class="form-control" id="company-phone" value="<?php echo $config['tel_empresa']; ?>"
                  required>
              </div>
            </div>


            <!--
            <div class="col-md-6 mb-3">
              <label for="login-email" class="form-label">Login de Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="login-email" value="<?php echo $config['login_email']; ?>"
                  required>
              </div>
            </div>
             -->

          </div>


          <hr>


          <!-- Configurações ShopiFy -->
          <div class="row bg-primary text-white">
            <div class="col-md-12">
              <h2 class="text-center">Configurações ShopiFy</h2>
            </div>

            <div class="col-md-12 mb-3">
              <?php

              $habilitar_shopify = $config["habilitar_shopify"];
              $checked = ($habilitar_shopify === 'sim') ? 'checked' : '';
              ?>
              <label for="shopify-url-senha" class="form-label">Habilitar</label>
              <input type="checkbox" name="habilitar_shopify" value="sim" <?= $checked ?>>
            </div>

            <div class="col-md-12 mb-3">

              <label for="shopify-url-senha" class="form-label">Nome para o Shopify</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-award"></i></span>
                <input type="text" class="form-control" id="nome-shopify" value="<?php echo $config['nome_shopify']; ?>"
                  required>
              </div>
            </div>


            <div class="col-md-6 mb-3">
              <label for="shopify-store" class="form-label">Shopify Store</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shopping-cart"></i></span>
                <input type="text" class="form-control" id="shopify-store"
                  value="<?php echo $config['shopify_store']; ?>">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-token" class="form-label">Shopify Token</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" class="form-control" id="shopify-token"
                  value="<?php echo $config['shopify_token']; ?>">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url" class="form-label">Shopify URL Loja</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="shopify-url"
                  value="<?php echo $config['shopify_url_loja']; ?>">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-cadastro" class="form-label">Shopify URL Cadastro</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="shopify-url-cadastro"
                  value="<?php echo $config['shopify_url_cadastro']; ?>">
              </div>
              <button type="button" id="test-connection-shopify" class="btn btn-primary">Testar Conexão</button>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Shopify URL recupera Senha</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="shopify-url-senha"
                  value="<?php echo $config['shopify_url_senha']; ?>">
              </div>

            </div>




          </div>

          <hr>

          <!--Configurações do WOOCOMERCE-->
          <div class="row bg-secondary text-white">
            <div class="col-md-12">
              <h2 class="text-center">Configurações Woocomerce <?= NOME_WOO ?></h2>
            </div>


            <div class="col-md-12 mb-3">
              <label for="max-points" class="form-label">Nome para o Woocommerce</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-dolly-flatbed"></i></span>
                <input type="text" class="form-control" id="nome-woo" value="<?php echo $config['nome_woo']; ?>"
                  required>
              </div>
            </div>


            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Consumer Key WooComerce</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" class="form-control" id="consumer-key" value="<?php echo $config['consumer_key']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Consumer Secret do Woocomerce</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="text" class="form-control" id="consumer-secret"
                  value="<?php echo $config['consumer_secret']; ?>">
              </div>
              <button type="button" id="test-connection-woo" class="btn btn-primary">Testar Conexão</button>

            </div>


            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">URL da API do Woocomerce sem o endpoint</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shopping-cart"></i></span>
                <input type="text" class="form-control" id="woocommerce-url"
                  value="<?php echo $config['woocommerce_url']; ?>">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Woocomerce URL Cadastro </label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shopping-cart"></i></span>
                <input type="text" class="form-control" id="woocommerce-url-cadastro"
                  value="<?php echo $config['woocommerce_url_cadastro']; ?>">
              </div>
            </div>

            

          </div>

          <hr>

          <!-- Configurações do sistema-->
          <div class="row bg-warning text-black">
            <div class="col-md-12">
              <h2 class="text-center">Configurações do <?= NOME_SISTEMA ?></h2>
            </div>

            <div class="col-md-12 mb-3">
              <label for="system-name" class="form-label">Nome do Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-server"></i></span>
                <input type="text" class="form-control" id="system-name" value="<?php echo $config['nome_sistema']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="system-version" class="form-label">Versão do Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-cogs"></i></span>
                <input type="text" class="form-control" id="system-version" value="<?php echo $config['versao']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="max-points" class="form-label">Pontuação Máxima para premiação</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-trophy"></i></span>
                <input type="text" class="form-control" id="max-points"
                  value="<?php echo $config['pontuacao_maxima']; ?>" required>
              </div>
            </div>


            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Path PHPMAIL</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="path-mail" value="<?php echo $config['path_mail']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="max-points" class="form-label">Path Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="path-sistema" value="<?php echo $config['path_sistema']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="smtp-host" class="form-label">SMTP Host</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-server"></i></span>
                <input type="text" class="form-control" id="smtp-host" value="<?php echo $config['smtp_host']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="smtp-port" class="form-label">SMTP Porta</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-plug"></i></span>
                <input type="number" class="form-control" id="smtp-port" value="<?php echo $config['smtp_porta']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="smtp-username" class="form-label">SMTP Username</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="smtp-username" value="<?php echo $config['smtp_email']; ?>"
                  required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="smtp-password" class="form-label">SMTP Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="password" class="form-control" id="smtp-password"
                  value="<?php echo $config['smtp_senha']; ?>" required>
              </div>
            </div>

            <div class="col-md-12 mb-3">
              <label for="shopify-url-senha" class="form-label">URL do Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-server"></i></span>
                <input type="text" class="form-control" id="url-sistema" value="<?php echo $config['url_sistema']; ?>"
                  required>
              </div>
            </div>


            <!-- Campos para upload das imagens -->
            <div class="col-md-6 mb-3">
              <label for="system-icon" class="form-label">Ícone do Sistema:</label>
              <div class="input-group">
                <input type="file" id="system-icon" name="system_icon" accept="image/*"
                  onchange="previewImage('system-icon', 'icon-preview')">
                <!-- Exibe a imagem existente, se houver -->
                <img id="icon-preview" src="<?= ICONE ?>" alt="Ícone do Sistema"
                  style="max-width: 100px; margin-top: 10px; <?php echo ICONE ? '' : 'display: none;'; ?>">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="system-logo" class="form-label">Logo do Sistema:</label>
              <div class="input-group">
                <input type="file" id="system-logo" name="system_logo" accept="image/*"
                  onchange="previewImage('system-logo', 'logo-preview')">
                <!-- Exibe a imagem existente, se houver -->
                <img id="logo-preview" src="<?= LOGO ?>" alt="Logo do Sistema"
                  style="max-width: 100px; margin-top: 10px; <?php echo LOGO ? '' : 'display: none;'; ?>">
              </div>
            </div>

          </div>

           <hr>

           <!-- Configurações do sistema-->
          <div class="row bg-info text-black">
            <div class="col-md-12">
              <h2 class="text-center">EAD <?= NOME_EAD ?></h2>
            </div>

            <div class="col-md-12 mb-3">
              <label for="system-name" class="form-label">Nome do Ead</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-server"></i></span>
                <input type="text" class="form-control" id="ead-name" value="<?php echo $config['nome_ead']; ?>"
                  required>
              </div>
            </div>
          </div>   



          <hr>

          <!--SEO E LINKS-->
          <div class="row bg-success text-black">
            <div class="col-md-12">
              <h2 class="text-center">SEO & LINKS</h2>
            </div>
            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Descrição do Sistema (SEO)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <textarea type="text" class="form-control" id="descriptions-site" required>
                                  <?php echo $config['descriptions']; ?>  </textarea>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Palavras Chave</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <textarea type="text" class="form-control" id="keywords-site" required>
                                  <?php echo $config['keywords']; ?>  </textarea>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Texto Politicas de Uso do Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <textarea type="text" class="form-control" id="politicas-uso" required>
                                  <?php echo $config['politicas_uso']; ?>  </textarea>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="shopify-url-senha" class="form-label">Link Sobre o Uso do Sistema</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shopping-cart"></i></span>
                <input type="text" class="form-control" id="link-sobre" value="<?php echo $config['link_sobre']; ?>"
                  required>
              </div>
            </div>
          </div>




          <div class="row mt-3">
            <div class="col-12">
              <button class="btn btn-success w-100 mb-3" type="button" onclick="saveSettings()">
                <i class="fas fa-save"></i> Salvar Configurações
              </button>

            </div>
          </div>
        </form>


      </div>
    </div>
  </div>