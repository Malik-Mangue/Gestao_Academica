<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/InscricaoController.php';
require_once __DIR__ . '/../../controller/FormandoController.php';
require_once __DIR__ . '/../../controller/ModuloController.php';
require_once __DIR__ . '/../../controller/QualificacaoController.php';

$controller   = new InscricaoController();
$formandoCtrl = new FormandoController();
$moduloCtrl   = new ModuloController();
$qualiCtrl    = new QualificacaoController();

$semestres = ['1º Semestre', '2º Semestre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, 'index.php');
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
        } elseif (!Validador::data($data)) {
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
$qualificacoes = $qualiCtrl->comboQualificacao();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::pode(Sessao::ACAO_CRIAR);
$pode_editar  = Sessao::pode(Sessao::ACAO_EDITAR);
$pode_remover = Sessao::pode(Sessao::ACAO_REMOVER);

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
            <!-- <form method="get" action="index.php" class="form-actions">
                 <div class="form-group">
                    <label for="pesquisa">Pesquisar por semestre</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="20" placeholder="Ex: 1º Semestre"
                           value="<?= htmlspecialchars($pesquisa) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpar</a>
            </form> -->

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Formando</th>
                            <th>Módulo</th>
                            <th>Qualificação</th>   <!-- NOVO -->
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
                                <td colspan="<?= $pode_editar || $pode_remover ? 7 : 6 ?>" class="table-empty"> <!-- ALTERADO: colspan +1 -->
                                    Nenhuma inscrição registada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inscricoes as $inscricao): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($inscricao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($inscricao->getFormando()->getNome() . ' ' . $inscricao->getFormando()->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getModulo()->getNome()) ?></td>
                                    <!-- NOVO: coluna Qualificação -->
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
                                                    <div class="form-group">
                                                        <label for="e-f-<?= $inscricao->getCodigo() ?>">Formando <span class="required">*</span></label>
                                                        <select id="e-f-<?= $inscricao->getCodigo() ?>" name="codFormando" class="form-control" required>
                                                            <?php foreach ($formandos as $f): ?>
                                                                <option value="<?= (int) $f->getCodigo() ?>"
                                                                    <?= (int) $inscricao->getFormando()->getCodigo() === (int) $f->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($f->getNome() . ' ' . $f->getApelido()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-m-<?= $inscricao->getCodigo() ?>">Módulo <span class="required">*</span></label>
                                                        <select id="e-m-<?= $inscricao->getCodigo() ?>" name="codModulo" class="form-control" required>
                                                            <?php foreach ($modulos as $op): ?>
                                                                <option value="<?= (int) $op['codigo'] ?>"
                                                                    <?= (int) $inscricao->getModulo()->getCodigo() === (int) $op['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($op['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-s-<?= $inscricao->getCodigo() ?>">Semestre <span class="required">*</span></label>
                                                        <select id="e-s-<?= $inscricao->getCodigo() ?>" name="semestre" class="form-control" required>
                                                            <?php foreach ($semestres as $s): ?>
                                                                <option value="<?= htmlspecialchars($s) ?>"
                                                                    <?= $inscricao->getSemestre() === $s ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($s) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-d-<?= $inscricao->getCodigo() ?>">Data de Inscrição <span class="required">*</span></label>
                                                        <input type="date" id="e-d-<?= $inscricao->getCodigo() ?>" name="data_inscricao" class="form-control"
                                                               value="<?= htmlspecialchars($inscricao->getDataInscricao()) ?>" required>
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
                            <label for="n-m">Modulo <span class="required">*</span></label>
                            <select id="n-m" name="codModulo" class="form-control" required>
                                <?php foreach ($modulos as $m): ?>
                                    <option value="<?= (int) $m['codigo'] ?>">
                                        <?= htmlspecialchars($m['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-s">Semestre <span class="required">*</span></label>
                            <select id="n-s" name="semestre" class="form-control" required>
                                <option value="">Selecione o semestre...</option>
                                <?php foreach ($semestres as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="n-d">Data <span class="required">*</span></label>
                            <input type="date" id="n-d" name="data_inscricao" class="form-control" required>
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
