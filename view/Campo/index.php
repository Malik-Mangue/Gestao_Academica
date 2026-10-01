<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/CampoController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$controller = new CampoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        $nome = trim($_POST['nome'] ?? '');
        if (empty($nome)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'O nome do campo não pode estar vazio.'
            ];
        } elseif ($controller->cadastrarCampo($nome)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Campo cadastrado com sucesso!'
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
                'msg' => 'Dados inválidos para atualização do campo.'
            ];
        } elseif ($controller->atualizarCampo($codigo, $nome)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Campo atualizado com sucesso!'
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
        } elseif ($controller->apagarCampo($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Campo removido com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não é possível eliminar: existem registos associados a este campo.'
            ];
        }
        header('Location: index.php');
        exit;
    }
}

$campos = $controller->listarCampo();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestão de Campos / Áreas';
$active_menu = 'campo';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Áreas de Formação / Campos</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Campos</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Áreas de Formação</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Novo Campo</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Nome do Campo / Área</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campos)): ?>
                            <tr>
                                <td colspan="3" class="table-empty">
                                    Nenhum campo ou área cadastrada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($campos as $campo): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($campo->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($campo->getNome()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= $campo->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= $campo->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                        </div>
                                    </td>
                                </tr>

                                <div id="modal-editar-<?= $campo->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header">
                                            <h3>Editar Campo #<?= htmlspecialchars($campo->getCodigo()) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($campo->getCodigo()) ?>">
                                                <div class="form-group">
                                                    <label for="nome-<?= $campo->getCodigo() ?>">Nome do Campo <span class="required">*</span></label>
                                                    <input type="text" id="nome-<?= $campo->getCodigo() ?>" name="nome" class="form-control" value="<?= htmlspecialchars($campo->getNome()) ?>" maxlength="60" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="modal-deletar-<?= $campo->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header modal-header-danger">
                                            <h3>Confirmar Exclusão de Registro</h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($campo->getCodigo()) ?>">
                                                <p class="confirm-question">
                                                    Tem certeza que deseja eliminar permanentemente este registro?
                                                </p>
                                                <div class="confirm-detail-box">
                                                    <p>
                                                        <strong>Código:</strong> #<?= htmlspecialchars($campo->getCodigo()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nome do Campo:</strong> <?= htmlspecialchars($campo->getNome()) ?>
                                                    </p>
                                                </div>
                                                <p class="confirm-warning">
                                                    Atenção: Esta ação não poderá ser desfeita e pode afetar qualificações associadas a esta área.
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
                <h3>Cadastrar Nova Área de Formação</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="novo-nome-campo">Nome do Campo / Área <span class="required">*</span></label>
                        <input type="text" id="novo-nome-campo" name="nome" class="form-control" placeholder="Ex: Tecnologias de Informação e Comunicação" maxlength="60" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Campo</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
