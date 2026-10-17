<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/InscricaoController.php';
require_once __DIR__ . '/../../controller/FormandoController.php';
require_once __DIR__ . '/../../controller/ModuloController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../controller/CampoController.php';

$controller   = new InscricaoController();
$formandoCtrl = new FormandoController();
$moduloCtrl   = new ModuloController();
$qualiNivelCtrl = new Quali_NivelController();

$semestres = ['1º Semestre', '2º Semestre'];
$tiposModulo = ['Genérico', 'Vocacional'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_INSCRICOES, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $_SESSION['flash'] = $controller->apagarInscricao($codigo)
            ? ['type' => 'success', 'msg' => 'Inscrição removida com sucesso!']
            : ['type' => 'danger', 'msg' => 'Não foi possível eliminar a inscrição.'];
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar' || $acao === 'editar') {
        Sessao::exigirAcao(
            $acao === 'gravar' ? Sessao::ACAO_CRIAR : Sessao::ACAO_EDITAR,
            Sessao::RECURSO_INSCRICOES,
            'index.php'
        );

        $codigo      = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $codFormando = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
        $codModulo   = filter_input(INPUT_POST, 'codModulo', FILTER_VALIDATE_INT);
        $semestre    = trim($_POST['semestre'] ?? '');
        $data        = trim($_POST['data_inscricao'] ?? '');

        if (!$codFormando) {
              $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o formando.'];
        } elseif (!$codModulo) {
              $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o módulo.'];
        } elseif (!in_array($semestre, $semestres, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione um semestre válido.'];
        } elseif (!$controller->data($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique uma data de inscrição válida.'];
        } else {
            $formando = new Formando($codFormando, null, null, null, null, null);
            $modulo   = new Modulo($codModulo, null, null);

            $sucesso = $acao === 'gravar'
                ? $controller->cadastrarInscricao($formando, $modulo, $semestre, $data)
                : $controller->atualizarInscricao($codigo, $formando, $modulo, $semestre, $data);

            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Inscrição registada com sucesso!' : 'Inscrição atualizada com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar a inscrição.'];
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa  = trim($_GET['pesquisa'] ?? '');
$inscricoes = $controller->listarInscricao($pesquisa);
$formandos  = $formandoCtrl->listar();
$modulos    = $moduloCtrl->listarModulos();
$pares      = $qualiNivelCtrl->listarPares();
$campos     = (new CampoController())->listarCampo(); 

// Contexto do "Carregar" (sem JavaScript): Campo filtra Qualificação/Nível e
// Formandos; Qualificação/Nível + Tipo de Módulo + Semestre filtram os módulos.
$carregar      = isset($_GET['carregar']);
$modalAtivo    = $_GET['modal'] ?? '';
$ctxCampo      = filter_input(INPUT_GET, 'codCampo', FILTER_VALIDATE_INT);
$ctxQualiNivel = filter_input(INPUT_GET, 'idQualiNivel', FILTER_VALIDATE_INT);
$ctxTipo       = trim($_GET['tipoModulo'] ?? '');
$ctxSemestre   = trim($_GET['semestre'] ?? '');
$ctxModulo     = filter_input(INPUT_GET, 'codModulo', FILTER_VALIDATE_INT);
$ctxFormando   = filter_input(INPUT_GET, 'codFormando', FILTER_VALIDATE_INT);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_INSCRICOES);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_INSCRICOES);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_INSCRICOES);

$page_title = 'Inscrições em Módulos';
$active_menu = 'inscricao';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Inscrições em Módulos</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Inscrições</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Inscrições</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Nova Inscrição</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Formando, módulo, qualificação, semestre ou data"
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
                            <th>Módulo</th>
                            <th>Qualificação</th>   
                            <th>Semestre</th>
                            <th>Data de Inscrição</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inscricoes)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 7 : 6 ?>" class="table-empty">
                                    Nenhuma inscrição encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inscricoes as $inscricao): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($inscricao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($inscricao->getFormando()->getNome() . ' ' . $inscricao->getFormando()->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getModulo()->getNome()) ?></td>
                                    <td><?= htmlspecialchars(
                                            $inscricao->getModulo()->getQualiNivel() && $inscricao->getModulo()->getQualiNivel()->getQualificacao()
                                                ? $inscricao->getModulo()->getQualiNivel()->getQualificacao()->getTitulo()
                                                : '-'
                                        ) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getSemestre()) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getDataInscricao() ?: '-') ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $inscricao->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $inscricao->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $inscricao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Inscrição #<?= htmlspecialchars($inscricao->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($inscricao->getCodigo()) ?>">
                                                    <?php
                                                    $sufixo = 'editar-' . $inscricao->getCodigo();
                                                    $editAtivo = $carregar && $modalAtivo === $sufixo;
                                                    $qnInscricao = $inscricao->getModulo()->getQualiNivel();
                                                    $campoReg = ($qnInscricao !== null && $qnInscricao->getQualificacao() !== null)
                                                        ? $qnInscricao->getQualificacao()->getCodCampo() : null;
                                                    $campoEdit = $editAtivo ? (int) $ctxCampo : (int) $campoReg;
                                                    $paresEdit = $campoEdit ? $qualiNivelCtrl->listarPares($campoEdit) : $pares;
                                                    $qnEditSel = $editAtivo ? $ctxQualiNivel : ($qnInscricao !== null ? (int) $qnInscricao->getCodigo() : null);
                                                    $tipoEditSel = $editAtivo ? $ctxTipo : '';
                                                    $semEditSel = $editAtivo ? $ctxSemestre : $inscricao->getSemestre();
                                                    $codQualiEdit = null;
                                                    if ($qnEditSel) {
                                                        $qnObj = $qualiNivelCtrl->buscar($qnEditSel);
                                                        $codQualiEdit = $qnObj !== null ? $qnObj->getCod_quali() : null;
                                                    }
                                                    $modulosEdit = $editAtivo
                                                        ? $moduloCtrl->listarModulosFiltrados(
                                                            $codQualiEdit ?: null,
                                                            $tipoEditSel !== '' ? $tipoEditSel : null,
                                                            $semEditSel !== '' ? $semEditSel : null
                                                          )
                                                        : $modulos;
                                                    $moduloEditSel = $editAtivo ? $ctxModulo : (int) $inscricao->getModulo()->getCodigo();
                                                    $formandosEdit = $campoEdit ? $formandoCtrl->listarPorCampo($campoEdit) : array_map(function ($f) {
                                                        return ['codigo' => $f->getCodigo(), 'descricao' => $f->getNome() . ' ' . $f->getApelido()];
                                                    }, $formandos);
                                                    $formandoEditSel = $editAtivo ? $ctxFormando : (int) $inscricao->getFormando()->getCodigo();
                                                    $dataEdit = $editAtivo ? trim($_GET['data_inscricao'] ?? '') : $inscricao->getDataInscricao();
                                                    ?>
                                                    <input type="hidden" name="modal" value="<?= htmlspecialchars($sufixo) ?>">
                                                    <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                                                    <div class="form-group">
                                                        <label for="e-c-<?= $inscricao->getCodigo() ?>">Campo / Área</label>
                                                        <select id="e-c-<?= $inscricao->getCodigo() ?>" name="codCampo" class="form-control">
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
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-qn-<?= $inscricao->getCodigo() ?>">Qualificação / Nível</label>
                                                        <select id="e-qn-<?= $inscricao->getCodigo() ?>" name="idQualiNivel" class="form-control">
                                                            <option value="">Selecione a qualificação e o nível...</option>
                                                            <?php foreach ($paresEdit as $p): ?>
                                                                <option value="<?= (int) $p['codigo'] ?>"
                                                                    <?= $qnEditSel !== null && (int) $qnEditSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-t-<?= $inscricao->getCodigo() ?>">Tipo de Módulo</label>
                                                        <select id="e-t-<?= $inscricao->getCodigo() ?>" name="tipoModulo" class="form-control">
                                                            <option value="">Todos os tipos</option>
                                                            <?php foreach ($tiposModulo as $tp): ?>
                                                                <option value="<?= htmlspecialchars($tp) ?>"
                                                                    <?= $tipoEditSel === $tp ? 'selected' : '' ?>><?= htmlspecialchars($tp) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-s-<?= $inscricao->getCodigo() ?>">Semestre <span class="required">*</span></label>
                                                        <select id="e-s-<?= $inscricao->getCodigo() ?>" name="semestre" class="form-control" required>
                                                            <?php foreach ($semestres as $s): ?>
                                                                <option value="<?= htmlspecialchars($s) ?>"
                                                                    <?= $semEditSel === $s ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($s) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-m-<?= $inscricao->getCodigo() ?>">Módulo <span class="required">*</span></label>
                                                        <select id="e-m-<?= $inscricao->getCodigo() ?>" name="codModulo" class="form-control" required>
                                                            <option value="">Selecione o módulo...</option>
                                                            <?php foreach ($modulosEdit as $op): ?>
                                                                <option value="<?= (int) $op['codigo'] ?>"
                                                                    <?= (int) $moduloEditSel === (int) $op['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($op['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-d-<?= $inscricao->getCodigo() ?>">Data de Inscrição <span class="required">*</span></label>
                                                        <input type="date" id="e-d-<?= $inscricao->getCodigo() ?>" name="data_inscricao" class="form-control"
                                                               value="<?= htmlspecialchars($dataEdit) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-f-<?= $inscricao->getCodigo() ?>">Formando <span class="required">*</span></label>
                                                        <select id="e-f-<?= $inscricao->getCodigo() ?>" name="codFormando" class="form-control" required>
                                                            <?php foreach ($formandosEdit as $f): ?>
                                                                <option value="<?= (int) $f['codigo'] ?>"
                                                                    <?= (int) $formandoEditSel === (int) $f['codigo'] ? 'selected' : '' ?>>
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
                                    <div id="modal-deletar-<?= $inscricao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($inscricao->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente esta inscrição?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars($inscricao->getCodigo()) ?></p>
                                                        <p><strong>Formando:</strong> <?= htmlspecialchars($inscricao->getFormando()->getNome()) ?></p>
                                                        <p><strong>Módulo:</strong> <?= htmlspecialchars($inscricao->getModulo()->getNome()) ?></p>
                                                        <p><strong>Semestre:</strong> <?= htmlspecialchars($inscricao->getSemestre()) ?></p>
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
                    <h3>Registar Nova Inscricao</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <?php
                        $novoAtivo = $carregar && $modalAtivo === 'novo';
                        $campoNovo = $novoAtivo ? (int) $ctxCampo : 0;
                        $paresNovo = $campoNovo ? $qualiNivelCtrl->listarPares($campoNovo) : $pares;
                        $qnNovoSel = $novoAtivo ? $ctxQualiNivel : null;
                        $tipoNovoSel = $novoAtivo ? $ctxTipo : '';
                        $semNovoSel = $novoAtivo ? $ctxSemestre : '';
                        $codQualiNovo = null;
                        if ($qnNovoSel) {
                            $qnObj = $qualiNivelCtrl->buscar($qnNovoSel);
                            $codQualiNovo = $qnObj !== null ? $qnObj->getCod_quali() : null;
                        }
                        $modulosNovo = ($codQualiNovo || $tipoNovoSel !== '' || $semNovoSel !== '')
                            ? $moduloCtrl->listarModulosFiltrados(
                                $codQualiNovo ?: null,
                                $tipoNovoSel !== '' ? $tipoNovoSel : null,
                                $semNovoSel !== '' ? $semNovoSel : null
                              )
                            : $modulos;
                        $moduloNovoSel = $novoAtivo ? $ctxModulo : null;
                        $formandosNovo = $campoNovo ? $formandoCtrl->listarPorCampo($campoNovo) : array_map(function ($f) {
                            return ['codigo' => $f->getCodigo(), 'descricao' => $f->getNome() . ' ' . $f->getApelido()];
                        }, $formandos);
                        $formandoNovoSel = $novoAtivo ? $ctxFormando : null;
                        $dataNovo = $novoAtivo ? trim($_GET['data_inscricao'] ?? '') : '';
                        ?>
                        <input type="hidden" name="modal" value="novo">
                        <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
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
                        <div class="form-group">
                            <label for="n-qn">Qualificação / Nível</label>
                            <select id="n-qn" name="idQualiNivel" class="form-control">
                                <option value="">Selecione a qualificação e o nível...</option>
                                <?php foreach ($paresNovo as $p): ?>
                                    <option value="<?= (int) $p['codigo'] ?>"
                                        <?= $qnNovoSel !== null && (int) $qnNovoSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-t">Tipo de Módulo</label>
                            <select id="n-t" name="tipoModulo" class="form-control">
                                <option value="">Todos os tipos</option>
                                <?php foreach ($tiposModulo as $tp): ?>
                                    <option value="<?= htmlspecialchars($tp) ?>"
                                        <?= $tipoNovoSel === $tp ? 'selected' : '' ?>><?= htmlspecialchars($tp) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>
                        <div class="form-group">
                            <label for="n-s">Semestre <span class="required">*</span></label>
                            <select id="n-s" name="semestre" class="form-control" required>
                                <option value="">Selecione o semestre...</option>
                                <?php foreach ($semestres as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"
                                        <?= $semNovoSel === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>
                        <div class="form-group">
                            <label for="n-m">Módulo <span class="required">*</span></label>
                            <select id="n-m" name="codModulo" class="form-control" required>
                                <option value="">Selecione o módulo...</option>
                                <?php foreach ($modulosNovo as $m): ?>
                                    <option value="<?= (int) $m['codigo'] ?>"
                                        <?= $moduloNovoSel !== null && (int) $moduloNovoSel === (int) $m['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-d">Data <span class="required">*</span></label>
                            <input type="date" id="n-d" name="data_inscricao" class="form-control"
                                   value="<?= htmlspecialchars($dataNovo) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="n-f">Formando <span class="required">*</span></label>
                            <select id="n-f" name="codFormando" class="form-control" required>
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
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Inscrição</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>
