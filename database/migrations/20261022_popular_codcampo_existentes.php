<?php
/**
 * Migração: 20261022_popular_codcampo_existentes
 *
 * Objectivo: Popular codCampo nos registos existentes de Formando e Formador
 * que ainda não têm este valor preenchido.
 *
 * Para Formando: deriva da qualificação da matrícula (via Classificacao)
 * Para Formador: deriva das qualificações que coordena (via Coordenador -> Qualificacao -> Classificacao)
 */

define('DOCUMENT_ROOT', __DIR__ . '/../..');

require_once __DIR__ . '/../../config/conexao.php';

$database = new Database();
$db = $database->getConnection();

echo "=== Migração 20261022_popular_codcampo_existentes ===\n\n";

// === 1. Formando: popular codCampo a partir da matrícula ===
echo "1. A popular codCampo em Formando...\n";

$sql_formando_popula = "
UPDATE `Formando` AS f
LEFT JOIN `Matricula` AS m ON m.cod_formando = f.codigo_formando
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = m.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;
";

$result = $db->query($sql_formando_popula);
$affected = $db->affected_rows;
echo "   Registos actualizados: $affected\n";

// Para formandos sem matrícula ou sem classificação, usar o primeiro campo disponível
$sql_formando_default = "
UPDATE `Formando`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;
";

$result = $db->query($sql_formando_default);
$affected = $db->affected_rows;
echo "   Registos com valor por omissão: $affected\n";

// === 2. Formador: popular codCampo a partir das qualificações que coordena ===
echo "\n2. A popular codCampo em Formador...\n";

$sql_formador_popula = "
UPDATE `Formador` AS f
LEFT JOIN `Coordenador` AS co ON co.cod_Formador = f.codigo
LEFT JOIN `Qualificacao` AS q ON q.cod_Coordenador = co.cod_Formador
LEFT JOIN `Classificacao` AS cl ON cl.cod_Qualificacao = q.cod_Quali
SET f.codCampo = cl.cod_Campo
WHERE f.codCampo IS NULL AND cl.cod_Campo IS NOT NULL;
";

$result = $db->query($sql_formador_popula);
$affected = $db->affected_rows;
echo "   Registos actualizados: $affected\n";

// Para formadores sem coordenação, usar o primeiro campo disponível
$sql_formador_default = "
UPDATE `Formador`
SET codCampo = (SELECT MIN(codigo) FROM `Campo`)
WHERE codCampo IS NULL;
";

$result = $db->query($sql_formador_default);
$affected = $db->affected_rows;
echo "   Registos com valor por omissão: $affected\n";

// === 3. Verificar resultados ===
echo "\n3. Verificação final:\n";

$sql_check_formando = "SELECT COUNT(*) as total, SUM(codCampo IS NULL) as nulos FROM Formando";
$rs = $db->query($sql_check_formando)->fetch_assoc();
echo "   Formando: Total={$rs['total']}, Sem codCampo={$rs['nulos']}\n";

$sql_check_formador = "SELECT COUNT(*) as total, SUM(codCampo IS NULL) as nulos FROM Formador";
$rs = $db->query($sql_check_formador)->fetch_assoc();
echo "   Formador: Total={$rs['total']}, Sem codCampo={$rs['nulos']}\n";

echo "\n=== Migração concluída ===\n";