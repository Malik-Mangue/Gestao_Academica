<?php
// Rota privada: exige autenticação (ESPECIFICACAO_AUTENTICACAO §7.1).
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/QualificacaoController.php';

$controller = new QualificacaoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar'
          : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, 'index.php');
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
            'index.php'
        );

        $codigo   = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $titulo   = trim($_POST['titulo'] ?? '');
        $coordenador = filter_input(INPUT_POST, 'coordenador', FILTER_VALIDATE_INT);
        $codCampo = filter_input(INPUT_POST, 'campo', FILTER_VALIDATE_INT);
        $codNivel = filter_input(INPUT_POST, 'nivel', FILTER_VALIDATE_INT);

        if (!Validador::texto($titulo, 60)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O título da qualificação é obrigatório (até 60 caracteres).'];
        } elseif (!$coordenador) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o coordenador da qualificação.'];
        } elseif ($acao === 'gravar' && !$codCampo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o campo a que a qualificação pertence.'];
        } elseif ($acao === 'gravar' && !$codNivel) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o nível da qualificação.'];
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

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::pode(Sessao::ACAO_CRIAR);
$pode_editar  = Sessao::pode(Sessao::ACAO_EDITAR);
$pode_remover = Sessao::pode(Sessao::ACAO_REMOVER);

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
                            <th>Campo</th>   <!-- NOVO -->
                            <th>Nível</th>   <!-- NOVO -->
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>                        <?php if (empty($qualificacoes)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 6 : 5 ?>" class="table-empty"> <!-- ALTERADO: colspan +2 -->
                                    Nenhuma qualificacao cadastrada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($qualificacoes as $qualificacao): ?>
                                <tr>
                                    <td class="col-codigo"><strong>#<?= htmlspecialchars((string) $qualificacao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($qualificacao->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars((string) $qualificacao->getCod_coordenador()) ?></td>
                                    <td><?= htmlspecialchars($qualificacao->getCampo() ?: '-') ?></td>   <!-- NOVO -->
                                    <td><?= htmlspecialchars($qualificacao->getNiveis() ?: '-') ?></td>   <!-- NOVO -->
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
                    <div class="form-group">
                        <label for="titulo-novo">Titulo <span class="required">*</span></label>
                        <input type="text" id="titulo-novo" name="titulo" class="form-control" maxlength="60" placeholder="Ex: Licenciatura em Engenharia Informatica" required>
                    </div>
                    <div class="form-group">
                        <label for="campo-novo">Campo / Área <span class="required">*</span></label>
                        <select id="campo-novo" name="campo" class="form-control" required>
                            <option value="">Selecione o campo...</option>
                            <?php foreach ($campos as $c): ?>
                                <option value="<?= (int) $c->getCodigo() ?>"><?= htmlspecialchars($c->getNome()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nivel-novo">Nível <span class="required">*</span></label>
                        <select id="nivel-novo" name="nivel" class="form-control" required>
                            <option value="">Selecione o nível...</option>
                            <?php foreach ($niveis as $n): ?>
                                <option value="<?= (int) $n->getCodigo() ?>"><?= htmlspecialchars($n->getNome()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="coordenador-novo">Coordenador <span class="required">*</span></label>
                        <select id="coordenador-novo" name="coordenador" class="form-control" required>
                            <option value="">Selecione o coordenador...</option>
                            <?php foreach ($coordenadores as $coordenador): ?>
                                <option value="<?= (int) $coordenador['codigo'] ?>"><?= htmlspecialchars($coordenador['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
        <?php if ($pode_editar): ?>
        <div id="modal-editar-<?= $qualificacao->getCodigo() ?>" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Editar Qualificacao</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $qualificacao->getCodigo()) ?>">
                        <div class="form-group">
                            <label for="titulo-e">Titulo <span class="required">*</span></label>
                            <input type="text" id="titulo-e" name="titulo" class="form-control" maxlength="60" value="<?= htmlspecialchars($qualificacao->getTitulo()) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="coordenador-e">Coordenador <span class="required">*</span></label>
                            <select id="coordenador-e" name="coordenador" class="form-control" required>
                                <option value="">Selecione o coordenador...</option>
                                <?php foreach ($coordenadores as $coordenador): ?>
                                    <option value="<?= (int) $coordenador['codigo'] ?>" <?= (int) $coordenador['codigo'] === (int) $qualificacao->getCod_coordenador() ? 'selected' : '' ?>><?= htmlspecialchars($coordenador['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
        <div id="modal-deletar-<?= $qualificacao->getCodigo() ?>" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header modal-header-danger">
                    <h3>Confirmar Exclusao de Registro</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $qualificacao->getCodigo()) ?>">
                        <p class="confirm-question">
                            Tem certeza que deseja eliminar permanentemente esta qualificacao?
                        </p>
                        <div class="confirm-detail-box">
                            <p><strong>Codigo:</strong> #<?= htmlspecialchars((string) $qualificacao->getCodigo()) ?></p>
                            <p><strong>Titulo:</strong> <?= htmlspecialchars($qualificacao->getTitulo()) ?></p>
                            <p><strong>Coordenador:</strong> #<?= htmlspecialchars((string) $qualificacao->getCod_coordenador()) ?></p>
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
