<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/QualificacaoController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';

$controller = new QualificacaoController();
$qualiNivelController = new Quali_NivelController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar'
          : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_QUALIFICACOES, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        try {
            $sucesso = $controller->delete($codigo);
            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Qualificação removida com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível remover a qualificação.'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar' || $acao === 'editar') {
        Sessao::exigirAcao(
            $acao === 'gravar' ? Sessao::ACAO_CRIAR : Sessao::ACAO_EDITAR,
            Sessao::RECURSO_QUALIFICACOES,
            'index.php'
        );

        $codigo   = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $titulo   = trim($_POST['titulo'] ?? '');
        $coordenador = filter_input(INPUT_POST, 'coordenador', FILTER_VALIDATE_INT);
        $codCampo = filter_input(INPUT_POST, 'campo', FILTER_VALIDATE_INT);
        $niveis = $_POST['nivel'] ?? [];

        if (!$controller->texto($titulo, 60)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O título da qualificação é obrigatório (até 60 caracteres).'];
        } elseif (!$coordenador) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o coordenador da qualificação.'];
        } elseif ($acao === 'gravar' && !$codCampo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o campo a que a qualificação pertence.'];
        } elseif ($acao === 'gravar' && ( !is_array($niveis) || count($niveis) === 0 )) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione pelo menos um nível.'];
        } elseif ($acao === 'editar' && !$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Código inválido para atualização da qualificação.'];
        } else {
            $sucesso = $acao === 'gravar'
                ? $controller->store()
                : $controller->update($codigo);

            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => $acao === 'gravar'
                    ? 'Qualificação cadastrada com sucesso!'
                    : 'Qualificação atualizada com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar a qualificação. Verifique os dados.'];
        }
        header('Location: index.php');
        exit;
    }
}
$pesquisa      = trim($_GET['pesquisa'] ?? '');
$qualificacoes = $controller->listar($pesquisa);
$coordenadores = $controller->listarCoordenadores();
$campos        = $controller->listarCampos();
$niveis        = $controller->listarNiveis();

// Contexto do "Carregar" (sem JavaScript): o campo seleccionado filtra a
// lista de coordenadores (apenas formadores desse campo).
$carregar   = isset($_GET['carregar']);
$modalAtivo = $_GET['modal'] ?? '';
$ctxCampo   = filter_input(INPUT_GET, 'campo', FILTER_VALIDATE_INT);

// Mapa código => nome do coordenador
$mapaCoordenadores = [];
foreach ($coordenadores as $c) {
    $mapaCoordenadores[(int) $c['codigo']] = $c['descricao'];
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_QUALIFICACOES);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_QUALIFICACOES);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_QUALIFICACOES);

