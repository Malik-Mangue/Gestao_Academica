<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Auditoria de logs: guarda por recurso (ver matriz de permissoes).
Sessao::exigirLeitura(Sessao::RECURSO_LOGS, '../dashboard/index.php');

require_once __DIR__ . '/../../controller/LogsController.php';

$controller = new LogController();

$filtro = trim($_GET['filtro'] ?? '');
$logs   = $controller->listarLogs($filtro !== '' ? $filtro : null);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Registos de Auditoria';
$active_menu = 'log';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Registos de Auditoria</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Registos</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Histórico de Ações</h2>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="filtro">Pesquisar registos</label>
                    <input type="text" id="filtro" name="filtro" class="form-control"
                           maxlength="100" placeholder="Utilizador, acao ou descricao"
                           value="<?= htmlspecialchars($filtro) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpar</a>
            </form>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Utilizador</th>
                            <th>Perfil</th>
                            <th>Ação</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="table-empty">Nenhum registo encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <?php $usuario = $log->getUsuario(); ?>
                                <tr>
                                    <td><?= htmlspecialchars($log->getData()) ?></td>
                                    <td><?= htmlspecialchars($usuario->getUsername()) ?></td>
                                    <td><?= htmlspecialchars($usuario->getPerfil() ? $usuario->getPerfil()->getNome() : '-') ?></td>
                                    <td><strong><?= htmlspecialchars($log->getAcao()) ?></strong></td>
                                    <td><?= htmlspecialchars($log->getDescricao()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>