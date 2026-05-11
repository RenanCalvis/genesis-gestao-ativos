<?php

$this->extend('layouts/main');
$this->section('title'); echo 'Estabelecimentos da Rede'; $this->endSection();
?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-buildings me-2" style="color:var(--success)"></i>Estabelecimentos da Rede
        </h1>
        <p class="page-subtitle">Visualize os hospitais, clínicas e laboratórios conveniados.</p>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="get" class="d-flex gap-2 w-50">
        <input type="text" name="search" class="form-control-app shadow-sm" placeholder="Buscar por nome ou CNPJ..." value="<?= esc($search ?? '') ?>">
        <button type="submit" class="btn btn-secondary btn-sm px-3 shadow-sm"><i class="bi bi-search"></i></button>
    </form>
    
    <button class="btn-app-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createEstModal">
        <i class="bi bi-plus-circle"></i> Nova Unidade
    </button>
</div>

<div class="card-app shadow-sm">

    <?php if (empty($establishments)): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Nenhuma unidade cadastrada ainda.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-app" id="establishments-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Tipo</th>
                <th>Prazo Máximo</th>
                <th>Membro desde</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($establishments as $est): ?>
            <tr>
                <td style="font-weight: 500; color: var(--text)"><?= esc($est['name']) ?></td>
                
                <td>
                    <!-- Máscara de CNPJ simples -->
                    <code class="app-code">
                        <?= preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", esc($est['cnpj'])) ?>
                    </code>
                </td>
                
                <td><?= \App\Enums\EstablishmentType::tryFrom($est['type'])?->badge() ?? esc($est['type']) ?></td>
                
                <td>
                    <?php if ($est['max_loan_days'] !== null): ?>
                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i><?= esc($est['max_loan_days']) ?> dias</span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>

                <td class="text-muted" style="font-size:.82rem">
                    <?= date('d/m/Y', strtotime($est['created_at'])) ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary btn-open-edit"
                        data-id="<?= esc($est['id']) ?>"
                        data-name="<?= esc($est['name']) ?>"
                        data-cnpj="<?= esc($est['cnpj']) ?>"
                        data-type="<?= esc($est['type']) ?>"
                        data-days="<?= esc($est['max_loan_days'] ?? '') ?>">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="mt-4 mb-2 pe-4 d-flex justify-content-end">
        <?= $pager->links() ?>
    </div>
    
    </div>
    <?php endif; ?>
</div>

<!-- Modal Criar -->
<div class="modal fade modal-app" id="createEstModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>Nova Unidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="create-feedback" class="alert-app" role="alert"></div>
        <form id="create-est-form">
            <div class="mb-3">
                <label class="form-label-app">Nome do Estabelecimento</label>
                <input type="text" name="name" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">CNPJ</label>
                <input type="text" name="cnpj" class="form-control-app" placeholder="00.000.000/0000-00" maxlength="18" inputmode="numeric" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Tipo</label>
                <select name="type" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <option value="CLINICA">Clínica</option>
                    <option value="LABORATORIO">Laboratório</option>
                    <option value="AMBULATORIO">Ambulatório</option>
                    <option value="HOSPITAL">Hospital</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Prazo Max. de Empréstimo (Dias)</label>
                <input type="number" name="max_loan_days" class="form-control-app" placeholder="Opcional">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-create" class="btn-app-primary px-4">
            <span class="spinner-border spinner-border-sm d-none"></span> Cadastrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade modal-app" id="editEstModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:var(--accent)"></i>Editar Unidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="edit-feedback" class="alert-app" role="alert"></div>
        <form id="edit-est-form">
            <input type="hidden" id="edit-id">
            <div class="mb-3">
                <label class="form-label-app">Nome do Estabelecimento</label>
                <input type="text" id="edit-name" name="name" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">CNPJ</label>
                <input type="text" id="edit-cnpj" name="cnpj" class="form-control-app" maxlength="18" inputmode="numeric" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Tipo</label>
                <select id="edit-type" name="type" class="form-select-app" required>
                    <option value="CLINICA">Clínica</option>
                    <option value="LABORATORIO">Laboratório</option>
                    <option value="AMBULATORIO">Ambulatório</option>
                    <option value="HOSPITAL">Hospital</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Prazo Max. de Empréstimo (Dias)</label>
                <input type="number" id="edit-days" name="max_loan_days" class="form-control-app">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-edit" class="btn-app-primary px-4">
            <span class="spinner-border spinner-border-sm d-none"></span> Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const BASE = window.location.origin;

    function showFeedback($el, type, msg, errors = null) {
        let html = msg;
        if (errors && Object.keys(errors).length > 0) {
            html += '<ul class="mb-0 mt-1" style="font-size: 0.85rem">';
            for (let key in errors) {
                html += `<li>${errors[key]}</li>`;
            }
            html += '</ul>';
        }
        $el.removeClass('alert-success alert-danger')
           .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
           .html(html).show();
    }

    // Máscara CNPJ
    $('input[name="cnpj"]').on('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').substring(0, 14);
        let x = val.match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
        e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '/' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    // Função utilitária para pegar os dados limpos
    function getCleanFormData($form) {
        const dataArray = $form.serializeArray();
        const dataObj = {};
        $(dataArray).each(function(i, field){
            if (field.name === 'cnpj') {
                dataObj[field.name] = field.value.replace(/\D/g, ''); // Envia apenas números
            } else {
                dataObj[field.name] = field.value;
            }
        });
        return dataObj;
    }

    // Criar
    $('#btn-confirm-create').on('click', function () {
        const $btn = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $feedback = $('#create-feedback');

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/establishments',
            method: 'POST',
            data: getCleanFormData($('#create-est-form')),
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => window.location.reload(), 1000);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao cadastrar.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); }
        });
    });

    // Editar
    const editModal = new bootstrap.Modal('#editEstModal');

    $(document).on('click', '.btn-open-edit', function () {
        const $b = $(this);
        $('#edit-id').val($b.data('id'));
        $('#edit-name').val($b.data('name'));
        $('#edit-cnpj').val($b.data('cnpj'));
        $('#edit-type').val($b.data('type'));
        $('#edit-days').val($b.data('days'));
        $('#edit-feedback').hide();
        editModal.show();
    });

    $('#btn-confirm-edit').on('click', function () {
        const $btn = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $feedback = $('#edit-feedback');
        const id = $('#edit-id').val();

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/establishments/update/' + id,
            method: 'POST',
            data: getCleanFormData($('#edit-est-form')),
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => window.location.reload(), 1000);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao atualizar.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); }
        });
    });
});
</script>
<?= $this->endSection() ?>
