<?php
$page_title = 'Dashboard Geral';
$active_menu = 'dashboard';
$screen_css = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
$deep = substr_count(trim($_SERVER['SCRIPT_NAME'], '/'), '/');
$rel_root = str_repeat('../', max(0, $deep - 1));
if (empty($rel_root)) {
    $rel_root = './';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/screens/dashboard.css">
</head>
<body>
    
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
                        <img src="<?php echo $rel_root; ?>assets/icons/professor.svg" alt="Professores">
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
                        <div class="stat-number">150</div>
                        <div class="stat-label">Formandos Ativos</div>
                    </div>
                    <div class="stat-icon">
                        <img src="<?php echo $rel_root; ?>assets/icons/formando.svg" alt="Formandos">
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
                        <img src="<?php echo $rel_root; ?>assets/icons/turma.svg" alt="Turmas">
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
                        <img src="<?php echo $rel_root; ?>assets/icons/modulo.svg" alt="Módulos">
                    </div>
                </div>
                <div class="stat-card-footer">
                    <span>Estrutura de Disciplinas</span>
                    <span>&rarr;</span>
                </div>
            </div>
        </div>
    
        <div class="card">
            <div class="card-header">
                <h2>Acesso Rápido e Operações</h2>
                <a href="#modal-info" class="btn btn-primary btn-sm">+ Informações do Sistema</a>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 16px; color: var(--text-secondary);">
                    Bem-vindo ao Sistema de Gestão Acadêmica. Utilize o menu lateral para navegar entre todos os módulos do sistema. Todas as telas seguem a identidade visual padronizada do projeto.
                </p>
    
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="../professor/index.php" class="btn btn-primary">Gerir Professores</a>
                    <a href="../turma/index.php" class="btn btn-success">Gerir Turmas</a>
                    <a href="../campo/index.php" class="btn btn-edit">Campos e Áreas</a>
                    <a href="#modal-novo-exemplo" class="btn btn-secondary">Abrir Modal de Teste</a>
                </div>
            </div>
        </div>
    
        <div id="modal-info" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Sobre o Sistema de Gestão Acadêmica</h3>
                    <a href="#" class="modal-close">
                        <img src="<?php echo $rel_root; ?>assets/icons/close.svg" alt="Fechar">
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
                        <img src="<?php echo $rel_root; ?>assets/icons/close.svg" alt="Fechar">
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
    
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
