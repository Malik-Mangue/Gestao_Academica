<?php
// Campos partilhados entre o formulario de criar e o de editar um formador.
// $acao define o rotulo do botao; $formador (opcional) preenche os valores.
$acao    = $acao ?? 'Gravar';
$formador = $formador ?? null;
$v = function ($getter) use ($formador) {
    return $formador !== null ? (string) $formador->$getter() : '';
};
?>
<div class="form-group">
    <label for="f-nome-<?= $sufixo ?>">Nome <span class="required">*</span></label>
    <input type="text" id="f-nome-<?= $sufixo ?>" name="nome" class="form-control"
           placeholder="Ex: João" maxlength="40"
           value="<?= htmlspecialchars($v('getNome')) ?>" required>
</div>
<div class="form-group">
    <label for="f-apelido-<?= $sufixo ?>">Apelido <span class="required">*</span></label>
    <input type="text" id="f-apelido-<?= $sufixo ?>" name="apelido" class="form-control"
           placeholder="Ex: Silva" maxlength="40"
           value="<?= htmlspecialchars($v('getApelido')) ?>" required>
</div>
<div class="form-group">
    <label for="f-email-<?= $sufixo ?>">E-mail <span class="required">*</span></label>
    <input type="email" id="f-email-<?= $sufixo ?>" name="email" class="form-control"
           placeholder="Ex: joao.silva@email.com" maxlength="100"
           value="<?= htmlspecialchars($v('getEmail')) ?>" required>
</div>
<div class="form-group">
    <label for="f-genero-<?= $sufixo ?>">Género</label>
    <select id="f-genero-<?= $sufixo ?>" name="genero" class="form-control">
        <?php foreach ($generos as $genero): ?>
            <option value="<?= htmlspecialchars($genero) ?>"
                <?= ($formador !== null && $formador->getGenero() === $genero) ? 'selected' : '' ?>>
                <?= htmlspecialchars($genero) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="f-estadoCivil-<?= $sufixo ?>">Estado Civil</label>
    <select id="f-estadoCivil-<?= $sufixo ?>" name="estadoCivil" class="form-control">
        <?php foreach ($estados_civil as $estado): ?>
            <option value="<?= htmlspecialchars($estado) ?>"
                <?= ($formador !== null && $formador->getEstadoCivil() === $estado) ? 'selected' : '' ?>>
                <?= htmlspecialchars($estado) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="f-contacto-<?= $sufixo ?>">Contacto <span class="required">*</span></label>
    <input type="number" id="f-contacto-<?= $sufixo ?>" name="contacto" class="form-control"
           min="1" placeholder="Ex: 912345678"
           value="<?= htmlspecialchars($v('getContacto')) ?>" required>
</div>
<div class="form-group">
    <label for="f-valorHora-<?= $sufixo ?>">Valor por hora <span class="required">*</span></label>
    <input type="number" id="f-valorHora-<?= $sufixo ?>" name="valorHora" class="form-control"
           min="1" value="<?= htmlspecialchars($v('getValorHoras')) ?>" required>
</div>
<div class="form-group">
    <label for="f-horasMes-<?= $sufixo ?>">Horas por mês <span class="required">*</span></label>
    <input type="number" id="f-horasMes-<?= $sufixo ?>" name="horasMes" class="form-control"
           min="1" value="<?= htmlspecialchars($v('getHorasMes')) ?>" required>
</div>
<div class="form-group">
    <label for="f-salario-<?= $sufixo ?>">Salário</label>
    <input type="number" id="f-salario-<?= $sufixo ?>" name="salario" class="form-control"
           min="0" value="<?= htmlspecialchars($v('getSalario')) ?>">
</div>
<div class="form-group">
    <label for="f-funcao-<?= $sufixo ?>">Função</label>
    <?php
    $ehDiretor     = $formador !== null && $controller->getStatusFormador($formador->getCodigo())[0];
    $ehCoordenador = $formador !== null && $controller->getStatusFormador($formador->getCodigo())[1];
    ?>
    <div class="form-actions">
        <label>
            <input type="checkbox" name="isDiretor" value="1"
                <?= $ehDiretor ? 'checked' : '' ?>> Diretor de Turma
        </label>
        <label>
            <input type="checkbox" name="isCoordenador" value="1"
                <?= $ehCoordenador ? 'checked' : '' ?>> Coordenador
        </label>
    </div>
</div>