<?php
require_once __DIR__ . '/../../controller/CampoController.php';
$controller = new CampoController();
$campos = $controller->listar();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lista de Campos</title>
</head>
<body>
    <h3>Lista de Campos</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Opção</th>
                <th><a href="criar.php">+</a></th>
            </tr>
        </thead>
        <?php if (count($campos) == 0): ?>
            <tr><td colspan="4">Nenhum campo cadastrado</td></tr>
        <?php else: foreach ($campos as $campo): ?>
            <tr>
                <td><?= $campo->getCodigo() ?></td>
                <td><?= $campo->getNome() ?></td>
                <td colspan="2">
                    <form method="post" action="deletar.php">
                        <input type="hidden" name="codigo" value="<?= $campo->getCodigo() ?>" />
                        <button type="submit">Remover</button>
                    </form>
                    <a href="editar.php?codigo=<?= $campo->getCodigo() ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </table>
</body>
</html>
