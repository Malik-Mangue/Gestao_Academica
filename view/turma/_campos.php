<?php
// Campos partilhados entre criar e editar uma turma.
$v = function ($getter) use ($turma) {
    return $turma !== null ? (string) $turma->$getter() : '';
};
?>
<div class="form-grid">
    <div class="form-group">
        <label for="n-<?= $sufixo ?>">Nome da Turma <span class="required">*</span></label>
        <input type="text" id="n-<?= $sufixo ?>" name="nome" class="form-control"
               placeholder="Ex: Turma A" maxlength="40"
               value="<?= htmlspecialchars($v('getNome')) ?>" required>
    </div>
    <div class="form-group">
        <label for="a-<?= $sufixo ?>">Ano Lectivo <span class="required">*</span></label>
        <input type="number" id="a-<?= $sufixo ?>" name="ano_lectivo" class="form-control"
               min="2000" max="2100" placeholder="Ex: 2026"
               value="<?= htmlspecialchars($v('getAnoIngresso')) ?>" required>
    </div>
    <div class="form-group">
        <label for="tn-<?= $sufixo ?>">Turno <span class="required">*</span></label>
        <select id="tn-<?= $sufixo ?>" name="turno" class="form-control" required>
            <?php foreach ($turnos as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"
                    <?= ($turma !== null && $turma->getTurno() === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="d-<?= $sufixo ?>">Diretor de Turma <span class="required">*</span></label>
        <select id="d-<?= $sufixo ?>" name="codDiretor" class="form-control" required>
            <?php foreach ($diretores as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= ($turma !== null && (int) $turma->getDiretorTurma()->getFormador()->getCodigo() === (int) $op['codigo']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="qn-<?= $sufixo ?>">Qualificação / Nível <span class="required">*</span></label>
        <select id="qn-<?= $sufixo ?>" name="qualiNivel" class="form-control" required>
            <?php foreach ($qualiNiveis as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= ($turma !== null && (int) $turma->getQualiNivel()->getCodigo() === (int) $op['codigo']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>