<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/TurmaController.php';

$controller = new TurmaController();
$codigo = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT);

$turma = $controller->buscarTurma($codigo);
if ($turma === null) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Turma não encontrada.'];
    header('Location: index.php');
    exit;
}

$formandos = $controller->listarFormandosPorTurma((int) $turma->getCodigo());
$totalFormandos = count($formandos);

$diretorTurmaModel = $turma->getDiretorTurma();
$nomeDiretor = ($diretorTurmaModel !== null && $diretorTurmaModel->getFormador() !== null)
    ? $diretorTurmaModel->getFormador()->getNome() : '-';

$qn = $turma->getQualiNivel();
$tituloQuali = ($qn !== null && $qn->getQualificacao() !== null) ? $qn->getQualificacao()->getTitulo() : '-';
$nomeNivel = ($qn !== null && $qn->getNivel() !== null) ? $qn->getNivel()->getNome() : '-';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Detalhes da Turma';
$active_menu = 'turma';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Turma #<?= htmlspecialchars((string) $turma->getCodigo()) ?> — <?= htmlspecialchars($turma->getNome()) ?></h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <a href="index.php">Turmas</a>
            <span class="divider">/</span>
            <span>Detalhes</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Detalhes da Turma</h2>
            <a href="index.php" class="btn btn-secondary">Voltar às Turmas</a>
        </div>
        <div class="card-body">
            <div class="confirm-detail-box">
                <p>
                    <strong>Qualificação / Nível:</strong>
                    <?= htmlspecialchars((string) $tituloQuali) ?> — <?= htmlspecialchars((string) $nomeNivel) ?>
                </p>
                <p><strong>Diretor de Turma:</strong> <?= htmlspecialchars((string) $nomeDiretor) ?></p>
                <p><strong>Ano Lectivo:</strong> <?= htmlspecialchars((string) $turma->getAnoIngresso()) ?></p>
                <p><strong>Turno:</strong> <?= htmlspecialchars($turma->getTurno()) ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Estudantes (<?= $totalFormandos ?>)</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Número</th>
                            <th>Nome</th>
                            <th>Apelido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($formandos)): ?>
                            <tr>
                                <td colspan="3" class="table-empty">
                                    Nenhum estudante associado a esta turma no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($formandos as $i => $formando): ?>
                                <tr>
                                    <td><strong><?= $i + 1 ?></strong></td>
                                    <td><?= htmlspecialchars((string) $formando['nome_formando']) ?></td>
                                    <td><?= htmlspecialchars((string) $formando['apelido_formando']) ?></td>
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
