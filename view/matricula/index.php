<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/MatriculaController.php';
require_once __DIR__ . '/../../controller/FormandoController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../controller/CampoController.php';
require_once __DIR__ . '/../../controller/TurmaController.php';

$controller     = new MatriculaController();
$formandoCtrl   = new FormandoController();
$turmaCtrl      = new TurmaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_MATRICULAS, 'index.php');
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
            Sessao::RECURSO_MATRICULAS,
            'index.php'
        );

        $codigo       = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $codFormando  = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
        $idQualiNivel = filter_input(INPUT_POST, 'idQualiNivel', FILTER_VALIDATE_INT);
        $codTurma     = filter_input(INPUT_POST, 'codTurma', FILTER_VALIDATE_INT) ?: null;
        $data         = trim($_POST['data'] ?? '');

        $qualiNivel = $idQualiNivel
            ? (new Quali_NivelController())->buscar($idQualiNivel)
            : null;

        if (!$codFormando) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o formando.'];
        } elseif ($qualiNivel === null) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione a qualificação e o nível.'];
        } elseif (!$controller->data($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique uma data de matrícula válida.'];
        } else {
            $formando     = new Formando($codFormando, null, null, null, null, null);
            $qualificacao = new Qualificacao($qualiNivel->getCod_quali(), null, null);
            $nivel        = new Nivel($qualiNivel->getCod_nivel(), null);

            try {
                $sucesso = $acao === 'gravar'
                    ? $controller->cadastrarMatricula($formando, $qualificacao, $nivel, $idQualiNivel, $data, $codTurma)
                    : $controller->atualizarMatricula($codigo, $formando, $qualificacao, $nivel, $idQualiNivel, $data, $codTurma);

                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Matrícula registada com sucesso!' : 'Matrícula atualizada com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível gravar a matrícula.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa       = trim($_GET['pesquisa'] ?? '');
$matriculas     = $controller->listarMatricula($pesquisa);
$formandos      = $formandoCtrl->listar();
$campos         = (new CampoController())->listarCampo();
$pares          = (new Quali_NivelController())->listarPares();
$turmas         = $turmaCtrl->listarTurma();

$formandosLista = array_map(function ($f) {
    return ['codigo' => $f->getCodigo(), 'descricao' => $f->getNome() . ' ' . $f->getApelido()];
}, $formandos);

function filtrarFormandos(array $lista, string $termo): array {
    if ($termo === '') {
        return $lista;
    }
    return array_values(array_filter($lista, function ($f) use ($termo) {
        return mb_stripos($f['descricao'], $termo) !== false;
    }));
}

function codQualificacaoDoPar($idQualiNivel): ?int {
    if (!$idQualiNivel) {
        return null;
    }
    $qn = (new Quali_NivelController())->buscar($idQualiNivel);
    return $qn ? (int) $qn->getCod_quali() : null;
}


function anoDaData(?string $data): ?string {
    return ($data && preg_match('/^(\d{4})-\d{2}-\d{2}$/', $data, $m)) ? $m[1] : null;
}


function filtrarTurmasPorQualificacao(array $turmas, ?int $codQuali, ?string $ano = null): array {
    if (!$codQuali) {
        return [];
    }
    return array_values(array_filter($turmas, function ($t) use ($codQuali, $ano) {
        if ((int) $t->getQualiNivel()->getCod_quali() !== $codQuali) {
            return false;
        }
        return $ano === null || (string) $t->getAnoIngresso() === $ano;
    }));
}

$carregar      = isset($_GET['carregar']);
$modalAtivo    = $_GET['modal'] ?? '';
$ctxCampo      = filter_input(INPUT_GET, 'codCampo', FILTER_VALIDATE_INT);
$ctxQualiNivel = filter_input(INPUT_GET, 'idQualiNivel', FILTER_VALIDATE_INT);
$ctxFormando   = filter_input(INPUT_GET, 'codFormando', FILTER_VALIDATE_INT);
$ctxTurma      = filter_input(INPUT_GET, 'codTurma', FILTER_VALIDATE_INT);
$ctxBusca      = trim($_GET['buscaFormando'] ?? '');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_MATRICULAS);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_MATRICULAS);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_MATRICULAS);

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
                            <th>Campo</th>
                            <th>Nível</th>
                            <th>Turma</th>
                            <th>Data</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($matriculas)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 8 : 7 ?>" class="table-empty">
                                    Nenhuma matrícula registada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($matriculas as $matricula): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($matricula->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($matricula->getFormando()->getNome() . ' ' . $matricula->getFormando()->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($matricula->getQualificacao()->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars($matricula->getQualificacao()->getCampo() ?: '-') ?></td>
                                    <td><?= htmlspecialchars($matricula->getNivel() ? $matricula->getNivel()->getNome() : '-') ?></td>
                                    <td><?= htmlspecialchars($matricula->getNomeTurma() ?: '-') ?></td>
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
                                                    <?php
                                                    $sufixo = 'editar-' . $matricula->getCodigo();
                                                    $editAtivo = $carregar && $modalAtivo === $sufixo;
                                                    $campoReg = $matricula->getQualificacao()->getCodCampo();
                                                    $campoEdit = $editAtivo ? (int) $ctxCampo : (int) $campoReg;
                                                    $paresEdit = $campoEdit ? (new Quali_NivelController())->listarPares($campoEdit) : $pares;
                                                    $formandosEdit = $campoEdit ? $formandoCtrl->listarPorCampo($campoEdit) : $formandosLista;
                                                    $buscaEdit = $editAtivo ? $ctxBusca : '';
                                                    $formandosEdit = filtrarFormandos($formandosEdit, $buscaEdit);
                                                    $qnSel = $editAtivo ? $ctxQualiNivel : (int) $matricula->getId_quali_nivel();
                                                    $dataEdit = $editAtivo ? trim($_GET['data'] ?? '') : $matricula->getDataMatricula();
                                                    $turmasEdit = filtrarTurmasPorQualificacao($turmas, codQualificacaoDoPar($qnSel), anoDaData($dataEdit));
                                                    $formandoSel = $editAtivo ? $ctxFormando : (int) $matricula->getFormando()->getCodigo();
                                                    $turmaSel = $editAtivo ? (int) $ctxTurma : (int) $matricula->getCodTurma();
                                                    ?>
                                                    <input type="hidden" name="modal" value="<?= htmlspecialchars($sufixo) ?>">
                                                    <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">

                                                    <!-- 1. Campo / Área -->
                                                    <div class="form-group">
                                                        <label for="e-c-<?= $matricula->getCodigo() ?>">Campo / Área</label>
                                                        <select id="e-c-<?= $matricula->getCodigo() ?>" name="codCampo" class="form-control">
                                                            <option value="">Selecione o campo...</option>
                                                            <?php foreach ($campos as $c): ?>
                                                                <option value="<?= (int) $c->getCodigo() ?>"
                                                                    <?= $campoEdit === (int) $c->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($c->getNome()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $matricula->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>

                                                    <!-- 2. Qualificação / Nível -->
                                                    <div class="form-group">
                                                        <label for="e-qn-<?= $matricula->getCodigo() ?>">Qualificação / Nível <span class="required">*</span></label>
                                                        <select id="e-qn-<?= $matricula->getCodigo() ?>" name="idQualiNivel" class="form-control" required>
                                                            <option value="">Selecione a qualificação e o nível...</option>
                                                            <?php foreach ($paresEdit as $p): ?>
                                                                <option value="<?= (int) $p['codigo'] ?>"
                                                                    <?= (int) $qnSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <!-- 3. Data: define o ano das turmas disponíveis -->
                                                    <div class="form-group">
                                                        <label for="e-d-<?= $matricula->getCodigo() ?>">Data <span class="required">*</span></label>
                                                        <input type="date" id="e-d-<?= $matricula->getCodigo() ?>" name="data" class="form-control"
                                                               value="<?= htmlspecialchars($dataEdit) ?>" required>
                                                    </div>

                                                    <!-- 4. Turma (mesma qualificação e mesmo ano da data) -->
                                                    <div class="form-group">
                                                        <label for="e-t-<?= $matricula->getCodigo() ?>">Turma</label>
                                                        <select id="e-t-<?= $matricula->getCodigo() ?>" name="codTurma" class="form-control">
                                                            <option value="">Sem turma</option>
                                                            <?php foreach ($turmasEdit as $t): ?>
                                                                <option value="<?= (int) $t->getCodigo() ?>"
                                                                    <?= $turmaSel === (int) $t->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($t->getNome() . ' (' . $t->getAnoIngresso() . ')') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $matricula->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar turmas</button>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="e-bf-<?= $matricula->getCodigo() ?>">Pesquisar formando</label>
                                                        <input type="text" id="e-bf-<?= $matricula->getCodigo() ?>" name="buscaFormando" class="form-control"
                                                               maxlength="100" placeholder="Escreva o nome e clique em Pesquisar"
                                                               value="<?= htmlspecialchars($buscaEdit) ?>">
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $matricula->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Pesquisar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-f-<?= $matricula->getCodigo() ?>">Formando <span class="required">*</span></label>
                                                        <select id="e-f-<?= $matricula->getCodigo() ?>" name="codFormando" class="form-control" required>
                                                            <?php if (empty($formandosEdit)): ?>
                                                                <option value="">Nenhum formando encontrado</option>
                                                            <?php endif; ?>
                                                            <?php foreach ($formandosEdit as $f): ?>
                                                                <option value="<?= (int) $f['codigo'] ?>"
                                                                    <?= (int) $formandoSel === (int) $f['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($f['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
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
                                                        <p><strong>Turma:</strong> <?= htmlspecialchars($matricula->getNomeTurma() ?: '-') ?></p>
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
                        <?php
                        $novoAtivo = $carregar && $modalAtivo === 'novo';
                        $campoNovo = $novoAtivo ? (int) $ctxCampo : 0;
                        $paresNovo = $campoNovo ? (new Quali_NivelController())->listarPares($campoNovo) : $pares;
                        $formandosNovo = $campoNovo ? $formandoCtrl->listarPorCampo($campoNovo) : $formandosLista;
                        $buscaNovo = $novoAtivo ? $ctxBusca : '';
                        $formandosNovo = filtrarFormandos($formandosNovo, $buscaNovo);
                        $qnNovoSel = $novoAtivo ? $ctxQualiNivel : null;
                        $dataNovo = $novoAtivo ? trim($_GET['data'] ?? '') : '';
                        $turmasNovo = filtrarTurmasPorQualificacao($turmas, codQualificacaoDoPar($qnNovoSel), anoDaData($dataNovo));
                        $formandoNovoSel = $novoAtivo ? $ctxFormando : null;
                        $turmaNovoSel = $novoAtivo ? (int) $ctxTurma : 0;
                        ?>
                        <input type="hidden" name="modal" value="novo">
                        <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">

                        <!-- 1. Campo / Área -->
                        <div class="form-group">
                            <label for="n-c">Campo / Área</label>
                            <select id="n-c" name="codCampo" class="form-control">
                                <option value="">Selecione o campo...</option>
                                <?php foreach ($campos as $c): ?>
                                    <option value="<?= (int) $c->getCodigo() ?>"
                                        <?= $campoNovo === (int) $c->getCodigo() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>

                        <!-- 2. Qualificação / Nível -->
                        <div class="form-group">
                            <label for="n-qn">Qualificação / Nível <span class="required">*</span></label>
                            <select id="n-qn" name="idQualiNivel" class="form-control" required>
                                <option value="">Selecione a qualificação e o nível...</option>
                                <?php foreach ($paresNovo as $p): ?>
                                    <option value="<?= (int) $p['codigo'] ?>"
                                        <?= $qnNovoSel !== null && (int) $qnNovoSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 3. Data: define o ano das turmas disponíveis -->
                        <div class="form-group">
                            <label for="n-d">Data <span class="required">*</span></label>
                            <input type="date" id="n-d" name="data" class="form-control"
                                   value="<?= htmlspecialchars($dataNovo) ?>" required>
                        </div>

                        <!-- 4. Turma (mesma qualificação e mesmo ano da data) -->
                        <div class="form-group">
                            <label for="n-t">Turma</label>
                            <select id="n-t" name="codTurma" class="form-control">
                                <option value="">Sem turma</option>
                                <?php foreach ($turmasNovo as $t): ?>
                                    <option value="<?= (int) $t->getCodigo() ?>"
                                        <?= $turmaNovoSel === (int) $t->getCodigo() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t->getNome() . ' (' . $t->getAnoIngresso() . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar turmas</button>
                        </div>

                        <div class="form-group">
                            <label for="n-bf">Pesquisar formando</label>
                            <input type="text" id="n-bf" name="buscaFormando" class="form-control"
                                   maxlength="100" placeholder="Escreva o nome e clique em Pesquisar"
                                   value="<?= htmlspecialchars($buscaNovo) ?>">
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Pesquisar</button>
                        </div>
                        <div class="form-group">
                            <label for="n-f">Formando <span class="required">*</span></label>
                            <select id="n-f" name="codFormando" class="form-control" required>
                                <?php if (empty($formandosNovo)): ?>
                                    <option value="">Nenhum formando encontrado</option>
                                <?php endif; ?>
                                <?php foreach ($formandosNovo as $f): ?>
                                    <option value="<?= (int) $f['codigo'] ?>"
                                        <?= $formandoNovoSel !== null && (int) $formandoNovoSel === (int) $f['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