$page_title = 'Gestão de Qualificações';
$active_menu = 'qualificacao';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Qualificações</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Qualificações</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Qualificações</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Nova Qualificação</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="60" placeholder="Título da qualificação"
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
                            <th>Título</th>
                            <th>Coordenador</th>
                            <th>Campo</th>
                            <th>Nível</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($qualificacoes)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 6 : 5 ?>" class="table-empty">
                                    Nenhuma qualificacao cadastrada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($qualificacoes as $qualificacao): ?>
                                <tr>
                                    <td class="col-codigo"><strong>#<?= htmlspecialchars((string) $qualificacao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($qualificacao->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars($mapaCoordenadores[(int) $qualificacao->getCod_coordenador()] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($qualificacao->getCampo() ?: '-') ?></td>
                                    <td><?= htmlspecialchars($qualificacao->getNiveis() ?: '-') ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $qualificacao->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $qualificacao->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
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
                <h3>Cadastrar Nova Qualificacao</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <?php
                    $novoAtivo = $carregar && $modalAtivo === 'novo';
                    $campoNovo = $novoAtivo ? (int) $ctxCampo : 0;
                    $coordNovo = $campoNovo ? $controller->listarCoordenadores($campoNovo) : $coordenadores;
                    $coordNovoSel = $novoAtivo ? filter_input(INPUT_GET, 'coordenador', FILTER_VALIDATE_INT) : null;
                    $tituloNovo = $novoAtivo ? trim($_GET['titulo'] ?? '') : '';
                    $niveisNovoSel = $novoAtivo ? array_map('intval', (array) ($_GET['nivel'] ?? [])) : [];
                    ?>
                    <input type="hidden" name="modal" value="novo">
                    <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                    <div class="form-group">
                        <label for="campo-novo">Campo / Área <span class="required">*</span></label>
                        <select id="campo-novo" name="campo" class="form-control" required>
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
                        <label class="required">Níveis <span class="required">*</span></label>
                        <?php foreach ($niveis as $n): ?>
                            <div class="checkbox-inline">
                                <input type="checkbox" name="nivel[]" value="<?= (int) $n->getCodigo() ?>" id="nivel-<?= (int) $n->getCodigo() ?>"
                                    <?= in_array((int) $n->getCodigo(), $niveisNovoSel, true) ? 'checked' : '' ?>>
                                <label for="nivel-<?= (int) $n->getCodigo() ?>"><?= htmlspecialchars($n->getNome()) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label for="coordenador-novo">Coordenador <span class="required">*</span></label>
                        <select id="coordenador-novo" name="coordenador" class="form-control" required>
                            <option value="">Selecione o coordenador...</option>
                            <?php foreach ($coordNovo as $coordenador): ?>
                                <option value="<?= (int) $coordenador['codigo'] ?>"
                                    <?= $coordNovoSel !== null && (int) $coordenador['codigo'] === (int) $coordNovoSel ? 'selected' : '' ?>><?= htmlspecialchars($coordenador['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="titulo-novo">Nome da qualificação <span class="required">*</span></label>
                        <input type="text" id="titulo-novo" name="titulo" class="form-control" maxlength="60" placeholder="Ex: Licenciatura em Engenharia Informatica"
                               value="<?= htmlspecialchars($tituloNovo) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Qualificacao</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($qualificacoes as $qualificacao): ?>
        <?php $cod = (int) $qualificacao->getCodigo(); ?>
        <?php if ($pode_editar): ?>
        <div id="modal-editar-<?= $cod ?>" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Editar Qualificacao</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <input type="hidden" name="codigo" value="<?= $cod ?>">
                        <?php
                        $sufixo = 'editar-' . $cod;
                        $editAtivo = $carregar && $modalAtivo === $sufixo;
                        $campoEdit = $editAtivo ? (int) $ctxCampo : (int) $qualificacao->getCodCampo();
                        $coordEdit = $campoEdit ? $controller->listarCoordenadores($campoEdit) : $coordenadores;
                        $coordEditSel = $editAtivo ? filter_input(INPUT_GET, 'coordenador', FILTER_VALIDATE_INT) : (int) $qualificacao->getCod_coordenador();
                        $tituloEdit = $editAtivo ? trim($_GET['titulo'] ?? '') : $qualificacao->getTitulo();
                        ?>
                        <input type="hidden" name="modal" value="<?= htmlspecialchars($sufixo) ?>">
                        <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                        <div class="form-group">
                            <label for="campo-e-<?= $cod ?>">Campo / Área <span class="required">*</span></label>
                            <select id="campo-e-<?= $cod ?>" name="campo" class="form-control" required>
                                <option value="">Selecione o campo...</option>
                                <?php foreach ($campos as $c): ?>
                                    <option value="<?= (int) $c->getCodigo() ?>" <?= $campoEdit === (int) $c->getCodigo() ? 'selected' : '' ?>><?= htmlspecialchars($c->getNome()) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-editar-<?= $cod ?>"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>
                        <div class="form-group">
                            <label for="nivel-e-<?= $cod ?>">Nível <span class="required">*</span></label>
                            <select id="nivel-e-<?= $cod ?>" name="nivel" class="form-control" required>
                                <option value="">Selecione o nível...</option>
                                <?php $niveisQuali = (new Quali_NivelController())->listarNiveisDaQualificacao($cod); ?>
                                <?php foreach ($niveisQuali as $n): ?>
                                    <option value="<?= (int) $n['codigo'] ?>"><?= htmlspecialchars($n['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="coordenador-e-<?= $cod ?>">Coordenador <span class="required">*</span></label>
                            <select id="coordenador-e-<?= $cod ?>" name="coordenador" class="form-control" required>
                                <option value="">Selecione o coordenador...</option>
                                <?php foreach ($coordEdit as $coordenador): ?>
                                    <option value="<?= (int) $coordenador['codigo'] ?>" <?= (int) $coordenador['codigo'] === (int) $coordEditSel ? 'selected' : '' ?>><?= htmlspecialchars($coordenador['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="titulo-e-<?= $cod ?>">Nome da qualificação <span class="required">*</span></label>
                            <input type="text" id="titulo-e-<?= $cod ?>" name="titulo" class="form-control" maxlength="60" value="<?= htmlspecialchars($tituloEdit) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" name="editar" class="btn btn-success">Salvar Alteracoes</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($pode_remover): ?>
        <div id="modal-deletar-<?= $cod ?>" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header modal-header-danger">
                    <h3>Confirmar Exclusao de Registro</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <input type="hidden" name="codigo" value="<?= $cod ?>">
                        <p class="confirm-question">
                            Tem certeza que deseja eliminar permanentemente esta qualificacao?
                        </p>
                        <div class="confirm-detail-box">
                            <p><strong>Codigo:</strong> #<?= $cod ?></p>
                            <p><strong>Titulo:</strong> <?= htmlspecialchars($qualificacao->getTitulo()) ?></p>
                            <p><strong>Coordenador:</strong> <?= htmlspecialchars($mapaCoordenadores[(int) $qualificacao->getCod_coordenador()] ?? '-') ?></p>
                        </div>
                        <p class="confirm-warning">
                            Atencao: esta acao nao podera ser desfeita e pode afetar licoes, matriculas e modulos associados.
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
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
