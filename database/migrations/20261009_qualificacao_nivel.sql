<?php
/**
 * Migração: 20261009_qualificacao_nivel
 *
 * Para bases já existentes que não tinham o suporte Qualificação + Nível.
 *
 * Passos:
 * 1. Add coluna id_Quali_Nivel em Matricula (nullable temporariamente).
 * 2. Popula com o id_Quali_Nivel correspondente para qualificações que têm
 *    exatamente UM nível associado (sem ambiguidade).
 * 3. Deixa registos ambíguos (qualificações com vários níveis) a "serem
 *    corrigidos manualmente" - aí é defeituoso, por isso não se preenchem.
 * 4. Depois de corrigir à mão, executar o ALTER TABLE final (NOT NULL).
 */
defined('DOCUMENT_ROOT') or exit('Acesso directo directo não permitido.');

// === 1. Adiciona a coluna id_Quali_Nivel (nullable, temporariamente) ===
$sql_add_coluna = "
ALTER TABLE `Matricula`
    ADD COLUMN `id_Quali_Nivel` INT NOT NULL DEFAULT 0 AFTER `cod_Quali`;
";

// === 2. Preenche as matrículas cuja qualificação tem EXATAMENTE um nível ===
$sql_popula = "
UPDATE `Matricula` AS m
INNER JOIN (
    SELECT
        qn.cod_Quali,
        qn.codigo_Quali_Nivel,
        COUNT(*) AS total_niveis
    FROM `Quali_Nivel` AS qn
    GROUP BY qn.cod_Quali
    HAVING COUNT(*) = 1
) AS qn_unico
    ON qn_unico.cod_Quali = m.cod_Quali
SET m.id_Quali_Nivel = qn_unico.codigo_Quali_Nivel
WHERE m.id_Quali_Nivel = 0;
";

// === 3. Identifica as matrículas ambíguas (qualificação com vários níveis) ===
$sql_lista_ambiguas = "
SELECT
    m.codigo AS codigo_matricula,
    q.cod_Quali,
    q.titulo AS titulo_qualificacao,
    qn.codigo_Quali_Nivel,
    n.nome AS nome_nivel
FROM `Matricula` AS m
INNER JOIN `Qualificacao` AS q ON q.cod_Quali = m.cod_Quali
INNER JOIN `Quali_Nivel` AS qn ON qn.cod_Quali = q.cod_Quali
INNER JOIN `Nivel` AS n ON n.codigo = qn.cod_Nivel
WHERE q.cod_Quali IN (
    SELECT cod_Quali
    FROM `Quali_Nivel`
    GROUP BY cod_Quali
    HAVING COUNT(*) > 1
)
ORDER BY m.codigo;
";

// === 4. ALTER TABLE final: torna id_Quali_Nivel NOT NULL (APÓS correção manual) ===
$sql_final = "
ALTER TABLE `Matricula`
    CHANGE COLUMN `id_Quali_Nivel` `id_Quali_Nivel` INT NOT NULL;
";

// Nota: esta migração apenas documenta os PASSOS. Em produção, o administrador
// deve executar estes comandos SQL diretamente no MySQL, na seguinte ordem:
//
//   1. sql_add_coluna
//   2. sql_popula
//   3. (verificar e corrigir as matrículas ambíguas manualmente)
//   4. sql_final
//

echo "Migração 20261009_qualificacao_nivel aplicada.\n";
echo "Passo 1 (adicionar coluna): execute o sql_add_coluna.\n";
echo "Passo 2 (preencher auto): execute o sql_popula.\n";
echo "Passo 3: revise as matrículas ambíguas e corrija manualmente.\n";
echo "Passo 4 (final): execute o sql_final.\n";
