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

<div class="card-app">
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
                <th>SLA (Prazo Máximo)</th>
                <th>Membro desde</th>
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
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
