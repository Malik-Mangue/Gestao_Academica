-- Migração: 20261022_popular_codcampo_existentes
--
-- Objectivo: Popular codCampo nos registos existentes de Formando e Formador
-- que ainda não têm este valor preenchido.
--
-- Para Formando: deriva da qualificação da matrícula (via Classificacao)
-- Para Formador: deriva das qualificações que coordena (via Coordenador -> Qualificacao -> Classificacao)

USE `GestaoProfessor`;

-- === 1. Formando: popular codCampo a partir da matrícula ===
SELECT '1. A popular codCampo em Formando...' AS status;

UPDATE `Formando` AS f
LEFT JOIN `Matricula` AS m ON m.cod_formando = f.codigo_formando
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = m.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;

SELECT ROW_COUNT() AS registos_actualizados_formando;

-- Para formandos sem matrícula ou sem classificação, usar o primeiro campo disponível
UPDATE `Formando`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;

SELECT ROW_COUNT() AS registos_com_valor_omissao_formando;

-- === 2. Formador: popular codCampo a partir das qualificações que coordena ===
SELECT '2. A popular codCampo em Formador...' AS status;

UPDATE `Formador` AS f
LEFT JOIN `Coordenador` AS co ON co.cod_Formador = f.codigo
LEFT JOIN `Qualificacao` AS q ON q.cod_Coordenador = co.cod_Formador
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = q.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;

SELECT ROW_COUNT() AS registos_actualizados_formador;

-- Para formadores sem coordenação, usar o primeiro campo disponível
UPDATE `Formador`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;

SELECT ROW_COUNT() AS registos_com_valor_omissao_formador;

-- === 3. Verificar resultados ===
SELECT '3. Verificação final:' AS status;

SELECT 'Formando' AS tabela, COUNT(*) AS total, SUM(codCampo IS NULL) AS nulos FROM Formando
UNION ALL
SELECT 'Formador' AS tabela, COUNT(*) AS total, SUM(codCampo IS NULL) AS nulos FROM Formador;

SELECT 'Migração concluída' AS status;