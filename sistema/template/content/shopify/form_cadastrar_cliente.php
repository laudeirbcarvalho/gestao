<div id="formcadastrosalao">

    <div class="row">
      <div class="col-12">
        <h3>Cadastrar <?= NOME_SHOPIFY ?></h3>
        <form id="form-cadastrar-cliente" action="#" method="post">
          <div class="form-group">
            <input type="hidden" class="form-control" id="id_shopifyCad" name="id_shopify" readonly>
          </div>
          <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nomeclienteCad" name="nome" required>
          </div>
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="emailclienteCad" name="email" required>
          </div>
          <div class="form-group">
            <label>Status:</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="status" id="status_ativo" value="ativo" disabled>
              <label class="form-check-label" for="status_ativo">
                Ativo
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="status" id="status_inativo" value="inativo" checked>
              <label class="form-check-label" for="status_inativo">
                Inativo ( o cliente recebe um e-mail com instruções para cadastrar sua senha de acesso a
                loja. )
              </label>
            </div>
          </div>
          <div class="form-group">
            <input type="hidden" class="form-control" id="data_cadastroCad" name="data_cadastro" readonly>
          </div>
          <div class="form-group">
            <label for="endereco">Endereço:</label>
            <input type="text" class="form-control" id="enderecoCad" name="endereco">
          </div>
          <div class="form-group">
            <label for="numero">Número:</label>
            <input type="text" class="form-control" id="numeroCad" name="numero">
          </div>
          <div class="form-group">
            <label for="complemento">Complemento:</label>
            <input type="text" class="form-control" id="complementoCad" name="complemento">
          </div>
          <div class="form-group">
            <label for="bairro">Bairro:</label>
            <input type="text" class="form-control" id="bairroCad" name="bairro">
          </div>

          <div class="form-group">
            <label for="cidade">Cidade:</label>
            <input type="text" class="form-control" id="cidadeCad" name="cidade">
          </div>
          <div class="form-group">
            <label for="estado">Estado:</label>
            <input type="text" class="form-control" id="estadoCad" name="estado">
          </div>
          <div class="form-group">
            <label for="pais">País:</label>
            <input type="text" class="form-control" id="paisCad" name="pais">
          </div>
          <div class="form-group">
            <label for="cep">CEP:</label>
            <input type="text" class="form-control" id="cepCad" name="cep">
          </div>
          <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" class="form-control" id="telefoneCad" name="telefone">
          </div>
          <div class="form-group">
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" class="form-control" id="data_nascimentoCad" name="data_nascimento">
          </div>
          <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" class="form-control" id="cpfCad" name="cpf">
          </div>
          <div class="form-group">
            <label for="cnpj">CNPJ:</label>
            <input type="text" class="form-control" id="cnpjCad" name="cnpj">
          </div>
          <div class="form-group">
            <label for="empresa">EMPRESA:</label>
            <input type="text" class="form-control" id="empresaCad" name="empresa">
          </div>
          <div class="form-group">
            <label>Lead: <em>( como ficou sabendo )</em></label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="instagram">
              <label class="form-check-label" for="indicado">
                Instagram
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="facebook">
              <label class="form-check-label" for="indicado">
                Facebook
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="landingpage">
              <label class="form-check-label" for="indicado">
                Landingpage
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="landingpage">
              <label class="form-check-label" for="lojavirtual">
                Loja Virtual <em>( <?php echo SHOPIFY_URL_LOJA; ?> )</em>
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="google">
              <label class="form-check-label" for="indicado">
                Google
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="indicado" id="indicado" value="indicado">
              <label class="form-check-label" for="indicado">
                Indicação por outra pessoa.
              </label>
            </div>
          </div>
          <input type="hidden" name="central_id" id="central_id" value="<?php echo CENTRAL_ID; ?>">
          <button id="" type="submit" class="btn btn-primary">Cadastrar</button>
          <button id="cancelarcadastrarsalao" class="btn btn-secondary">Cancelar</button>

        </form>
        <hr>

      </div>
    </div>
  </div>