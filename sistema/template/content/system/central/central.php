<div id="central" class="settings" style="display: none;">
    <div class="card">
      <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="fas fa-cog"></i> Central Administrativa</h5>
      </div>

      <!-- Link para abrir o collapse -->
      <div class="d-flex justify-content-end">
        <a href="#explicacao-collapse" data-bs-toggle="collapse" role="button" aria-expanded="false"
          aria-controls="explicacao-collapse">
          <i class="fas fa-info-circle"></i> Como funciona a Central?
        </a>
      </div>

      <!-- Collapse com a explicação -->
      <div class="collapse" id="explicacao-collapse">
        <div class="card card-body mt-3">
          <h5><strong>Explicação sobre a Central:</strong></h5>
          <p>A Central Administrativa é um sistema que organiza e gerencia diversos painéis, cada um com seu
            conjunto de configurações e informações. Cada central possui um <strong>ID único</strong>,
            chamado de <strong>central_id</strong>, que é utilizado para separar e identificar as
            configurações de diferentes centrais dentro do sistema. Esse ID assegura que cada central tenha
            acesso exclusivo ao seu painel, garantindo que as operações realizadas em uma central não
            interfiram nas de outra.</p>
          <p>O <strong>central_id</strong> é gerado aleatoriamente como um número de 6 dígitos e deve ser
            único, o que significa que não pode haver dois painéis com o mesmo número de identificação.
            Quando você cadastrar uma nova central, um <strong>central_id</strong> será atribuído
            automaticamente, e você poderá gerenciar as configurações dessa central com total autonomia.</p>
          <p>Esse sistema garante maior segurança e organização, permitindo que cada central tenha acesso a
            suas próprias configurações, senhas e dados sem risco de interferência de outras centrais.</p>
        </div>
      </div>



      <div class="card-body">
        <!-- Formulário para cadastro da Central-->
        <form id="central-form" class="needs-validation" novalidate>


          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="central-name" class="form-label">Nome da Central</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-building"></i></span>
                <input type="text" class="form-control" id="central-name" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="central-whatsapp" class="form-label">Whatsapp</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                <input type="text" class="form-control" id="central-whatsapp" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="responsavel-name" class="form-label">Responsável</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="responsavel-name" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="central-email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="central-email" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="central-senha" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="password" class="form-control" id="central-senha" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="central-dominio" class="form-label">Dominio</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-link"></i></span>
                <input type="text" class="form-control" id="central-dominio" placeholder="http://" required>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <button class="btn btn-success w-100 mb-3" type="button" onclick="saveCentral()">
                <i class="fas fa-save"></i> Salvar Central
              </button>
            </div>
          </div>
        </form>

        <!-- Tabela para listar as centrais -->
        <div class="mt-4">
          <h5>Centrais Cadastradas</h5>
          <div id="central-list-container" class="mt-4">
            <!-- A tabela será inserida dinamicamente aqui -->
          </div>

        </div>
      </div>
    </div>
  </div>