<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/TurmaController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../controller/CampoController.php';
require_once __DIR__ . '/../../model/Formador.php';
require_once __DIR__ . '/../../model/Diretor_turma.php';
require_once __DIR__ . '/../../model/Qualificacao.php';
require_once __DIR__ . '/../../model/Nivel.php';

$controller = new TurmaController();

$turnos = ['Manhã', 'Tarde', 'Noite'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_TURMAS, 'index.php');

        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        if (!$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Código inválido para remoção.'];
        } else {
            try {
                $sucesso = $controller->apagarTurma($codigo);
                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => 'Turma removida com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível eliminar a turma.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar' || $acao === 'editar') {
        Sessao::exigirAcao(
            $acao === 'gravar' ? Sessao::ACAO_CRIAR : Sessao::ACAO_EDITAR,
            Sessao::RECURSO_TURMAS,
            'index.php'
        );

        $codigo       = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $nome         = trim($_POST['nome'] ?? '');
        $anoLectivo   = trim($_POST['ano_lectivo'] ?? '');
        $turno        = trim($_POST['turno'] ?? '');
        $codDiretor   = filter_input(INPUT_POST, 'codDiretor', FILTER_VALIDATE_INT);
        $idQualiNivel = filter_input(INPUT_POST, 'idQualiNivel', FILTER_VALIDATE_INT);

        $qualiNivel = $idQualiNivel ? (new Quali_NivelController())->buscar($idQualiNivel) : null;

        if ($acao === 'editar' && !$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Turma inválida para atualização.'];
        } elseif (!$controller->texto($nome, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O nome da turma é obrigatório (até 40 caracteres).'];
        } elseif (!$controller->inteiroPositivo($anoLectivo) || strlen($anoLectivo) !== 4) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O ano lectivo deve ter 4 dígitos (ex.: 2025).'];
        } elseif (!in_array($turno, $turnos, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione um turno válido.'];
        } elseif (!$codDiretor) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o diretor de turma.'];
        } elseif ($qualiNivel === null) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione a qualificação e o nível da turma.'];
        } else {
            $formador     = new Formador($codDiretor, null, null, null, null, null, null, null, null, null);
            $diretor      = new Diretor_turma($formador);
            $qualificacao = new Qualificacao($qualiNivel->getCod_quali(), null, null);
            $nivel        = new Nivel($qualiNivel->getCod_nivel(), null);

            try {
                $sucesso = $acao === 'gravar'
                    ? $controller->cadastrarTurma($nome, (int) $anoLectivo, $turno, $diretor, $qualificacao, $nivel, $qualiNivel)
                    : $controller->atualizarTurma($codigo, $nome, (int) $anoLectivo, $turno, $diretor, $qualificacao, $nivel, $qualiNivel);

                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Turma cadastrada com sucesso!' : 'Turma atualizada com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível gravar a turma.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');

$turmas        = $controller->listarTurma($pesquisa);
$diretores     = $controller->listarDiretores();
$pares         = (new Quali_NivelController())->listarPares();
$campos        = (new CampoController())->listarCampo(); 

// Contexto do "Carregar" (sem JavaScript): o campo selecionado filtra a
// qualificação/nível e o diretor de turma.
$carregar      = isset($_GET['carregar']);
$modalAtivo    = $_GET['modal'] ?? '';
$ctxCampo      = filter_input(INPUT_GET, 'codCampo', FILTER_VALIDATE_INT);
$ctxQualiNivel = filter_input(INPUT_GET, 'idQualiNivel', FILTER_VALIDATE_INT);
$ctxDiretor    = filter_input(INPUT_GET, 'codDiretor', FILTER_VALIDATE_INT);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_TURMAS);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_TURMAS);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_TURMAS);

