<?php
require_once __DIR__ . '/../../controller/CampoController.php';
$controller = new CampoController();

$codigo = $_GET['codigo'] ?? $_POST['codigo'];

if (isset($_POST['gravar'])) {
    $controller->atualizarCampo($_POST['codigo'], $_POST['nome']);
    header('Location: index.php');
    exit;
}
if (isset($_POST['cancelar'])) {
    header('Location: index.php');
    exit;
}

$campo = $controller->buscarCampo($codigo);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Campo</title>
</head>
<body>
    <h3>Editar Campo</h3>
    <form method="post">
        <input type="hidden" name="codigo" value="<?= $campo->getCodigo() ?>" />
        <table>
            <tr>
                <td>Nome:</td>
                <td><input type="text" name="nome" value="<?= $campo->getNome() ?>" /></td>
            </tr>
            <tr>
                <td><input type="submit" value="Editar" name="gravar" /></td>
                <td><input type="submit" value="Cancelar" name="cancelar" /></td>
            </tr>
        </table>
    </form>
</body>
</html>
