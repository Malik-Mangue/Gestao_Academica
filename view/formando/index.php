<?php
require_once __DIR__ . '/../../controller/FormandoController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FormandoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        $nome = trim($_POST['nome'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bi = trim($_POST['bi'] ?? '');
        if (empty($nome) || empty($apelido) || empty($bi)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Os campos nome, apelido e nº do BI não podem estar vazios.'
            ];
        } elseif (strlen($nome) > 100 || strlen($apelido) > 100 || strlen($email) > 100 || strlen($bi) > 20) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível gravar o registo. Nome/apelido até 100, e-mail até 100 e nº do BI até 20 caracteres.'
            ];
        } elseif ($contacto !== '' && !ctype_digit($contacto)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'O contacto deve conter apenas dígitos.'
            ];
        } elseif ($controller->store()) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Formando cadastrado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível gravar o registo.'
            ];
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['editar'])) {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bi = trim($_POST['bi'] ?? '');
        if (!$codigo || empty($nome) || empty($apelido) || empty($bi)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Dados inválidos para atualização do formando.'
            ];
        } elseif (strlen($nome) > 100 || strlen($apelido) > 100 || strlen($email) > 100 || strlen($bi) > 20) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível atualizar o registo. Nome/apelido até 100, e-mail até 100 e nº do BI até 20 caracteres.'
            ];
        } elseif ($contacto !== '' && !ctype_digit($contacto)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'O contacto deve conter apenas dígitos.'
            ];
        } elseif ($controller->update($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Formando atualizado com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível atualizar o registo.'
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
                'msg' => 'Formando removido com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não é possível eliminar: existem matrículas ou inscrições associadas a este formando.'
            ];
        }
        header('Location: index.php');
        exit;
    }
}

$formandos = $controller->listar();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestão de Formandos';
$active_menu = 'formando';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Formandos</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Formandos</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Formandos</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Novo Formando</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Nome</th>
                            <th>Apelido</th>
                            <th>Contacto</th>
                            <th>E-mail</th>
                            <th>Nº do BI</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($formandos)): ?>
                            <tr>
                                <td colspan="7" class="table-empty">
                                    Nenhum formando cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($formandos as $formando): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($formando->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($formando->getNome()) ?></td>
                                    <td><?= htmlspecialchars($formando->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($formando->getContacto() ?? '-') ?></td>
                                    <td><?= htmlspecialchars($formando->getEmail() ?? '-') ?></td>
                                    <td><?= htmlspecialchars($formando->getBi()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= $formando->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= $formando->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                        </div>
                                    </td>
                                </tr>

                                <div id="modal-editar-<?= $formando->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header">
                                            <h3>Editar Formando #<?= htmlspecialchars($formando->getCodigo()) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($formando->getCodigo()) ?>">
                                                <div class="form-group">
                                                    <label for="nome-<?= $formando->getCodigo() ?>">Nome <span class="required">*</span></label>
                                                    <input type="text" id="nome-<?= $formando->getCodigo() ?>" name="nome" class="form-control" value="<?= htmlspecialchars($formando->getNome()) ?>" maxlength="100" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="apelido-<?= $formando->getCodigo() ?>">Apelido <span class="required">*</span></label>
                                                    <input type="text" id="apelido-<?= $formando->getCodigo() ?>" name="apelido" class="form-control" value="<?= htmlspecialchars($formando->getApelido()) ?>" maxlength="100" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="contacto-<?= $formando->getCodigo() ?>">Contacto</label>
                                                    <input type="number" id="contacto-<?= $formando->getCodigo() ?>" name="contacto" class="form-control" min="0" value="<?= htmlspecialchars($formando->getContacto() ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-<?= $formando->getCodigo() ?>">E-mail</label>
                                                    <input type="email" id="email-<?= $formando->getCodigo() ?>" name="email" class="form-control" value="<?= htmlspecialchars($formando->getEmail() ?? '') ?>" maxlength="100">
                                                </div>
                                                <div class="form-group">
                                                    <label for="bi-<?= $formando->getCodigo() ?>">Nº do BI <span class="required">*</span></label>
                                                    <input type="text" id="bi-<?= $formando->getCodigo() ?>" name="bi" class="form-control" value="<?= htmlspecialchars($formando->getBi()) ?>" maxlength="20" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="modal-deletar-<?= $formando->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header modal-header-danger">
                                            <h3>Confirmar Exclusão de Registro</h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($formando->getCodigo()) ?>">
                                                <p class="confirm-question">
                                                    Tem certeza que deseja eliminar permanentemente este registro?
                                                </p>
                                                <div class="confirm-detail-box">
                                                    <p>
                                                        <strong>Código:</strong> #<?= htmlspecialchars($formando->getCodigo()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nome:</strong> <?= htmlspecialchars($formando->getNome()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Apelido:</strong> <?= htmlspecialchars($formando->getApelido()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nº do BI:</strong> <?= htmlspecialchars($formando->getBi()) ?>
                                                    </p>
                                                </div>
                                                <p class="confirm-warning">
                                                    Atenção: Esta ação não poderá ser desfeita e pode afetar matrículas e inscrições associadas a este formando.
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
                <h3>Cadastrar Novo Formando</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="novo-nome">Nome <span class="required">*</span></label>
                        <input type="text" id="novo-nome" name="nome" class="form-control" placeholder="Ex: João" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="novo-apelido">Apelido <span class="required">*</span></label>
                        <input type="text" id="novo-apelido" name="apelido" class="form-control" placeholder="Ex: Silva" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="novo-contacto">Contacto</label>
                        <input type="number" id="novo-contacto" name="contacto" class="form-control" min="0" placeholder="Ex: 912345678">
                    </div>
                    <div class="form-group">
                        <label for="novo-email">E-mail</label>
                        <input type="email" id="novo-email" name="email" class="form-control" placeholder="Ex: joao.silva@email.com" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="novo-bi">Nº do BI <span class="required">*</span></label>
                        <input type="text" id="novo-bi" name="bi" class="form-control" placeholder="Ex: 001234567LA042" maxlength="20" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Formando</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
