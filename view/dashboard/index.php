<?php
// if (!isset($_SESSION['user_id'])) {
//     header('Location: /login');
//     exit;
// }
require_once __DIR__ . '/../../controller/FormandoController.php'; 
$FormandoController = new FormandoController();

$formandos = $FormandoController->listar();

$page_title = 'Dashboard Geral';
$active_menu = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';

?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Dashboard Control Panel</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="divider">/</span>
            <span>Dashboard</span>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card card-cyan">
            <div class="stat-card-inner">
                <div>
                    
                    <div class="stat-number">24</div>
                    <div class="stat-label">Professores Cadastrados</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/professor.svg" alt="Professores">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Visualizar Corpo Docente</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-green">
            <div class="stat-card-inner">
                <div>
                    <?php if(!empty($formandos)): ?>
                    
                    <div class="stat-number"><?= count($formandos) ?></div>


                    <?php endif; ?>

                    <div class="stat-label">Formandos Ativos</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/formando.svg" alt="Formandos">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Ver Matrículas e Alunos</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-orange">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number">18</div>
                    <div class="stat-label">Turmas em Andamento</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/turma.svg" alt="Turmas">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Turmas</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-red">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number">42</div>
                    <div class="stat-label">Módulos Curriculares</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/modulo.svg" alt="Módulos">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Estrutura de Disciplinas</span>
                <span>&rarr;</span>
            </div>
        </div>
    </div>

   

    <div id="modal-info" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Sobre o Sistema de Gestão Acadêmica</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <div class="modal-body">
                <p><strong>Arquitetura:</strong> MVC Puro em PHP 8.</p>
                <p><strong>Estilização:</strong> CSS3 Puro em estilo global e modular.</p>
                <p><strong>Navegação:</strong> Menu lateral direto sem rotas amigáveis.</p>
                <p><strong>Persistência:</strong> MySQLi com Prepared Statements.</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-secondary">Fechar</a>
            </div>
        </div>
    </div>

    <div id="modal-novo-exemplo" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Demonstração de Modal Pop-up</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <div class="modal-body">
                <p>Este pop-up opera nativamente via CSS puro através do seletor target.</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary">Entendido</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'?>