<?php
require_once __DIR__ . '/../../controller/CampoController.php';
$controller = new CampoController();

if (isset($_POST['codigo'])) {
    $controller->delete($_POST['codigo']);
}
header('Location: index.php');
exit;
?>
