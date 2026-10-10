<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');

require_once __DIR__ . '/../../controller/DashboardController.php';

$dashboard = new DashboardController();

// Todos os numeros sao contados na base de dados (nunca fixos no codigo).
$resumo = $dashboard->resumo();

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
                    <div class="stat-number"><?= (int) $resumo['formadores'] ?></div>
                    <div class="stat-label">Formadores Cadastrados</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/professor.svg" alt="Formadores">
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
                    <div class="stat-number"><?= (int) $resumo['formandos'] ?></div>
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
                    <div class="stat-number"><?= (int) $resumo['turmas'] ?></div>
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
                    <div class="stat-number"><?= (int) $resumo['modulos'] ?></div>
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
<div class="stat-grid">
        <div class="stat-card card-cyan">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['licoes'] ?></div>
                    <div class="stat-label">Lições / Horários</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/licao.svg" alt="Lições">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Horários</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-green">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['matriculas'] ?></div>
                    <div class="stat-label">Matrículas Registadas</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/matricula.svg" alt="Matrículas">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Matrículas</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-orange">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['inscricoes'] ?></div>
                    <div class="stat-label">Inscrições em Módulos</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/inscricao.svg" alt="Inscrições">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Inscrições</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-red">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['qualificacoes'] ?></div>
                    <div class="stat-label">Qualificações</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/qualificacao.svg" alt="Qualificações">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Estrutura Curricular</span>
                <span>&rarr;</span>
            </div>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card card-cyan">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['niveis'] ?></div>
                    <div class="stat-label">Níveis de Formação</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/nivel.svg" alt="Níveis">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Níveis</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-green">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['campos'] ?></div>
                    <div class="stat-label">Campos de Formação</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/campo.svg" alt="Campos">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Campos</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-orange">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['salas'] ?></div>
                    <div class="stat-label">Salas</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/sala.svg" alt="Salas">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Gerir Salas</span>
                <span>&rarr;</span>
            </div>
        </div>

        <div class="stat-card card-red">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-number"><?= (int) $resumo['utilizadores'] ?></div>
                    <div class="stat-label">Utilizadores</div>
                </div>
                <div class="stat-icon">
                    <img src="../../assets/icons/usuario.svg" alt="Utilizadores">
                </div>
            </div>
            <div class="stat-card-footer">
                <span>Contas do Sistema</span>
                <span>&rarr;</span>
            </div>
        </div>
    </div>

   

    </main>

<?php require_once __DIR__ . '/../partials/footer.php'?>