<?php
/**
 * Campos partilhados entre o formulario de criar e o de editar uma turma.
 *
 * @var string $sufixo        'novo' ao criar, 'editar-<codigo>' ao editar
 * @var Turma|null $turma     Turma a editar (null ao criar)
 * @var array $turnos         Lista de turnos
 * @var array $diretores      Opcoes [codigo, descricao] de diretores de turma
 * @var array $qualificacoes  Opcoes [codigo, descricao] de qualificacoes
 * @var array $niveis         Opcoes [codigo, descricao] de niveis
 */
$turma = $turma ?? null;

$nomeAtual = $turma !== null ? (string) $turma->getNome() : '';
$anoAtual = $turma !== null ? (string) $turma->getAnoIngresso() : '';
$turnoAtual = $turma !== null ? (string) $turma->getTurno() : '';
$codDiretorAtual = ($turma !== null && $turma->getDiretorTurma() !== null && $turma->getDiretorTurma()->getFormador() !== null)
    ? (string) $turma->getDiretorTurma()->getFormador()->getCodigo() : '';

// A qualificacao e o nivel da turma vem do Quali_Nivel (entidade associativa).
$qn = $turma !== null ? $turma->getQualiNivel() : null;
$qualificacaoAtual = ($qn !== null && $qn->getCod_quali() !== null) ? (string) $qn->getCod_quali() : '';
$nivelAtual = ($qn !== null && $qn->getCod_nivel() !== null) ? (string) $qn->getCod_nivel() : '';
?>
<div class="form-group">
    <label for="t-nome-<?= $sufixo ?>">Nome da turma <span class="required">*</span></label>
    <input type="text" id="t-nome-<?= $sufixo ?>" name="nome" class="form-control"
           placeholder="Ex: TI-2025-A" maxlength="40"
           value="<?= htmlspecialchars($nomeAtual) ?>" required>
</div>
<div class="form-group">
    <label for="t-ano-<?= $sufixo ?>">Ano lectivo <span class="required">*</span></label>
    <input type="number" id="t-ano-<?= $sufixo ?>" name="ano_lectivo" class="form-control"
           min="1" placeholder="Ex: 2025"
           value="<?= htmlspecialchars($anoAtual) ?>" required>
</div>
<div class="form-group">
    <label for="t-turno-<?= $sufixo ?>">Turno <span class="required">*</span></label>
    <select id="t-turno-<?= $sufixo ?>" name="turno" class="form-control" required>
        <option value="">Selecione...</option>
        <?php foreach ($turnos as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $t === $turnoAtual ? 'selected' : '' ?>>
                <?= htmlspecialchars($t) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="t-diretor-<?= $sufixo ?>">Diretor de Turma <span class="required">*</span></label>
    <select id="t-diretor-<?= $sufixo ?>" name="codDiretor" class="form-control" required>
        <option value="">Selecione...</option>
        <?php foreach ($diretores as $d): ?>
            <option value="<?= htmlspecialchars((string) $d['codigo']) ?>"
                <?= (string) $d['codigo'] === $codDiretorAtual ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="t-qualificacao-<?= $sufixo ?>">Qualificação <span class="required">*</span></label>
    <select id="t-qualificacao-<?= $sufixo ?>" name="qualificacao" class="form-control" required>
        <option value="">Selecione...</option>
        <?php foreach ($qualificacoes as $q): ?>
            <option value="<?= htmlspecialchars((string) $q['codigo']) ?>"
                <?= (string) $q['codigo'] === $qualificacaoAtual ? 'selected' : '' ?>>
                <?= htmlspecialchars($q['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="t-nivel-<?= $sufixo ?>">Nível <span class="required">*</span></label>
    <select id="t-nivel-<?= $sufixo ?>" name="nivel" class="form-control" required>
        <option value="">Selecione...</option>
        <?php foreach ($niveis as $n): ?>
            <option value="<?= htmlspecialchars((string) $n['codigo']) ?>"
                <?= (string) $n['codigo'] === $nivelAtual ? 'selected' : '' ?>>
                <?= htmlspecialchars($n['descricao']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
