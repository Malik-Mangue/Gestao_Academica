<?php
$active_menu = 'campo';
require_once __DIR__ . '/../../controller/CampoController.php';
require_once __DIR__ . '../../partials/header.php';
require_once __DIR__ . '../../partials/sidebar.php';
$controller = new CampoController();
$campos = $controller->listar();
?>

    <div class="main-wrapper">
        
    </div>

<?php require_once __DIR__ . '/../partials/footer.php' ?>
