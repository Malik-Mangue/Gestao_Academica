<?php
/**
 * Migração: 20261017_campo_em_formando_formador_e_categoria_modulo
 *
 * Objectivo: associar Formando e Formador a um Campo (área) e dar aos
 * Módulos uma categoria (Genérico / Vocacional).
 *
 * Passos:
 * 1. Formando: add coluna codCampo (nullable, temporariamente), preenche a
 *    partir da qualificação da matrícula (via Classificacao), garante um valor
 *    por omissão e só depois aplica NOT NULL + chave estrangeira.
 * 2. Formador: idêntico, mas a área é derivada da(s) qualificação(ões) que o
 *    formador coordena.
 * 3. Modulo: add coluna categoria (Genérico por omissão).
 *
 * NOTA: esta migração apenas documenta os PASSOS. Em produção, o
 * administrador deve executar os comandos SQL diretamente no MySQL, pela
 * ordem apresentada.
 */
defined('DOCUMENT_ROOT') or exit('Acesso directo directo não permitido.');

// === 1. Formando.codCampo ===
$sql_formando_add = "
ALTER TABLE `Formando`
    ADD COLUMN `codCampo` INT NULL AFTER `BI`;
";

$sql_formando_popula = "
UPDATE `Formando` AS f
LEFT JOIN `Matricula` AS m ON m.cod_formando = f.codigo_formando
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = m.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;
";

$sql_formando_default = "
UPDATE `Formando`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;
";

$sql_formando_final = "
ALTER TABLE `Formando`
    MODIFY COLUMN `codCampo` INT NOT NULL,
    ADD CONSTRAINT `Formando_codCampo_fk`
        FOREIGN KEY (`codCampo`) REFERENCES `Campo` (`codigo`);
";

// === 2. Formador.codCampo ===
$sql_formador_add = "
ALTER TABLE `Formador`
    ADD COLUMN `codCampo` INT NULL AFTER `horas_mes`;
";

$sql_formador_popula = "
UPDATE `Formador` AS f
LEFT JOIN `Coordenador` AS co ON co.cod_Formador = f.codigo
LEFT JOIN `Qualificacao` AS q ON q.cod_Coordenador = co.cod_Formador
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = q.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;
";

$sql_formador_default = "
UPDATE `Formador`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;
";

$sql_formador_final = "
ALTER TABLE `Formador`
    MODIFY COLUMN `codCampo` INT NOT NULL,
    ADD CONSTRAINT `Formador_codCampo_fk`
        FOREIGN KEY (`codCampo`) REFERENCES `Campo` (`codigo`);
";

// === 3. Modulo.categoria ===
$sql_modulo = "
ALTER TABLE `Modulo`
    ADD COLUMN `categoria` VARCHAR(20) NOT NULL DEFAULT 'Genérico' AFTER `carga_horaria`;
";

// Ordem de execução:
//   1. sql_formando_add -> sql_formando_popula -> sql_formando_default -> sql_formando_final
//   2. sql_formador_add -> sql_formador_popula -> sql_formador_default -> sql_formador_final
//   3. sql_modulo

echo "Migração 20261017_campo_em_formando_formador_e_categoria_modulo aplicada.\n";
