<?php
$active = $active_menu ?? 'dashboard';
$deep = substr_count(trim($_SERVER['SCRIPT_NAME'], '/'), '/');
$rel_root = str_repeat('../', max(0, $deep - 1));
if (empty($rel_root)) {
    $rel_root = './';
}
?>
<aside class="sidebar">
    <div class="sidebar-user-panel">
        <div class="sidebar-avatar">
            <img src="<?php echo $rel_root; ?>assets/icons/user.svg" alt="User">
        </div>
        <div class="sidebar-user-greeting">Welcome!</div>
        <div class="sidebar-user-name">admin</div>
    </div>

    <div class="sidebar-heading">Menu Principal</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/dashboard/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/dashboard.svg" alt="Dashboard">
                </span>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-heading">Corpo Docente</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'professor' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/professor/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/professor.svg" alt="Professores">
                </span>
                <span>Professores / Formadores</span>
            </a>
        </li>
        <li class="<?php echo $active === 'licao' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/licao/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/licao.svg" alt="Lições">
                </span>
                <span>Diário de Lições</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-heading">Gestão Acadêmica</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'turma' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/turma/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/turma.svg" alt="Turmas">
                </span>
                <span>Turmas</span>
            </a>
        </li>
        <li class="<?php echo $active === 'formando' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/formando/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/formando.svg" alt="Formandos">
                </span>
                <span>Formandos</span>
            </a>
        </li>
        <li class="<?php echo $active === 'matricula' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/matricula/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/matricula.svg" alt="Matrículas">
                </span>
                <span>Matrículas</span>
            </a>
        </li>
        <li class="<?php echo $active === 'inscricao' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/inscricao/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/inscricao.svg" alt="Inscrições">
                </span>
                <span>Inscrições em Módulos</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-heading">Estrutura Curricular</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'modulo' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/modulo/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/modulo.svg" alt="Módulos">
                </span>
                <span>Módulos</span>
            </a>
        </li>
        <li class="<?php echo $active === 'qualificacao' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/qualificacao/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/qualificacao.svg" alt="Qualificações">
                </span>
                <span>Qualificações</span>
            </a>
        </li>
        <li class="<?php echo $active === 'nivel' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/nivel/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/nivel.svg" alt="Níveis">
                </span>
                <span>Níveis de Formação</span>
            </a>
        </li>
        <li class="<?php echo $active === 'campo' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>Gestao_Academica/view/campo/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/campo.svg" alt="Campos">
                </span>
                <span>Campos / Áreas</span>
            </a>
        </li>
        <li class="<?php echo $active === 'sala' ? 'active' : ''; ?>">
            <a href="<?php echo $rel_root; ?>view/sala/index.php">
                <span class="sidebar-icon">
                    <img src="<?php echo $rel_root; ?>assets/icons/sala.svg" alt="Salas">
                </span>
                <span>Salas</span>
            </a>
        </li>
    </ul>
</aside>
