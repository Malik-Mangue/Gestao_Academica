<?php

require_once __DIR__ . '../../../controller/FormadorController.php';

$controller = new Formadorcontroller();
$formandores = $controller->listar();


$page_title = 'Gestão de Formador';
$active_menu = 'professor';
require_once __DIR__ . '../../partials/header.php';
require_once __DIR__ . '../../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Formadores</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Formadores</span>
        </div>
    </div>

    

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Formadores</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Novo Formador</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Nome</th>
                            <th>Apelido</th>
                            <th>Email</th>
                            <th>Genero</th>
                            <th>Estado Civil</th>
                            <th>Contacto</th>
                            <th>Valor por hora</th>
                            <th>horas de trabalho no mes</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($formadores)): ?>
                            <tr>
                                <td colspan="7" class="table-empty">
                                    Nenhum formando cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($formadores as $formador): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($formador->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($formador->getNome()) ?></td>
                                    <td><?= htmlspecialchars($formador->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($formador->getContacto() ?? '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getEmail() ?? '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getBi()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= $formador->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= $formador->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                        </div>
                                    </td>
                                </tr>

                                <div id="modal-editar-<?= $formador->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header">
                                            <h3>Editar formador #<?= htmlspecialchars($formador->getCodigo()) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($formador->getCodigo()) ?>">
                                                <div class="form-group">
                                                    <label for="nome-<?= $formador->getCodigo() ?>">Nome <span class="required">*</span></label>
                                                    <input type="text" id="nome-<?= $formador->getCodigo() ?>" name="nome" class="form-control" value="<?= htmlspecialchars($formador->getNome()) ?>" maxlength="100" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="apelido-<?= $formador->getCodigo() ?>">Apelido <span class="required">*</span></label>
                                                    <input type="text" id="apelido-<?= $formador->getCodigo() ?>" name="apelido" class="form-control" value="<?= htmlspecialchars($formador->getApelido()) ?>" maxlength="100" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="contacto-<?= $formador->getCodigo() ?>">Contacto</label>
                                                    <input type="number" id="contacto-<?= $formador->getCodigo() ?>" name="contacto" class="form-control" min="0" value="<?= htmlspecialchars($formador->getContacto() ?? '') ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email-<?= $formador->getCodigo() ?>">E-mail</label>
                                                    <input type="email" id="email-<?= $formador->getCodigo() ?>" name="email" class="form-control" value="<?= htmlspecialchars($formador->getEmail() ?? '') ?>" maxlength="100">
                                                </div>
                                                <div class="form-group">
                                                    <label for="bi-<?= $formador->getCodigo() ?>">Nº do BI <span class="required">*</span></label>
                                                    <input type="text" id="bi-<?= $formador->getCodigo() ?>" name="bi" class="form-control" value="<?= htmlspecialchars($formador->getBi()) ?>" maxlength="20" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#" class="btn btn-secondary">Cancelar</a>
                                                <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="modal-deletar-<?= $formador->getCodigo() ?>" class="modal-overlay">
                                    <div class="modal-box">
                                        <div class="modal-header modal-header-danger">
                                            <h3>Confirmar Exclusão de Registro</h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <form method="post" action="index.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($formador->getCodigo()) ?>">
                                                <p class="confirm-question">
                                                    Tem certeza que deseja eliminar permanentemente este registro?
                                                </p>
                                                <div class="confirm-detail-box">
                                                    <p>
                                                        <strong>Código:</strong> #<?= htmlspecialchars($formador->getCodigo()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nome:</strong> <?= htmlspecialchars($formador->getNome()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Apelido:</strong> <?= htmlspecialchars($formador->getApelido()) ?>
                                                    </p>
                                                    <p>
                                                        <strong>Nº do BI:</strong> <?= htmlspecialchars($formador->getBi()) ?>
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

<?php require_once __DIR__ . '../../partials/footer.php'; ?>