<?php
// Campos partilhados entre criar e editar um horário (lição).
// $licao opcional preenche os valores; $sufixo distingue os ids dos campos.
$v = function ($getter) use ($licao) {
    return $licao !== null ? (string) $licao->$getter() : '';
};
$sel = function ($lista, $atual) {
    return (int) $atual;
};
?>
<div class="form-grid">
    <div class="form-group">
        <label for="m-<?= $sufixo ?>">Módulo <span class="required">*</span></label>
        <select id="m-<?= $sufixo ?>" name="codModulo" class="form-control" required>
            <?php foreach ($modulos as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= $licao !== null && $sel($op['codigo'], $licao->getModulo()->getCodigo()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="f-<?= $sufixo ?>">Formador <span class="required">*</span></label>
        <select id="f-<?= $sufixo ?>" name="codFormador" class="form-control" required>
            <?php foreach ($formadores as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= $licao !== null && $sel($op['codigo'], $licao->getFormador()->getCodigo()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="s-<?= $sufixo ?>">Sala <span class="required">*</span></label>
        <select id="s-<?= $sufixo ?>" name="codSala" class="form-control" required>
            <?php foreach ($salas as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= $licao !== null && $sel($op['codigo'], $licao->getSala()->getCodigo()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="t-<?= $sufixo ?>">Turma <span class="required">*</span></label>
        <select id="t-<?= $sufixo ?>" name="codTurma" class="form-control" required>
            <?php foreach ($turmas as $op): ?>
                <option value="<?= (int) $op['codigo'] ?>"
                    <?= $licao !== null && $sel($op['codigo'], $licao->getTurma()->getCodigo()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($op['descricao']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="d-<?= $sufixo ?>">Data <span class="required">*</span></label>
        <input type="date" id="d-<?= $sufixo ?>" name="data" class="form-control"
               value="<?= htmlspecialchars($v('getData')) ?>" required>
    </div>
    <div class="form-group">
        <label for="hi-<?= $sufixo ?>">Hora de início <span class="required">*</span></label>
        <input type="time" id="hi-<?= $sufixo ?>" name="hora_inicio" class="form-control"
               value="<?= htmlspecialchars($v('getHoraInicio')) ?>" required>
    </div>
    <div class="form-group">
        <label for="hf-<?= $sufixo ?>">Hora de fim <span class="required">*</span></label>
        <input type="time" id="hf-<?= $sufixo ?>" name="hora_fim" class="form-control"
               value="<?= htmlspecialchars($v('getHoraFim')) ?>" required>
    </div>
</div>