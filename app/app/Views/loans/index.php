<?php

$this->extend('layouts/main');
$this->section('title'); echo 'Empréstimos'; $this->endSection();
?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-arrow-left-right me-2" style="color:var(--info)"></i>Empréstimos
        </h1>
        <p class="page-subtitle">Acompanhe todos os ativos emprestados entre os estabelecimentos da rede.</p>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="get" class="d-flex gap-2 w-50">
        <input type="text" name="search" class="form-control-app shadow-sm" placeholder="Buscar ativo, origem, destino..." value="<?= esc($search ?? '') ?>">
        <select name="status" class="form-select-app shadow-sm" style="width: auto;">
            <option value="all" <?= ($status === 'all' || empty($status)) ? 'selected' : '' ?>>Todos</option>
            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Em Uso</option>
            <option value="late" <?= ($status === 'late') ? 'selected' : '' ?>>Em Atraso</option>
            <option value="returned" <?= ($status === 'returned') ? 'selected' : '' ?>>Devolvidos</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm px-3 shadow-sm"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card-app shadow-sm">
    <?php if (empty($loans)): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Nenhum empréstimo registrado ainda.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-app" id="loans-table">
        <thead>
            <tr>
                <th>Cód. Ativo</th>
                <th>Patrimônio</th>
                <th>De (Atendente)</th>
                <th>Para (Solicitante)</th>
                <th>Retirada</th>
                <th>Devolução Prevista</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $loan): ?>
            <tr>
                <td><code class="app-code"><?= esc($loan['asset_code']) ?></code></td>
                <td><?= esc($loan['asset_name']) ?></td>
                <td><?= esc($loan['lender_name']) ?></td>
                <td><span style="font-weight: 500; color: var(--text)"><?= esc($loan['requester_name']) ?></span></td>
                
                <td class="text-muted" style="font-size:.82rem">
                    <?= date('d/m/Y H:i', strtotime($loan['checked_out_at'])) ?>
                </td>
                
                <td class="text-muted" style="font-size:.82rem">
                    <?= date('d/m/Y H:i', strtotime($loan['due_date'])) ?>
                </td>
                
                <td>
                    <?php if ($loan['is_returned']): ?>
                        <span class="badge-app badge-active"><i class="bi bi-check-circle"></i>Devolvido</span>
                    <?php elseif ($loan['is_late']): ?>
                        <span class="badge-app badge-decommissioned"><i class="bi bi-exclamation-circle"></i>Em Atraso</span>
                    <?php else: ?>
                        <span class="badge-app badge-borrowed"><i class="bi bi-clock-history"></i>Em Uso</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (! $loan['is_returned']): ?>
                        <button class="btn btn-sm btn-outline-success btn-return-loan" data-id="<?= esc($loan['id']) ?>">
                            <i class="bi bi-box-arrow-in-down"></i> Devolver
                        </button>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const BASE = window.location.origin;

    $('.btn-return-loan').on('click', function () {
        if (!confirm('Deseja registrar a devolução deste patrimônio agora?')) {
            return;
        }

        const $btn = $(this);
        const id = $btn.data('id');
        $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: BASE + '/loans/return/' + id,
            method: 'POST',
            success(res) {
                alert(res.message);
                window.location.reload();
            },
            error(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao processar devolução.');
                $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Devolver');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
