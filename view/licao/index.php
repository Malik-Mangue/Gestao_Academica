<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/LicaoController.php';

$controller = new LicaoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_LICOES, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $_SESSION['flash'] = $controller->apagarLicao($codigo)
            ? ['type' => 'success', 'msg' => 'Horário removido com sucesso!']
            : ['type' => 'danger', 'msg' => 'Não foi possível eliminar o registo.'];
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar' || $acao === 'editar') {
        Sessao::exigirAcao(
            $acao === 'gravar' ? Sessao::ACAO_CRIAR : Sessao::ACAO_EDITAR,
            Sessao::RECURSO_LICOES,
            'index.php'
        );

        $codigo     = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $codModulo  = filter_input(INPUT_POST, 'codModulo', FILTER_VALIDATE_INT);
        $codFormador= filter_input(INPUT_POST, 'codFormador', FILTER_VALIDATE_INT);
        $codSala    = filter_input(INPUT_POST, 'codSala', FILTER_VALIDATE_INT);
        $codTurma   = filter_input(INPUT_POST, 'codTurma', FILTER_VALIDATE_INT);
        $data       = trim($_POST['data'] ?? '');
        $horaInicio = trim($_POST['hora_inicio'] ?? '');
        $horaFim    = trim($_POST['hora_fim'] ?? '');

        if (!$codModulo || !$codFormador || !$codSala || !$codTurma) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione módulo, formador, sala e turma.'];
        } elseif (!Validador::data($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique uma data válida.'];
        } elseif (!Validador::hora($horaInicio) || !Validador::hora($horaFim)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique as horas no formato HH:MM.'];
        } elseif ($horaFim <= $horaInicio) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A hora de fim deve ser posterior à hora de início.'];
        } elseif ($controller->existeConflito($codSala, $codFormador, $data, $horaInicio, $horaFim, $codigo)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Já existe uma lição para esta sala e formador nesse intervalo.'];
        } else {
            $modulo   = new Modulo($codModulo, null, null);
            $formador = new Formador($codFormador, null, null, null, null, null, null, null, null, null);
            $sala     = new Sala($codSala, null, null);
            $turma    = new Turma($codTurma, null, null, null);

            $sucesso = $acao === 'gravar'
                ? $controller->cadastrarLicao($modulo, $formador, $sala, $turma, $data, $horaInicio, $horaFim)
                : $controller->atualizarLicao($codigo, $modulo, $formador, $sala, $turma, $data, $horaInicio, $horaFim);

            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Horário cadastrado com sucesso!' : 'Horário atualizado com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar o horário.'];
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');
$licoes      = $controller->listarLicao($pesquisa);
$modulos     = $controller->listarModulos();
$formadores  = $controller->listarFormadores();
$salas       = $controller->listarSalas();
$turmas      = $controller->listarTurmas();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_LICOES);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_LICOES);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_LICOES);

$page_title = 'Gestão de Horários';
$active_menu = 'licao';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Horários</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Horários</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Horários</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Novo Horário</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Módulo, formador ou turma"
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
                            <th>Data</th>
                            <th>Hora Início</th>
                            <th>Hora Fim</th>
                            <th>Módulo</th>
                            <th>Formador</th>
                            <th>Sala</th>
                            <th>Turma</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($licoes)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 9 : 8 ?>" class="table-empty">
                                    Nenhum horário cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($licoes as $licao): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($licao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($licao->getData()) ?></td>
                                    <td><?= htmlspecialchars($licao->getHoraInicio()) ?></td>
                                    <td><?= htmlspecialchars($licao->getHoraFim()) ?></td>
                                    <td><?= htmlspecialchars($licao->getModulo()->getNome()) ?></td>
                                    <td><?= htmlspecialchars($licao->getFormador()->getNome()) ?></td>
                                    <td><?= htmlspecialchars($licao->getSala()->getDesignacao()) ?></td>
                                    <td><?= htmlspecialchars($licao->getTurma()->getNome()) ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $licao->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $licao->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $licao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Horário #<?= htmlspecialchars($licao->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($licao->getCodigo()) ?>">
                                                    <?php
                                                    $sufixo = 'editar-' . $licao->getCodigo();
                                                    require __DIR__ . '/_campos.php';
                                                    ?>
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
                                    <div id="modal-deletar-<?= $licao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($licao->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente este horário?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars($licao->getCodigo()) ?></p>
                                                        <p><strong>Módulo:</strong> <?= htmlspecialchars($licao->getModulo()->getNome()) ?></p>
                                                        <p><strong>Data:</strong> <?= htmlspecialchars($licao->getData()) ?></p>
                                                        <p><strong>Hora:</strong> <?= htmlspecialchars($licao->getHoraInicio()) ?> - <?= htmlspecialchars($licao->getHoraFim()) ?></p>
                                                        <p><strong>Turma:</strong> <?= htmlspecialchars($licao->getTurma()->getNome()) ?></p>
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
                    <h3>Cadastrar Novo Horário</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <?php
                        $sufixo = 'novo';
                        $licao  = null;
                        require __DIR__ . '/_campos.php';
                        ?>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Horário</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
