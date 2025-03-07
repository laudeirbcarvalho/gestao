<div id="adluxacademy" class="settings" style="display: none;">
  <h3 class="text-center"> EAD <?= NOME_EAD ?> </h3>
  <div class="container my-5">


    <ul class="list-group">
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-book mr-2"></i>
        <!-- Botão para abrir o popup -->      
        <button type="button" class="w-100 btn btn-primary" data-toggle="modal" data-target="#agendaModal">
          Abrir Agenda
        </button>

      </li>
      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-person-plus mr-2"></i>
        Cadastro de Aluno
      </li>

      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-person-plus mr-2"></i>
        Lista de Técnicos e Agendamentos
      </li>

      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-diagram-3 mr-2"></i>
        <a class="btn btn-primary w-100 listarosleads" href="#" id="listarleads"> CRM: Envio de e-mails e mensagens
          por whatsapp e
          notifcações push </a>
      </li>

      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-link mr-2"></i>
        Enviar Link de convites com pagamento via pix
      </li>

      <li class="list-group-item d-flex align-items-center">
        <i class="bi bi-link mr-2"></i>
        EAD - Cursos On-line com avaliação e diploma.
        -
        <a target="_blank" href="https://treinamentos.adlux.com.br/">Acessao o Site</a>
      </li>


    </ul>


  </div>

  <div class="container my-5 resultadoleads">
    Lista de Leads

  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="agendaModal" tabindex="-1" role="dialog" aria-labelledby="agendaModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agendaModalLabel">Agenda</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Aqui será carregado o conteúdo do agenda.php -->
        <div id="agendaContent">Carregando...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>