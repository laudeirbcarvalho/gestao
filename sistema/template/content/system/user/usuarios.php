<div id="user-list" class="user-list">

    <?php if ($_SESSION["permissao"] == "admin") { ?>
      <h3>Lista de Usuários</h3>
      <button class="btn btn-primary w-100 mb-3" id="btnCadastrar">Cadastrar Novo Usuário</button>
    <?php } ?>
    <div class="form-container">
      <!--Formulário de Cadastro de usuario-->
      <form id="formularioDeCadastro" enctype="multipart/form-data">
        <input type="hidden" id="userId" name="userId">
        <div class="form-group">
          <label for="nome">Nome</label>
          <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" class="form-control" id="senha" name="senha" required>
        </div>
        <div class="form-group">
          <label for="whatsapp">WhatsApp</label>
          <input type="text" class="form-control" id="whatsapp" name="whatsapp" required>
        </div>
        <div class="form-group">
          <label for="permissao">Permissão</label>
          <input type="radio" name="permissao" value="admin" id="admin"> Admin
          <input type="radio" name="permissao" value="user" id="user" checked> Operador
        </div>
        <div class="form-group">
          <label for="cargo">Função</label>
          <select class="form-control" name="cargo" id="cargo" required>
            <option value="" disabled selected>Selecione o Cargo</option>
            <option value="Gerente Comercial">Gerente Comercial</option>
            <option value="Gestor de Vendas">Gestor de Vendas</option>
            <option value="Auditor">Auditor</option>
            <option value="Administrador">Administrador</option>
          </select>
        </div>
        <div class="form-group">
          <label for="avatar">Avatar</label>
          <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        </div>
        <button type="submit" class="btn btn-success w-100 mb-3">Salvar</button>
      </form>
      <!-- Formulário de Edição Usuario -->
      <div id="edit-form-container" style="display: none;">
        <form id="edit-form" enctype="multipart/form-data">
          <input type="hidden" name="id" id="edit-id">
          <div class="form-group">
            <label for="edit-nome">Nome:</label>
            <input class="form-control" type="text" name="nome" id="edit-nome" required>
          </div>
          <div class="form-group">
            <label for="edit-email">Email:</label>
            <input class="form-control" type="email" name="email" id="edit-email" required>
          </div>
          <div class="form-group">
            <label for="edit-whatsapp">WhatsApp:</label>
            <input class="form-control" type="text" name="whatsapp" id="edit-whatsapp" required>
          </div>
          <div class="form-group">
            <label for="edit-permissao">Permissão:</label>
            <select class="form-control" name="permissao" id="edit-permissao">
              <option value="user">Operador</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit-cargo">Cargo:</label>
            <select class="form-control" name="cargo" id="edit-cargo">
              <option value="" disabled selected>Selecione o Cargo</option>
              <option value="Gerente Comercial">Gerente Comercial</option>
              <option value="Gestor de Vendas">Gestor de Vendas</option>
              <option value="Auditor">Auditor</option>
              <option value="Administrador">Administrador</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit-avatar">Avatar:</label>
            <input type="hidden" name="tem_avatar" id="edit-avatar">
            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
          </div>
          <button type="submit" class="btn btn-success w-100 mb-3">Salvar</button>
          <button type="button" id="cancel-edit" class="btn btn-secondary w-100 mb-3">Fechar</button>
        </form>
        <hr>
      </div>

    </div>
    <?php if ($_SESSION["permissao"] == "admin") { ?>
      <div id="listar-usuarios" class="listar-usuarios">
       
      </div>
      <div id="listar-usuario-atualizado"></div>
    <?php } ?>
  </div>