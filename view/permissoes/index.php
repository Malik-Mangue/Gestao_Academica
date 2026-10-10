<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Gestao de permissoes: restricao por recurso (exclusiva do Administrador).
Sessao::exigirLeitura(Sessao::RECURSO_PERFIS, '../dashboard/index.php');

require_once __DIR__ . '/../../Dao/PerfilDao.php';
require_once __DIR__ . '/../../Dao/RecursoDao.php';
require_once __DIR__ . '/../../Dao/PermissaoDao.php';
require_once __DIR__ . '/../../Dao/PerfilPermissaoDao.php';

$perfilDao   = new PerfilDao();
$recursoDao  = new RecursoDao();
$permissaoDao= new PermissaoDao();
$ppDao       = new PerfilPermissaoDao();

$perfis = $perfilDao->getAll();
$recursos = $recursoDao->getAll();

// Ordem canonica das acoes: Criar, Consultar, Editar, Eliminar.
$permissoes_ordem = [
    ['id' => 2, 'nome' => 'Criar'],
    ['id' => 1, 'nome' => 'Consultar'],
    ['id' => 3, 'nome' => 'Editar'],
    ['id' => 4, 'nome' => 'Eliminar'],
];

// Determina o perfil em exibicao.
$idPerfil = filter_input(INPUT_GET, 'perfil', FILTER_VALIDATE_INT);
if (!$idPerfil && $perfis) {
    $idPerfil = (int) $perfis[0]->getId();
}

$perfilNome = '';
$concessoes = [];
if ($idPerfil) {
    $perfilNome = $perfilDao->getById($idPerfil)->getNome();
    $concessoes = $ppDao->getConcessoesByPerfil($idPerfil);
}

// Estado actual das concessoes (recurso_id => permissao_id => true).
$ativadas = [];
foreach ($concessoes as $c) {
    $ativadas[(int) $c['recurso_id']][(int) $c['permissao_id']] = true;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// -----------------------------------------------------------------------
// LOGICA DE GUARDADO PENDENTE (a tua logica, futuramente).
// Quando existir, aqui se grava $_POST['permissoes'][$recurso_id][$permissao_id]
// noutra table (perfil_permissao) e, de seguida, chama
// Sessao::recarregarPermissoes() para actualizar a sessao.
// -----------------------------------------------------------------------

$page_title = 'Gestao de Permissoes';
$active_menu = 'perfil';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Gestao de Permissoes</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a><span class="divider">/</span><span>Permissoes</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Selecionar Perfil</h2>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="perfil">Perfil</label>
                    <select id="perfil" name="perfil" class="form-control">
                        <?php foreach ($perfis as $p): ?>
                            <option value="<?= (int) $p->getId() ?>"
                                <?= $idPerfil === (int) $p->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->getNome()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Seleccionar</button>
                <?php if ($idPerfil): ?>
                    <a href="../../view/perfil/index.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($idPerfil): ?>
        <div class="card">
            <div class="card-header">
                <h2>Permissoes de <?= htmlspecialchars($perfilNome) ?></h2>
                <div class="table-actions table-actions-end">
                    <a href="../../view/perfil/visualizar.php?id=<?= (int) $idPerfil ?>"
                       class="btn btn-sm btn-edit">Ver descricao</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-custom permissao-matrix">
                        <thead>
                            <tr>
                                <th class="col-recurso">Recurso</th>
                                <?php foreach ($permissoes_ordem as $p): ?>
                                    <th class="col-permissao"><?= htmlspecialchars($p['nome']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recursos as $recurso): ?>
                                <tr>
                                    <td class="col-recurso"><?= htmlspecialchars($recurso->getNome()) ?></td>
                                    <?php foreach ($permissoes_ordem as $p): ?>
                                        <td>
                                            <input type="checkbox"
                                                   name="permissoes[<?= (int) $recurso->getId() ?>][<?= (int) $p['id'] ?>]"
                                                   value="1"
                                                   <?= isset($ativadas[(int) $recurso->getId()][(int) $p['id']]) ? 'checked' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="note">
                    C = Criar &middot; R = Consultar &middot; U = Editar &middot; D = Eliminar.
                    Perfis sem registos em perfil_permissao usam o padrao dos perfis legados.
                </p>

                <div class="card-footer">
                    <form method="post" action="index.php" style="display:inline;">
                        <button type="submit" name="guardar" class="btn btn-success">Guardar</button>
                    </form>
                    <a href="?perfil=<?= (int) $idPerfil ?>" class="btn btn-secondary">Fazer novo</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
