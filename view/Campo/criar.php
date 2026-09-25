<?php
require_once __DIR__ . '/../../controller/CampoController.php';
$controller = new CampoController();

if (isset($_POST['gravar'])) {
    $controller->store();
    header('Location: index.php');
    exit;
}
if (isset($_POST['cancelar'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Campo</title>
</head>
<body>
    <h3>Cadastro de novo Campo</h3>
    <form method="post">
        <table>
            <tr>
                <td>Nome:</td>
                <td><input type="text" name="nome" style="width:300px" required /></td>
            </tr>
            <tr>
                <td><input type="submit" value="Gravar" name="gravar" /></td>
                <td><input type="submit" value="Cancelar" name="cancelar" /></td>
            </tr>
        </table>
    </form>
</body>
</html>
