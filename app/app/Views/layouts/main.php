<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> — Genesis</title>

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dependências globais -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Design System global -->
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">

    <!-- CSS específico da página (opcional) -->
    <?= $this->renderSection('styles') ?>
</head>
<body>

<div class="app-container">
    <!-- ── Sidebar ── -->
    <aside class="app-sidebar" id="mainSidebar">
        <a href="<?= base_url('/') ?>" class="sidebar-brand">
            <span>G</span> <div class="brand-text">enesis</div>
        </a>

        <nav class="sidebar-nav">
            <?php $uri = uri_string(); ?>
            <a href="<?= base_url('assets') ?>" class="sidebar-link <?= ($uri === 'assets' || $uri === '') ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i>
                <span class="link-text">Patrimônios</span>
            </a>
            
            <a href="<?= base_url('loans') ?>" class="sidebar-link <?= ($uri === 'loans') ? 'active' : '' ?>">
                <i class="bi bi-arrow-left-right"></i>
                <span class="link-text">Empréstimos</span>
            </a>

            <a href="<?= base_url('establishments') ?>" class="sidebar-link <?= ($uri === 'establishments') ? 'active' : '' ?>">
                <i class="bi bi-buildings"></i>
                <span class="link-text">Estabelecimentos</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <button class="sidebar-toggle" id="btnToggleSidebar">
                <i class="bi bi-chevron-double-left"></i>
                <span class="toggle-text">Minimizar</span>
            </button>
        </div>
    </aside>

    <!-- ── Conteúdo Principal ── -->
    <div class="app-content">
        <header class="top-header">
            <div class="header-title">Sistema de Gestão de Ativos</div>
            <div class="user-profile text-muted" style="font-size: .85rem;">
                <i class="bi bi-person-circle me-2"></i>Admin
            </div>
        </header>

        <main class="page-wrapper">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<!-- ── Scripts globais ── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    // Gerencia estado da Sidebar com LocalStorage
    document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById('mainSidebar');
        const toggleBtn = document.getElementById('btnToggleSidebar');

        // Restaura estado preferido do usuário
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        });
    });
</script>

<!-- JS específico da página (opcional) -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
