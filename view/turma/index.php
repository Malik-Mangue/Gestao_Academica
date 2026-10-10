<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/TurmaController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../model/Formador.php';
require_once __DIR__ . '/../../model/Diretor_turma.php';
require_once __DIR__ . '/../../model/Qualificacao.php';
require_once __DIR__ . '/../../model/Nivel.php';

$controller = new TurmaController();

// Ajusta estes valores aos turnos usados na tua base de dados.
$turnos = ['Manhã', 'Tarde', 'Noite'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // O controller revalida o perfil: a view esconde os botoes nao permitidos,
    // mas quem salta a interface continua a ser barrado aqui.
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

        $codigo     = $acao === 'editar' ? filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT) : null;
        $nome       = trim($_POST['nome'] ?? '');
        $anoLectivo = $_POST['ano_lectivo'] ?? '';
        $turno      = trim($_POST['turno'] ?? '');
        $codDiretor = filter_input(INPUT_POST, 'codDiretor', FILTER_VALIDATE_INT);
        $codQuali   = filter_input(INPUT_POST, 'qualificacao', FILTER_VALIDATE_INT);
        $codNivel   = filter_input(INPUT_POST, 'nivel', FILTER_VALIDATE_INT);

        if ($acao === 'editar' && !$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Turma inválida para atualização.'];
        } elseif (!Validador::texto($nome, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O nome da turma é obrigatório (até 40 caracteres).'];
        } elseif (!Validador::inteiroPositivo($anoLectivo)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O ano lectivo deve ser um número positivo.'];
        } elseif (!in_array($turno, $turnos, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione um turno válido.'];
        } elseif (!$codDiretor) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o diretor de turma.'];
        } elseif (!$codQuali) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione a qualificação da turma.'];
        } elseif (!$codNivel) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o nível da turma.'];
        } else {
            $formador     = new Formador($codDiretor, null, null, null, null, null, null, null, null, null);
            $diretor      = new Diretor_turma($formador);
            $qualificacao = new Qualificacao($codQuali, null, null);
            $nivel        = new Nivel($codNivel, null);

            // O nivel escolhido tem de estar associado a qualificacao (tabela Quali_Nivel).
            if (!(new Quali_NivelController())->buscarCodigo($qualificacao, $nivel)) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A qualificação selecionada não tem o nível escolhido.'];
            } else {
                try {
                    $sucesso = $acao === 'gravar'
                        ? $controller->cadastrarTurma($nome, (int) $anoLectivo, $turno, $diretor, $qualificacao, $nivel)
                        : $controller->atualizarTurma($codigo, $nome, (int) $anoLectivo, $turno, $diretor, $qualificacao, $nivel);

                    $_SESSION['flash'] = $sucesso
                        ? ['type' => 'success', 'msg' => $acao === 'gravar' ? 'Turma cadastrada com sucesso!' : 'Turma atualizada com sucesso!']
                        : ['type' => 'danger', 'msg' => 'Não foi possível gravar a turma.'];
                } catch (Exception $e) {
                    $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
                }
            }
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');

$turmas        = $controller->listarTurma($pesquisa);
$diretores     = $controller->listarDiretores();
$qualificacoes = $controller->listarQualificacoes();
$niveis        = $controller->listarNiveis();

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
                            <th>Nível</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($turmas)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 8 : 7 ?>" class="table-empty">
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
                                                    require __DIR__ . '/_campos.php';
                                                    ?>
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
                        $turma = null;
                        $sufixo = 'novo';
                        require __DIR__ . '/_campos.php';
                        ?>
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
