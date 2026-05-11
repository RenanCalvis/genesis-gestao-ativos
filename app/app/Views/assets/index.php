<?php
/**
 *
 * @var array<int, array{
 *   id:                      string,
 *   name:                    string,
 *   code:                    string,
 *   type:                    string,
 *   parent_establishment_id: string,
 *   entry_date:              string,
 *   decommissioned_at:       string|null,
 *   decommission_reason:     string|null,
 *   establishment_name:      string,
 *   establishment_type:      string,
 * }> $assets
 *
 * @var array<int, array{
 *   id:            string,
 *   name:          string,
 *   type:          string,
 *   max_loan_days: string|null,
 * }> $establishments
 */

use App\Enums\AssetType;

$this->extend('layouts/main');
$this->section('title'); echo 'Inventário de Patrimônios'; $this->endSection();
?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-boxes me-2" style="color:var(--accent)"></i>Inventário
        </h1>
        <p class="page-subtitle">Gerencie patrimônios e registre empréstimos entre estabelecimentos.</p>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="get" class="d-flex gap-2 w-50">
        <input type="text" name="search" class="form-control-app shadow-sm" placeholder="Buscar código ou nome..." value="<?= esc($search ?? '') ?>">
        <select name="status" class="form-select-app shadow-sm" style="width: auto;">
            <option value="active" <?= ($status === 'active' || empty($status)) ? 'selected' : '' ?>>Somente Ativos</option>
            <option value="decommissioned" <?= ($status === 'decommissioned') ? 'selected' : '' ?>>Somente Baixados</option>
            <option value="all" <?= ($status === 'all') ? 'selected' : '' ?>>Todos</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm px-3 shadow-sm"><i class="bi bi-search"></i></button>
    </form>
    
    <button class="btn-app-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createAssetModal">
        <i class="bi bi-plus-circle"></i> Novo Patrimônio
    </button>
</div>

<div class="card-app shadow-sm">

    <?php if (empty($assets)): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Nenhum patrimônio cadastrado ainda.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-app" id="assets-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Tipo do Item</th>
                <th>Unidade</th>
                <th>Tipo da Unidade</th>
                <th>Entrada</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($assets as $asset): ?>
            <?php $isDecommissioned = ! empty($asset['decommissioned_at']); ?>
            <tr>
                <td><code class="app-code"><?= esc($asset['code']) ?></code></td>
                <td><?= esc($asset['name']) ?></td>
                <td><?= AssetType::from($asset['type'])->badge() ?></td>
                <td><?= esc($asset['establishment_name']) ?></td>
                <td><?= \App\Enums\EstablishmentType::tryFrom($asset['establishment_type'])?->badge() ?? esc($asset['establishment_type']) ?></td>
                <td class="text-muted" style="font-size:.82rem">
                    <?= date('d/m/Y', strtotime($asset['entry_date'])) ?>
                </td>
                <td>
                    <?php if ($asset['decommissioned_at'] !== null): ?>
                        <span class="badge-app badge-decommissioned"><i class="bi bi-x-circle"></i>Baixado</span>
                    <?php else: ?>
                        <span class="badge-app badge-active"><i class="bi bi-check-circle"></i>Ativo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (! $isDecommissioned): ?>
                    <div class="d-flex gap-2">
                        <button class="btn-app-primary btn-open-loan"
                            id="btn-loan-<?= esc($asset['id']) ?>"
                            data-asset-id="<?= esc($asset['id']) ?>"
                            data-asset-name="<?= esc($asset['name']) ?>"
                            data-lender-id="<?= esc($asset['parent_establishment_id']) ?>"
                            data-lender-name="<?= esc($asset['establishment_name']) ?>">
                            <i class="bi bi-arrow-left-right"></i>Emprestar
                        </button>
                        <button class="btn btn-sm btn-outline-secondary btn-open-edit"
                            data-id="<?= esc($asset['id']) ?>"
                            data-name="<?= esc($asset['name']) ?>"
                            data-code="<?= esc($asset['code']) ?>"
                            data-type="<?= esc($asset['type']) ?>"
                            data-parent="<?= esc($asset['parent_establishment_id']) ?>">
                            <i class="bi bi-pencil"></i>Editar
                        </button>
                        <button class="btn-app-danger btn-open-decomm"
                            id="btn-decomm-<?= esc($asset['id']) ?>"
                            data-asset-id="<?= esc($asset['id']) ?>"
                            data-asset-name="<?= esc($asset['name']) ?>">
                            <i class="bi bi-archive"></i>Baixar
                        </button>
                    </div>
                    <?php endif; ?>
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

 <!-- Modal Emprestimo -->
