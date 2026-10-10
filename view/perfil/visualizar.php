<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Informacoes sobre o perfil: identidade + matriz de permissoes (apenas leitura).
// Nao existe descricao persistida na base de dados nem edicao neste ecra.
Sessao::exigirLeitura(Sessao::RECURSO_PERFIS, '../dashboard/index.php');

require_once __DIR__ . '/../../controller/PerfilController.php';

$controller = new PerfilController();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'ID de perfil invalido.'];
    header('Location: ../../view/perfil/index.php');
    exit;
}

$perfil = $controller->buscar($id);
if ($perfil === null) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Perfil nao encontrado.'];
    header('Location: ../../view/perfil/index.php');
    exit;
}

$recursos   = $controller->listarRecursos();
$permissoes = $controller->listarPermissoes();

// Ordem canonica de apresentacao: Criar, Consultar, Editar, Eliminar.
$ordem = ['criar', 'consultar', 'editar', 'eliminar'];
usort($permissoes, function ($a, $b) use ($ordem) {
    $ia = array_search($a->getNome(), $ordem, true);
    $ib = array_search($b->getNome(), $ordem, true);
    return ($ia === false ? PHP_INT_MAX : $ia) <=> ($ib === false ? PHP_INT_MAX : $ib);
});

// Concessoes do perfil: matriz efectiva e flag de "tem registos explicitos".
$concessoes = $controller->concessoes($perfil->getId());
$temConcessoes = !empty($concessoes);

$ativadas = [];
foreach ($concessoes as $c) {
    $ativadas[(int) $c['recurso_id']][(int) $c['permissao_id']] = true;
}

// Recursos agrupados por grupo (Academico, Administrativos, ...).
$grupos = [];
foreach ($recursos as $recurso) {
    $grupos[$recurso->getGrupo()][] = $recurso;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Informacoes sobre o Perfil';
$active_menu = 'perfil';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Informacoes sobre o Perfil</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <a href="../../view/perfil/index.php">Perfis</a>
            <span class="divider">/</span>
            <span>Informacoes</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h2>Identidade</h2></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="perfil-id">ID</label>
                    <input type="text" id="perfil-id" class="form-control"
                           value="<?= (int) $perfil->getId() ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="perfil-nome">Nome</label>
                    <input type="text" id="perfil-nome" class="form-control"
                           value="<?= htmlspecialchars($perfil->getNome()) ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Permissoes associadas</h2>
            <span class="badge-readonly">Apenas leitura</span>
        </div>
        <div class="card-body">
            <?php if (empty($temConcessoes)): ?>
                <p class="note">
                    Sem permissoes explicitas cadastradas para este perfil.
                    O sistema aplica o padrao definido para os perfis legados.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-custom permissao-matrix permissao-matrix-readonly">
                        <thead>
                            <tr>
                                <th class="col-recurso">Recurso</th>
                                <?php foreach ($permissoes as $p): ?>
                                    <th class="col-permissao"><?= htmlspecialchars(ucfirst($p->getNome())) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $grupo => $listaRecursos): ?>
                                <tr class="permissao-grupo">
                                    <td colspan="<?= 1 + count($permissoes) ?>">
                                        <span class="grupo-tag"><?= htmlspecialchars($grupo) ?></span>
                                    </td>
                                </tr>
                                <?php foreach ($listaRecursos as $recurso): ?>
                                    <tr>
                                        <td class="col-recurso"><?= htmlspecialchars($recurso->getNome()) ?></td>
                                        <?php foreach ($permissoes as $p): ?>
                                            <td>
                                                <?php if (isset($ativadas[(int) $recurso->getId()][(int) $p->getId()])): ?>
                                                    <span class="perm-sim">&#10003;</span>
                                                <?php else: ?>
                                                    <span class="perm-nao">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="note">
                    C = Criar &middot; R = Consultar &middot; U = Editar &middot; D = Eliminar.
                    Esta vista e apenas de leitura; a edicao faz-se em "Permissoes".
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-header">
            <a href="../../view/perfil/index.php" class="btn btn-secondary">Voltar a lista de perfis</a>
            <?php if (Sessao::podeEditar(Sessao::RECURSO_PERFIS)): ?>
                <a href="../../view/permissoes/index.php?perfil=<?= (int) $perfil->getId() ?>"
                   class="btn btn-edit">Gerir permissoes</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
