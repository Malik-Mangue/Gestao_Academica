<?php
require_once __DIR__ . '/../../controller/NivelController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new NivelController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        $nome = trim($_POST['nome'] ?? '');
        if (empty($nome)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'O nome do nível não pode estar vazio.'
            ];
        } elseif ($controller->store()) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Nível cadastrado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível gravar o registo. O nome deve ter no máximo 60 caracteres.'
            ];
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['editar'])) {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        if (!$codigo || empty($nome)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Dados inválidos para atualização do nível.'
            ];
        } elseif ($controller->update($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Nível atualizado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível atualizar o registo. O nome deve ter no máximo 60 caracteres.'
            ];
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['deletar'])) {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        if (!$codigo) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Código inválido para remoção.'
            ];
        } elseif ($controller->delete($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Nível removido com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não é possível eliminar: existem qualificações associadas a este nível.'
            ];
        }
        header('Location: index.php');
        exit;
    }
}

$niveis = $controller->listar();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestão de Níveis de Formação';
$active_menu = 'nivel';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Níveis de Formação</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Níveis de Formação</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Níveis de Formação</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Novo Nível</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Nome do Nível</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($niveis)): ?>
                            <tr>
                                <td colspan="3" class="table-empty">
                                    Nenhum nível de formação cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($niveis as $nivel): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($nivel->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($nivel->getNome()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= $nivel->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= $nivel->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                        </div>
                                    </td>
                                </tr>

                                <div id="modal-editar-<?= $nivel->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header">
                                            <h3>Editar Nível #<?= htmlspecialchars($nivel->getCodigo()) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($nivel->getCodigo()) ?>">
                                                <div class="form-group">
                                                    <label for="nome-<?= $nivel->getCodigo() ?>">Nome do Nível <span class="required">*</span></label>
                                                    <input type="text" id="nome-<?= $nivel->getCodigo() ?>" name="nome" class="form-control" value="<?= htmlspecialchars($nivel->getNome()) ?>" maxlength="60" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="modal-deletar-<?= $nivel->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header modal-header-danger">
                                            <h3>Confirmar Exclusão de Registro</h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($nivel->getCodigo()) ?>">
                                                <p class="confirm-question">
                                                    Tem certeza que deseja eliminar permanentemente este registro?
                                                </p>
                                                <div class="confirm-detail-box">
                                                    <p>
                                                        <strong>Código:</strong> #<?= htmlspecialchars($nivel->getCodigo()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nome do Nível:</strong> <?= htmlspecialchars($nivel->getNome()) ?>
                                                    </p>
                                                </div>
                                                <p class="confirm-warning">
                                                    Atenção: Esta ação não poderá ser desfeita e pode afetar qualificações associadas a este nível.
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" name="deletar" class="btn btn-delete">Sim, Deletar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modal-novo" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Cadastrar Novo Nível de Formação</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="novo-nome-nivel">Nome do Nível <span class="required">*</span></label>
                        <input type="text" id="novo-nome-nivel" name="nome" class="form-control" placeholder="Ex: CV3 - Nível Vocacional 3" maxlength="60" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Nível</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
