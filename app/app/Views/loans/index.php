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

<div class="card-app">
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
            </tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $loan): ?>
            <?php 
                $isReturned = $loan['returned_at'] !== null;
                $isLate = ! $isReturned && strtotime($loan['due_date']) < time();
            ?>
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
                    <?php if ($isReturned): ?>
                        <span class="badge-app badge-active"><i class="bi bi-check-circle"></i>Devolvido</span>
                    <?php elseif ($isLate): ?>
                        <span class="badge-app badge-decommissioned"><i class="bi bi-exclamation-circle"></i>Em Atraso</span>
                    <?php else: ?>
                        <span class="badge-app badge-borrowed"><i class="bi bi-clock-history"></i>Em Uso</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
