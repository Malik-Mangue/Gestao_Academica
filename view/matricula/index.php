<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/MatriculaController.php';
require_once __DIR__ . '/../../controller/FormandoController.php';
require_once __DIR__ . '/../../controller/QualificacaoController.php';

$controller     = new MatriculaController();
$formandoCtrl   = new FormandoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $_SESSION['flash'] = $controller->apagarMatricula($codigo)
            ? ['type' => 'success', 'msg' => 'Matrícula removida com sucesso!']
            : ['type' => 'danger', 'msg' => 'Não foi possível eliminar a matrícula.'];
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar' || $acao === 'editar') {
        Sessao::exigirAcao(
            $acao === 'gravar' ? Sessao::ACAO_CRIAR : Sessao::ACAO_EDITAR,
            'index.php'
        );

        $codigo      = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $codFormando = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
        $codQuali    = filter_input(INPUT_POST, 'codQuali', FILTER_VALIDATE_INT);
        $data        = trim($_POST['data'] ?? '');

        if (Dao/TurmaDao.phpcodFormando) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o formando.'];
        } elseif (Dao/TurmaDao.phpcodQuali) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione a qualificação.'];
        } elseif (!Validador::data($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique uma data de matrícula válida.'];
        } else {
            $formando     = new Formando($codFormando, null, null, null, null, null);
            $qualificacao = new Qualificacao($codQuali, null, null);
            $nivel        = new Nivel(null, null);

            $sucesso = $acao === 'gravar'
                ? $controller->cadastrarMatricula($formando, $qualificacao, $nivel, $data)
                : $controller->atualizarMatricula($codigo, $formando, $qualificacao, $nivel, $data);

            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Matrícula registada com sucesso!' : 'Matrícula atualizada com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar a matrícula.'];
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa       = trim($_GET['pesquisa'] ?? '');
$matriculas     = $controller->listarMatricula($pesquisa);
$formandos      = $formandoCtrl->listar();
$qualificacoes  = (new QualificacaoController())->comboQualificacao();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::pode(Sessao::ACAO_CRIAR);
$pode_editar  = Sessao::pode(Sessao::ACAO_EDITAR);
$pode_remover = Sessao::pode(Sessao::ACAO_REMOVER);

$page_title = 'Gestão de Matrículas';
$active_menu = 'matricula';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Matrículas</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Matrículas</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Matrículas</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Nova Matrícula</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Nome do formando"
                           value="<?= htmlspecialchars($pesquisa) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpar</a>
            </form>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Formando</th>
                            <th>Qualificação</th>
                            <th>Nível</th>
                            <th>Data</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($matriculas)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 6 : 5 ?>" class="table-empty">
                                    Nenhuma matrícula registada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($matriculas as $matricula): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($matricula->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($matricula->getFormando()->getNome() . ' ' . $matricula->getFormando()->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($matricula->getQualificacao()->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars($matricula->getNivel() ? $matricula->getNivel()->getNome() : '-') ?></td>
                                    <td><?= htmlspecialchars($matricula->getDataMatricula()) ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $matricula->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $matricula->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $matricula->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Matrícula #<?= htmlspecialchars($matricula->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($matricula->getCodigo()) ?>">
                                                    <div class="form-group">
                                                        <label for="e-f-<?= $matricula->getCodigo() ?>">Formando <span class="required">*</span></label>
                                                        <select id="e-f-<?= $matricula->getCodigo() ?>" name="codFormando" class="form-control" required>
                                                            <?php foreach ($formandos as $f): ?>
                                                                <option value="<?= (int) $f->getCodigo() ?>"
                                                                    <?= (int) $matricula->getFormando()->getCodigo() === (int) $f->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($f->getNome() . ' ' . $f->getApelido()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-q-<?= $matricula->getCodigo() ?>">Qualificação <span class="required">*</span></label>
                                                        <select id="e-q-<?= $matricula->getCodigo() ?>" name="codQuali" class="form-control" required>
                                                            <?php foreach ($qualificacoes as $q): ?>
                                                                <option value="<?= (int) $q->getCodigo() ?>"
                                                                    <?= (int) $matricula->getQualificacao()->getCodigo() === (int) $q->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($q->getTitulo()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-d-<?= $matricula->getCodigo() ?>">Data <span class="required">*</span></label>
                                                        <input type="date" id="e-d-<?= $matricula->getCodigo() ?>" name="data" class="form-control"
                                                               value="<?= htmlspecialchars($matricula->getDataMatricula()) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pode_remover): ?>
                                    <div id="modal-deletar-<?= $matricula->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($matricula->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente esta matrícula?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars($matricula->getCodigo()) ?></p>
                                                        <p><strong>Formando:</strong> <?= htmlspecialchars($matricula->getFormando()->getNome() . ' ' . $matricula->getFormando()->getApelido()) ?></p>
                                                        <p><strong>Data:</strong> <?= htmlspecialchars($matricula->getDataMatricula()) ?></p>
                                                    </div>
                                                    <p class="confirm-warning">
                                                        Atenção: esta ação não poderá ser desfeita.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="deletar" class="btn btn-delete">Sim, Deletar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($pode_criar): ?>
        <div id="modal-novo" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Registar Nova Matrícula</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="n-f">Formando <span class="required">*</span></label>
                            <select id="n-f" name="codFormando" class="form-control" required>
                                <?php foreach ($formandos as $f): ?>
                                    <option value="<?= (int) $f->getCodigo() ?>">
                                        <?= htmlspecialchars($f->getNome() . ' ' . $f->getApelido()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-q">Qualificação <span class="required">*</span></label>
                            <select id="n-q" name="codQuali" class="form-control" required>
                                <?php foreach ($qualificacoes as $q): ?>
                                    <option value="<?= (int) $q->getCodigo() ?>">
                                        <?= htmlspecialchars($q->getTitulo()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-d">Data <span class="required">*</span></label>
                            <input type="date" id="n-d" name="data" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
