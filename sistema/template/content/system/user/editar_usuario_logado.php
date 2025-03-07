<div id="edit-user" class="settings" style="display: none;">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user-edit"></i> Editar Dados do Usuário Logado</h5>
      </div>
      <div class="card-body">
        <form id="edit-user-form" class="needs-validation" novalidate>
          <input type="hidden" id="user-id" value="<?php echo $_SESSION['usuario_id']; ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="user-name" class="form-label">Nome Completo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="user-name" value="<?php echo $_SESSION['nome']; ?>"
                  required>
              </div>
              <div class="invalid-feedback">
                Por favor insira seu nome completo.
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="user-email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="user-email" value="<?php echo $_SESSION['email']; ?>"
                  required>
              </div>
              <div class="invalid-feedback">
                Por favor, insira um email válido.
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="user-password" class="form-label">Nova Senha</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="user-password">
              </div>
              <small class="text-muted">Deixe em branco para manter a senha atual</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="user-password-confirm" class="form-label">Confirmar Nova Senha</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="user-password-confirm">
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <button class="btn btn-primary w-100 mb-3" type="button" onclick="saveUserData()">
                <i class="fas fa-save"></i> Salvar Alterações
              </button>
              <button class="btn btn-secondary w-100 mb-3" type="button" onclick="showDashboard()">
                <i class="fas fa-times"></i> Cancelar
              </button>
            </div>
          </div>
        </form>

        <div class="mt-3">
          <div id="edit-message" class="alert" style="display: none;"></div>
        </div>
      </div>
    </div>
  </div>