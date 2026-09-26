<?php
require_once __DIR__ . '/../../controller/SalaController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new SalaController();

$tipos_sala = ['Teórica', 'Laboratório', 'Oficina', 'Manutenção'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        $designacao = trim($_POST['designacao'] ?? '');
        $tipo_sala = trim($_POST['tipo_sala'] ?? '');
        if (empty($designacao) || empty($tipo_sala)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'A designação e o tipo da sala não podem estar vazios.'
            ];
        } elseif (strlen($designacao) > 20 || strlen($tipo_sala) > 20) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível gravar o registo. A designação e o tipo devem ter no máximo 20 caracteres.'
            ];
        } elseif ($controller->store()) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Sala cadastrada com sucesso!'
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
        $designacao = trim($_POST['designacao'] ?? '');
        $tipo_sala = trim($_POST['tipo_sala'] ?? '');
        if (!$codigo || empty($designacao) || empty($tipo_sala)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Dados inválidos para atualização da sala.'
            ];
        } elseif (strlen($designacao) > 20 || strlen($tipo_sala) > 20) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível atualizar o registo. A designação e o tipo devem ter no máximo 20 caracteres.'
            ];
        } elseif ($controller->update($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Sala atualizada com sucesso!'
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
                'msg' => 'Sala removida com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível eliminar o registo.'
            ];
        }
        header('Location: index.php');
        exit;
    }
}

$salas = $controller->listar();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestão de Salas';
$active_menu = 'sala';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Salas de Formação</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Salas</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Salas</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Nova Sala</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Designação</th>
                            <th>Tipo de Sala</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salas)): ?>
                            <tr>
                                <td colspan="4" class="table-empty">
                                    Nenhuma sala cadastrada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salas as $sala): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($sala->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($sala->getDesignacao()) ?></td>
                                    <td><?= htmlspecialchars($sala->getTipo_sala()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= $sala->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= $sala->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                        </div>
                                    </td>
                                </tr>

                                <div id="modal-editar-<?= $sala->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header">
                                            <h3>Editar Sala #<?= htmlspecialchars($sala->getCodigo()) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($sala->getCodigo()) ?>">
                                                <div class="form-group">
                                                    <label for="designacao-<?= $sala->getCodigo() ?>">Designação da Sala <span class="required">*</span></label>
                                                    <input type="text" id="designacao-<?= $sala->getCodigo() ?>" name="designacao" class="form-control" value="<?= htmlspecialchars($sala->getDesignacao()) ?>" maxlength="20" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tipo-<?= $sala->getCodigo() ?>">Tipo de Sala <span class="required">*</span></label>
                                                    <select id="tipo-<?= $sala->getCodigo() ?>" name="tipo_sala" class="form-control" required>
                                                        <?php foreach ($tipos_sala as $tipo): ?>
                                                            <option value="<?= htmlspecialchars($tipo) ?>" <?= $sala->getTipo_sala() === $tipo ? 'selected' : '' ?>><?= htmlspecialchars($tipo) ?></option>
                                                        <?php endforeach; ?>
                                                        <?php if (!in_array($sala->getTipo_sala(), $tipos_sala, true)): ?>
                                                            <option value="<?= htmlspecialchars($sala->getTipo_sala()) ?>" selected><?= htmlspecialchars($sala->getTipo_sala()) ?></option>
                                                        <?php endif; ?>
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

                                <div id="modal-deletar-<?= $sala->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header modal-header-danger">
                                            <h3>Confirmar Exclusão de Registro</h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($sala->getCodigo()) ?>">
                                                <p class="confirm-question">
                                                    Tem certeza que deseja eliminar permanentemente este registro?
                                                </p>
                                                <div class="confirm-detail-box">
                                                    <p>
                                                        <strong>Código:</strong> #<?= htmlspecialchars($sala->getCodigo()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Designação da Sala:</strong> <?= htmlspecialchars($sala->getDesignacao()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Tipo de Sala:</strong> <?= htmlspecialchars($sala->getTipo_sala()) ?>
                                                    </p>
                                                </div>
                                                <p class="confirm-warning">
                                                    Atenção: Esta ação não poderá ser desfeita.
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
                <h3>Cadastrar Nova Sala</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nova-designacao">Designação da Sala <span class="required">*</span></label>
                        <input type="text" id="nova-designacao" name="designacao" class="form-control" placeholder="Ex: Sala 01" maxlength="20" required>
                    </div>
                    <div class="form-group">
                        <label for="novo-tipo">Tipo de Sala <span class="required">*</span></label>
                        <select id="novo-tipo" name="tipo_sala" class="form-control" required>
                            <?php foreach ($tipos_sala as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Sala</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