<div class="modal fade modal-app" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loanModalLabel">
            <i class="bi bi-arrow-left-right me-2" style="color:var(--accent)"></i>Registrar Empréstimo
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="loan-feedback" class="alert-app" role="alert"></div>
        <form id="loan-form" novalidate>
            <input type="hidden" id="loan-asset-id" name="asset_id">
            <input type="hidden" id="loan-lender-id" name="lender_establishment_id">

            <div class="mb-3">
                <label class="form-label-app">Patrimônio</label>
                <input type="text" id="loan-asset-name" class="form-control-app" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Unidade Atendente (Emprestadora)</label>
                <input type="text" id="loan-lender-name" class="form-control-app" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label-app" for="loan-requester">Unidade Solicitante</label>
                <select id="loan-requester" name="requester_establishment_id" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <?php foreach ($establishments as $est): ?>
                    <option value="<?= esc($est['id']) ?>" data-type="<?= esc($est['type']) ?>">
                        <?= esc($est['name']) ?> — <?= \App\Enums\EstablishmentType::tryFrom($est['type'])?->label() ?? esc($est['type']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label-app" for="loan-checked-out">Data de Retirada</label>
                <input type="datetime-local" id="loan-checked-out" name="checked_out_at" class="form-control-app" required>
            </div>
            <div class="mb-2">
                <label class="form-label-app" for="loan-due-date">Data Prevista de Devolução</label>
                <input type="datetime-local" id="loan-due-date" name="due_date" class="form-control-app" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-loan" class="btn-app-primary px-4">
            <span id="btn-loan-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            Confirmar Empréstimo
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Baixa  -->
<div class="modal fade modal-app" id="decommModal" tabindex="-1" aria-labelledby="decommModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="decommModalLabel">
            <i class="bi bi-archive me-2" style="color:var(--danger)"></i>Baixar Patrimônio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="decomm-feedback" class="alert-app" role="alert"></div>
        <form id="decomm-form" novalidate>
            <input type="hidden" id="decomm-asset-id">
            <div class="mb-3">
                <label class="form-label-app">Patrimônio</label>
                <input type="text" id="decomm-asset-name" class="form-control-app" readonly>
            </div>
            <div class="mb-2">
                <label class="form-label-app" for="decomm-reason">
                    Motivo da Baixa <span style="color:var(--danger)">*</span>
                </label>
                <textarea id="decomm-reason" name="decommission_reason"
                    class="form-control-app"
                    placeholder="Descreva o motivo (mínimo 10 caracteres)…"
                    required></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-decomm" class="btn btn-sm btn-danger fw-semibold px-4">
            <span id="btn-decomm-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            Confirmar Baixa
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal criar patrimonio -->
<div class="modal fade modal-app" id="createAssetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>Novo Patrimônio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="create-feedback" class="alert-app" role="alert"></div>
        <form id="create-asset-form">
            <div class="mb-3">
                <label class="form-label-app">Nome do Item</label>
                <input type="text" id="create-name" name="name" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Código / Tag</label>
                <input type="text" id="create-code" name="code" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Situação (Tipo)</label>
                <select id="create-type" name="type" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <option value="OWNED">Próprio</option>
                    <option value="RENTED">Alugado</option>
                    <option value="BORROWED">Emprestado</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Unidade de Origem</label>
                <select id="create-parent" name="parent_establishment_id" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <?php foreach ($establishments as $est): ?>
                    <option value="<?= esc($est['id']) ?>">
                        <?= esc($est['name']) ?> — <?= \App\Enums\EstablishmentType::tryFrom($est['type'])?->label() ?? esc($est['type']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-create" class="btn-app-primary px-4">
            <span id="btn-create-spinner" class="spinner-border spinner-border-sm d-none"></span>
            Cadastrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal editar patrimonio -->
<div class="modal fade modal-app" id="editAssetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-pencil me-2" style="color:var(--accent)"></i>Editar Patrimônio
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="edit-feedback" class="alert-app" role="alert"></div>
        <form id="edit-asset-form">
            <input type="hidden" id="edit-id">
            <div class="mb-3">
                <label class="form-label-app">Nome do Item</label>
                <input type="text" id="edit-name" name="name" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Código / Tag</label>
                <input type="text" id="edit-code" name="code" class="form-control-app" required>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Situação (Tipo)</label>
                <select id="edit-type" name="type" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <option value="OWNED">Próprio</option>
                    <option value="RENTED">Alugado</option>
                    <option value="BORROWED">Emprestado</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-app">Unidade de Origem</label>
                <select id="edit-parent" name="parent_establishment_id" class="form-select-app" required>
                    <option value="">— Selecione —</option>
                    <?php foreach ($establishments as $est): ?>
                    <option value="<?= esc($est['id']) ?>">
                        <?= esc($est['name']) ?> — <?= \App\Enums\EstablishmentType::tryFrom($est['type'])?->label() ?? esc($est['type']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirm-edit" class="btn-app-primary px-4">
            <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none"></span>
            Salvar Alterações
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

    function nowDatetimeLocal() {
        const d = new Date(); d.setSeconds(0, 0);
        return d.toISOString().slice(0, 16);
    }

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

    function formatDateBR(isoStr) {
        if (!isoStr) return '—';
        return new Date(isoStr).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
    }

    const loanModal = new bootstrap.Modal('#loanModal');

    $(document).on('click', '.btn-open-loan', function () {
        const $b = $(this);
        $('#loan-asset-id').val($b.data('asset-id'));
        $('#loan-lender-id').val($b.data('lender-id'));
        $('#loan-asset-name').val($b.data('asset-name'));
        $('#loan-lender-name').val($b.data('lender-name'));
        $('#loan-requester').val('');
        $('#loan-checked-out').val(nowDatetimeLocal());
        $('#loan-due-date').val('');
        $('#loan-feedback').hide().text('');
        loanModal.show();
    });

    $('#btn-confirm-loan').on('click', function () {
        const $btn      = $(this);
        const $spinner  = $('#btn-loan-spinner');
        const $feedback = $('#loan-feedback');

        if (! $('#loan-requester').val()) {
            showFeedback($feedback, 'error', 'Selecione a unidade solicitante.');
            return;
        }

        if (! $('#loan-checked-out').val() || ! $('#loan-due-date').val()) {
            showFeedback($feedback, 'error', 'As datas de retirada e devolução são obrigatórias.');
            return;
        }

        const checkedOut = $('#loan-checked-out').val().replace('T', ' ') + ':00';
        const dueDate    = $('#loan-due-date').val().replace('T', ' ') + ':00';

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/loans',
            method: 'POST',
            data: {
                asset_id:                   $('#loan-asset-id').val(),
                lender_establishment_id:    $('#loan-lender-id').val(),
                requester_establishment_id: $('#loan-requester').val(),
                checked_out_at:             checkedOut,
                due_date:                   dueDate,
            },
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => window.location.reload(), 1000);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao registrar empréstimo.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); },
        });
    });

    /*  Modal Baixa  */
    const decommModal = new bootstrap.Modal('#decommModal');

    $(document).on('click', '.btn-open-decomm', function () {
        const $b = $(this);
        $('#decomm-asset-id').val($b.data('asset-id'));
        $('#decomm-asset-name').val($b.data('asset-name'));
        $('#decomm-reason').val('');
        $('#decomm-feedback').hide().text('');
        decommModal.show();
    });

    $('#btn-confirm-decomm').on('click', function () {
        const $btn      = $(this);
        const $spinner  = $('#btn-decomm-spinner');
        const $feedback = $('#decomm-feedback');
        const id        = $('#decomm-asset-id').val();

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/assets/decommission/' + id,
            method: 'POST',
            data: { decommission_reason: $('#decomm-reason').val() },
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => {
                    const $row = $('#btn-decomm-' + id).closest('tr');
                    $row.find('td:nth-child(7)').html(
                        '<span class="badge-app badge-decommissioned"><i class="bi bi-x-circle"></i>Baixado</span>'
                    );
                    $row.find('td:last-child').empty();
                    decommModal.hide();
                }, 1100);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao baixar patrimônio.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); },
        });
    });

    const createModal = new bootstrap.Modal('#createAssetModal');

    $('#btn-confirm-create').on('click', function () {
        const $btn      = $(this);
        const $spinner  = $('#btn-create-spinner');
        const $feedback = $('#create-feedback');

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/assets',
            method: 'POST',
            data: $('#create-asset-form').serialize(),
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => window.location.reload(), 1000);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao cadastrar patrimônio.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); },
        });
    });

    const editModal = new bootstrap.Modal('#editAssetModal');

    $(document).on('click', '.btn-open-edit', function () {
        const $b = $(this);
        $('#edit-id').val($b.data('id'));
        $('#edit-name').val($b.data('name'));
        $('#edit-code').val($b.data('code'));
        $('#edit-type').val($b.data('type'));
        $('#edit-parent').val($b.data('parent'));
        $('#edit-feedback').hide().text('');
        editModal.show();
    });

    $('#btn-confirm-edit').on('click', function () {
        const $btn      = $(this);
        const $spinner  = $('#btn-edit-spinner');
        const $feedback = $('#edit-feedback');
        const id        = $('#edit-id').val();

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $feedback.hide();

        $.ajax({
            url: BASE + '/assets/update/' + id,
            method: 'POST',
            data: $('#edit-asset-form').serialize(),
            success(res) {
                showFeedback($feedback, 'success', res.message);
                setTimeout(() => window.location.reload(), 1000);
            },
            error(xhr) {
                const res = xhr.responseJSON ?? {};
                showFeedback($feedback, 'error', res.message || 'Erro ao editar patrimônio.', res.errors);
            },
            complete() { $btn.prop('disabled', false); $spinner.addClass('d-none'); },
        });
    });
});
</script>
<?= $this->endSection() ?>