$page_title = 'Gestão de Turmas';
$active_menu = 'turma';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Turmas</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Turmas</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Turmas</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Nova Turma</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="60" placeholder="Pesquisar por nome..."
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
                            <th>Nome</th>
                            <th>Ano Lectivo</th>
                            <th>Turno</th>
                            <th>Diretor de Turma</th>
                            <th>Qualificação</th>
                            <th>Campo</th>
                            <th>Nível</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($turmas)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 9 : 8 ?>" class="table-empty">
                                    Nenhuma turma cadastrada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($turmas as $turma): ?>
                                <?php
                                $nomeDiretor = ($turma->getDiretorTurma() !== null && $turma->getDiretorTurma()->getFormador() !== null)
                                    ? $turma->getDiretorTurma()->getFormador()->getNome() : '-';


                                    $qn = $turma->getQualiNivel();
                                    $tituloQuali = ($qn !== null && $qn->getQualificacao() !== null) ? $qn->getQualificacao()->getTitulo() : '-';
                                    $nomeNivel = ($qn !== null && $qn->getNivel() !== null) ? $qn->getNivel()->getNome() : '-';
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string) $turma->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($turma->getNome()) ?></td>
                                    <td><?= htmlspecialchars((string) $turma->getAnoIngresso()) ?></td>
                                    <td><?= htmlspecialchars($turma->getTurno()) ?></td>
                                    <td><?= htmlspecialchars((string) $nomeDiretor) ?></td>
                                    <td><?= htmlspecialchars((string) $tituloQuali) ?></td>
                                    <td><?= htmlspecialchars(($qn !== null && $qn->getQualificacao() !== null && $qn->getQualificacao()->getCampo() !== null) ? $qn->getQualificacao()->getCampo() : '-') ?></td>
                                    <td><?= htmlspecialchars((string) $nomeNivel) ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $turma->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $turma->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $turma->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Turma #<?= htmlspecialchars((string) $turma->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $turma->getCodigo()) ?>">
                                                    <?php
                                                    $sufixo = 'editar-' . $turma->getCodigo();
                                                    $qnTurma = $turma->getQualiNivel();
                                                    $editAtivo = $carregar && $modalAtivo === $sufixo;
                                                    $campoReg = ($qnTurma !== null && $qnTurma->getQualificacao() !== null)
                                                        ? $qnTurma->getQualificacao()->getCodCampo() : null;
                                                    $campoEdit = $editAtivo ? (int) $ctxCampo : (int) $campoReg;
                                                    $paresEdit = $campoEdit ? (new Quali_NivelController())->listarPares($campoEdit) : $pares;
                                                    $diretoresEdit = $campoEdit ? $controller->listarDiretores($campoEdit) : $diretores;
                                                    $qnSel = $editAtivo ? $ctxQualiNivel : ($qnTurma !== null ? (int) $qnTurma->getCodigo() : null);
                                                    $dirSel = $editAtivo ? $ctxDiretor : (int) $turma->getDiretorTurma()->getFormador()->getCodigo();
                                                    ?>
                                                    <input type="hidden" name="modal" value="<?= htmlspecialchars($sufixo) ?>">
                                                    <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                                                    <div class="form-group">
                                                        <label for="t-campo-<?= $sufixo ?>">Campo / Área</label>
                                                        <select id="t-campo-<?= $sufixo ?>" name="codCampo" class="form-control">
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
                                                                formaction="index.php#modal-editar-<?= (int) $turma->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="t-qn-<?= $sufixo ?>">Qualificação / Nível <span class="required">*</span></label>
                                                        <select id="t-qn-<?= $sufixo ?>" name="idQualiNivel" class="form-control" required>
                                                            <option value="">Selecione...</option>
                                                            <?php foreach ($paresEdit as $p): ?>
                                                                <option value="<?= (int) $p['codigo'] ?>"
                                                                    <?= (int) $qnSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="t-diretor-<?= $sufixo ?>">Diretor de Turma <span class="required">*</span></label>
                                                        <select id="t-diretor-<?= $sufixo ?>" name="codDiretor" class="form-control" required>
                                                            <option value="">Selecione...</option>
                                                            <?php foreach ($diretoresEdit as $d): ?>
                                                                <option value="<?= (int) $d['codigo'] ?>"
                                                                    <?= (int) $dirSel === (int) $d['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($d['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="t-turno-<?= $sufixo ?>">Turno <span class="required">*</span></label>
                                                        <select id="t-turno-<?= $sufixo ?>" name="turno" class="form-control" required>
                                                            <option value="">Selecione...</option>
                                                            <?php foreach ($turnos as $t): ?>
                                                                <option value="<?= htmlspecialchars($t) ?>" <?= $t === $turma->getTurno() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($t) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="t-ano-<?= $sufixo ?>">Ano lectivo <span class="required">*</span></label>
                                                        <input type="number" id="t-ano-<?= $sufixo ?>" name="ano_lectivo" class="form-control"
                                                               min="1000" max="9999" step="1" placeholder="Ex: 2025"
                                                               value="<?= htmlspecialchars((string) $turma->getAnoIngresso()) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="t-nome-<?= $sufixo ?>">Nome da turma <span class="required">*</span></label>
                                                        <input type="text" id="t-nome-<?= $sufixo ?>" name="nome" class="form-control"
                                                               placeholder="Ex: TI-2025-A" maxlength="40"
                                                               value="<?= htmlspecialchars($turma->getNome()) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="acao" value="editar" class="btn btn-success">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pode_remover): ?>
                                    <div id="modal-deletar-<?= $turma->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $turma->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente este registro?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars((string) $turma->getCodigo()) ?></p>
                                                        <p><strong>Nome:</strong> <?= htmlspecialchars($turma->getNome()) ?></p>
                                                        <p><strong>Ano lectivo:</strong> <?= htmlspecialchars((string) $turma->getAnoIngresso()) ?></p>
                                                        <p><strong>Turno:</strong> <?= htmlspecialchars($turma->getTurno()) ?></p>
                                                    </div>
                                                    <p class="confirm-warning">
                                                        Atenção: esta ação não poderá ser desfeita.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="acao" value="deletar" class="btn btn-delete">Sim, Deletar</button>
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
                    <h3>Cadastrar Nova Turma</h3>
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
                        $diretoresNovo = $campoNovo ? $controller->listarDiretores($campoNovo) : $diretores;
                        $qnNovoSel = $novoAtivo ? $ctxQualiNivel : null;
                        $dirNovoSel = $novoAtivo ? $ctxDiretor : null;
                        $turnoNovoSel = $novoAtivo ? trim($_GET['turno'] ?? '') : '';
                        $anoNovo = $novoAtivo ? trim($_GET['ano_lectivo'] ?? '') : '';
                        $nomeNovo = $novoAtivo ? trim($_GET['nome'] ?? '') : '';
                        ?>
                        <input type="hidden" name="modal" value="novo">
                        <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                        <div class="form-group">
                            <label for="t-campo-novo">Campo / Área</label>
                            <select id="t-campo-novo" name="codCampo" class="form-control">
                                <option value="">Selecione o campo...</option>
                                <?php foreach ($campos as $c): ?>
                                    <option value="<?= (int) $c->getCodigo() ?>"
                                        <?= $campoNovo === (int) $c->getCodigo() ? 'selected' : '' ?>><?= htmlspecialchars($c->getNome()) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>
                        <div class="form-group">
                            <label for="t-qn-novo">Qualificação / Nível <span class="required">*</span></label>
                            <select id="t-qn-novo" name="idQualiNivel" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($paresNovo as $p): ?>
                                    <option value="<?= (int) $p['codigo'] ?>"
                                        <?= $qnNovoSel !== null && (int) $qnNovoSel === (int) $p['codigo'] ? 'selected' : '' ?>><?= htmlspecialchars($p['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="t-diretor-novo">Diretor de Turma <span class="required">*</span></label>
                            <select id="t-diretor-novo" name="codDiretor" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($diretoresNovo as $d): ?>
                                    <option value="<?= (int) $d['codigo'] ?>"
                                        <?= $dirNovoSel !== null && (int) $dirNovoSel === (int) $d['codigo'] ? 'selected' : '' ?>><?= htmlspecialchars($d['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="t-turno-novo">Turno <span class="required">*</span></label>
                            <select id="t-turno-novo" name="turno" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($turnos as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>"
                                        <?= $turnoNovoSel === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="t-ano-novo">Ano lectivo <span class="required">*</span></label>
                            <input type="number" id="t-ano-novo" name="ano_lectivo" class="form-control"
                                   min="1000" max="9999" step="1" placeholder="Ex: 2025"
                                   value="<?= htmlspecialchars((string) $anoNovo) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="t-nome-novo">Nome da turma <span class="required">*</span></label>
                            <input type="text" id="t-nome-novo" name="nome" class="form-control"
                                   placeholder="Ex: TI-2025-A" maxlength="40"
                                   value="<?= htmlspecialchars($nomeNovo) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="acao" value="gravar" class="btn btn-success">Salvar Turma</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
