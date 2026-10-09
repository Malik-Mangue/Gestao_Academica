<?php
// Rota privada: exige autenticação (ESPECIFICACAO_AUTENTICACAO §7.1).
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/ModuloController.php';
require_once __DIR__ . '/../../controller/QualificacaoController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../model/Qualificacao.php';
require_once __DIR__ . '/../../model/Nivel.php';

$controller             = new ModuloController();
$qualificacaoController = new QualificacaoController();
$qualiNivelController   = new Quali_NivelController();
$semestres              = ['1º Semestre', '2º Semestre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar'
          : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        try {
            $sucesso = $controller->apagarModulo($codigo);
            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Módulo removido com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível remover o módulo.'];
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
        $nome     = trim($_POST['nome'] ?? '');
        $carga    = trim($_POST['carga_horaria'] ?? '');
        $codQuali = filter_input(INPUT_POST, 'codQuali', FILTER_VALIDATE_INT);
        $codNivel = filter_input(INPUT_POST, 'codNivel', FILTER_VALIDATE_INT);
        $semestre = trim($_POST['semestre'] ?? '');

        if (!Validador::texto($nome, 100)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O nome do módulo é obrigatório (até 100 caracteres).'];
        } elseif (!Validador::inteiroPositivo($carga)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A carga horária deve ser um número positivo.'];
        } elseif (!$codQuali || !$codNivel) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione a qualificação e o nível do módulo.'];
        } elseif (!in_array($semestre, $semestres, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione um semestre válido.'];
        } elseif ($acao === 'editar' && !$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Código inválido para atualização do módulo.'];
        } else {
            $qualificacao = new Qualificacao($codQuali, null, null);
            $nivel        = new Nivel($codNivel, null);

            // O nível escolhido tem de estar associado à qualificação (tabela Quali_Nivel).
            if (!$qualiNivelController->buscarCodigo($qualificacao, $nivel)) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A qualificação selecionada não tem o nível escolhido.'];
            } else {
                $sucesso = $acao === 'gravar'
                    ? $controller->cadastrarModulo($nome, $carga, $semestre, $qualificacao, $nivel)
                    : $controller->atualizarModulo($nome, $carga, $codigo, $qualificacao, $nivel, $semestre);

                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => $acao === 'gravar'
                        ? 'Módulo cadastrado com sucesso!' : 'Módulo atualizado com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível gravar o módulo. Verifique os dados.'];
            }
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa      = trim($_GET['pesquisa'] ?? '');
$qualificacoes = $qualificacaoController->comboQualificacao();
$niveis        = $qualiNivelController->listarNiveis();
$modulos       = $controller->listarModulo($pesquisa);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::pode(Sessao::ACAO_CRIAR);
$pode_editar  = Sessao::pode(Sessao::ACAO_EDITAR);
$pode_remover = Sessao::pode(Sessao::ACAO_REMOVER);

$page_title = 'Gestão de Módulos';
$active_menu = 'modulo';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Módulos</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Módulos</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Módulos</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Novo Módulo</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Nome do módulo"
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
                            <th>Carga Horária</th>
                            <th>Qualificação</th>
                            <th>Nível</th>
                            <th>Semestre</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($modulos)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 7 : 6 ?>" class="table-empty">
                                    Nenhum módulo cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($modulos as $modulo): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string) $modulo->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($modulo->getNome()) ?></td>
                                    <td><?= htmlspecialchars((string) $modulo->getCargaHoraria()) ?></td>
                                    <td><?= htmlspecialchars($modulo->getQualiNivel()->getQualificacao()->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars($modulo->getQualiNivel()->getNivel()->getNome()) ?></td>
                                    <td><?= htmlspecialchars($modulo->getQualiModulo()->getSemestre() ?? '') ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $modulo->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $modulo->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $modulo->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Módulo #<?= htmlspecialchars((string) $modulo->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $modulo->getCodigo()) ?>">
                                                    <?php $sufixo = 'editar-' . $modulo->getCodigo(); ?>
                                                    <div class="form-grid">
                                                        <div class="form-group">
                                                            <label for="nome-<?= $sufixo ?>">Nome do Módulo <span class="required">*</span></label>
                                                            <input type="text" id="nome-<?= $sufixo ?>" name="nome" class="form-control"
                                                                   maxlength="100" placeholder="Ex: Programação Web"
                                                                   value="<?= htmlspecialchars($modulo->getNome()) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="carga-<?= $sufixo ?>">Carga Horária <span class="required">*</span></label>
                                                            <input type="number" id="carga-<?= $sufixo ?>" name="carga_horaria" class="form-control"
                                                                   min="1" placeholder="Ex: 60"
                                                                   value="<?= htmlspecialchars((string) $modulo->getCargaHoraria()) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="quali-<?= $sufixo ?>">Qualificação <span class="required">*</span></label>
                                                            <select id="quali-<?= $sufixo ?>" name="codQuali" class="form-control" required>
                                                                <option value="">Selecione a qualificação...</option>
                                                                <?php foreach ($qualificacoes as $q): ?>
                                                                    <option value="<?= (int) $q->getCodigo() ?>"
                                                                        <?= (int) $modulo->getQualiNivel()->getCod_quali() === (int) $q->getCodigo() ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($q->getTitulo()) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="nivel-<?= $sufixo ?>">Nível <span class="required">*</span></label>
                                                            <select id="nivel-<?= $sufixo ?>" name="codNivel" class="form-control" required>
                                                                <option value="">Selecione o nível...</option>
                                                                <?php foreach ($niveis as $n): ?>
                                                                    <option value="<?= (int) $n['codigo'] ?>"
                                                                        <?= (int) $modulo->getQualiNivel()->getCod_nivel() === (int) $n['codigo'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($n['descricao']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="semestre-<?= $sufixo ?>">Semestre <span class="required">*</span></label>
                                                            <select id="semestre-<?= $sufixo ?>" name="semestre" class="form-control" required>
                                                                <option value="">Selecione o semestre...</option>
                                                                <?php foreach ($semestres as $s): ?>
                                                                    <option value="<?= htmlspecialchars($s) ?>"
                                                                        <?= ($modulo->getQualiModulo()->getSemestre() ?? '') === $s ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($s) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
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
                                    <div id="modal-deletar-<?= $modulo->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars((string) $modulo->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente este módulo?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars((string) $modulo->getCodigo()) ?></p>
                                                        <p><strong>Nome:</strong> <?= htmlspecialchars($modulo->getNome()) ?></p>
                                                        <p><strong>Carga Horária:</strong> <?= htmlspecialchars((string) $modulo->getCargaHoraria()) ?></p>
                                                        <p><strong>Qualificação:</strong> <?= htmlspecialchars($modulo->getQualiNivel()->getQualificacao()->getTitulo()) ?></p>
                                                        <p><strong>Nível:</strong> <?= htmlspecialchars($modulo->getQualiNivel()->getNivel()->getNome()) ?></p>
                                                        <p><strong>Semestre:</strong> <?= htmlspecialchars($modulo->getQualiModulo()->getSemestre() ?? '') ?></p>
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
                    <h3>Cadastrar Novo Módulo</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome-novo">Nome do Módulo <span class="required">*</span></label>
                                <input type="text" id="nome-novo" name="nome" class="form-control"
                                       maxlength="100" placeholder="Ex: Programação Web" required>
                            </div>
                            <div class="form-group">
                                <label for="carga-novo">Carga Horária <span class="required">*</span></label>
                                <input type="number" id="carga-novo" name="carga_horaria" class="form-control"
                                       min="1" placeholder="Ex: 60" required>
                            </div>
                            <div class="form-group">
                                <label for="quali-novo">Qualificação <span class="required">*</span></label>
                                <select id="quali-novo" name="codQuali" class="form-control" required>
                                    <option value="">Selecione a qualificação...</option>
                                    <?php foreach ($qualificacoes as $q): ?>
                                        <option value="<?= (int) $q->getCodigo() ?>"><?= htmlspecialchars($q->getTitulo()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nivel-novo">Nível <span class="required">*</span></label>
                                <select id="nivel-novo" name="codNivel" class="form-control" required>
                                    <option value="">Selecione o nível...</option>
                                    <?php foreach ($niveis as $n): ?>
                                        <option value="<?= (int) $n['codigo'] ?>"><?= htmlspecialchars($n['descricao']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semestre-novo">Semestre <span class="required">*</span></label>
                                <select id="semestre-novo" name="semestre" class="form-control" required>
                                    <option value="">Selecione o semestre...</option>
                                    <?php foreach ($semestres as $s): ?>
                                        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Módulo</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
