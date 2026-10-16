<?php
/**
 * view/formador/ver.php — Ficha completa do formador
 *
 * Como ligar aos dados reais: o controlador define estas variáveis antes de
 * incluir esta vista. Se não existirem, usa dados de exemplo (para pré-visualizar).
 *   $formador, $turmasDirigidas, $coordenacoes, $modulos, $licoes, $logs
 * Ajuste $rotas aos caminhos reais do projecto.
 */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$seg = fn(int $d) => date('Y-m-d', strtotime("monday this week +$d day"));

$formador ??= ['id' => 7, 'nome' => 'Ana Paula', 'apelido' => 'Matsinhe', 'bi' => '110100234567B',
    'contacto' => '+258 84 123 4567', 'email' => 'ana.matsinhe@instituto.ac.mz', 'salario' => 31250.00];
$turmasDirigidas ??= [
    ['id' => 3, 'nome' => 'INF-1A', 'ano' => 2026, 'turno' => 'Manhã', 'qualificacao' => 'Informática de Gestão', 'nivel' => 'Nível 3', 'formandos' => 28],
    ['id' => 9, 'nome' => 'INF-2B', 'ano' => 2026, 'turno' => 'Noite', 'qualificacao' => 'Informática de Gestão', 'nivel' => 'Nível 4', 'formandos' => 22]];
$coordenacoes ??= [
    ['id' => 2, 'qualificacao' => 'Informática de Gestão', 'campo' => 'Tecnologias de Informação', 'niveis' => ['Nível 3', 'Nível 4']]];
$modulos ??= [
    ['nome' => 'Programação Web', 'qualificacao' => 'Informática de Gestão', 'nivel' => 'Nível 3', 'turmas' => ['INF-1A'], 'carga' => 90],
    ['nome' => 'Bases de Dados', 'qualificacao' => 'Informática de Gestão', 'nivel' => 'Nível 4', 'turmas' => ['INF-2B', 'INF-2A'], 'carga' => 80],
    ['nome' => 'Análise de Sistemas', 'qualificacao' => 'Informática de Gestão', 'nivel' => 'Nível 4', 'turmas' => ['INF-2B'], 'carga' => 60]];
$licoes ??= [
    ['data' => $seg(0), 'inicio' => '08:00', 'fim' => '10:00', 'modulo' => 'Programação Web', 'turma' => 'INF-1A', 'sala' => 'Lab 2'],
    ['data' => $seg(1), 'inicio' => '08:00', 'fim' => '09:30', 'modulo' => 'Programação Web', 'turma' => 'INF-1A', 'sala' => 'Lab 2'],
    ['data' => $seg(2), 'inicio' => '18:00', 'fim' => '20:00', 'modulo' => 'Bases de Dados', 'turma' => 'INF-2B', 'sala' => 'Sala 5'],
    ['data' => $seg(3), 'inicio' => '18:00', 'fim' => '20:00', 'modulo' => 'Análise de Sistemas', 'turma' => 'INF-2B', 'sala' => 'Sala 5'],
    ['data' => $seg(4), 'inicio' => '10:30', 'fim' => '12:00', 'modulo' => 'Bases de Dados', 'turma' => 'INF-2A', 'sala' => 'Lab 1']];
$logs ??= [
    ['quando' => '2026-10-12 09:14', 'utilizador' => 'admin', 'accao' => 'Atribuiu a direcção da turma INF-2B'],
    ['quando' => '2026-09-30 15:02', 'utilizador' => 'secretaria', 'accao' => 'Actualizou o contacto'],
    ['quando' => '2026-09-02 11:40', 'utilizador' => 'admin', 'accao' => 'Registou o formador']];

$id = (int)$formador['id'];
$rotas = [
    'lista'    => 'index.php?pagina=formadores',
    'editar'   => "index.php?pagina=formador_editar&id=$id",
    'rapido'   => "index.php?pagina=formador_contacto&id=$id",
    'eliminar' => "index.php?pagina=formador_eliminar&id=$id",
    'licao'    => "index.php?pagina=licao_nova&formador=$id",
    'dirigir'  => "index.php?pagina=turmas&diretor=$id",
    'coord'    => "index.php?pagina=coordenadores&formador=$id",
    'turma'    => 'index.php?pagina=turma_ver&id=',
];
$csrf ??= '';

$nomeCompleto = trim($formador['nome'] . ' ' . $formador['apelido']);
$iniciais = mb_strtoupper(mb_substr($formador['nome'], 0, 1) . mb_substr($formador['apelido'], 0, 1));
$dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
$semana = array_fill(0, 6, []);
$minutos = 0;
foreach ($licoes as $l) {
    $t = strtotime($l['data']);
    $i = (int)date('N', $t) - 1;
    if ($i < 6 && date('o-W', $t) === date('o-W')) {
        $semana[$i][] = $l;
        $minutos += (strtotime($l['fim']) - strtotime($l['inicio'])) / 60;
    }
}
foreach ($semana as &$d) usort($d, fn($a, $b) => strcmp($a['inicio'], $b['inicio']));
unset($d);
$horas = rtrim(rtrim(number_format($minutos / 60, 1, ',', ''), '0'), ',');
$nTurmas = count(array_unique(array_merge(...array_map(fn($m) => $m['turmas'], $modulos ?: [[ 'turmas' => [] ]]))));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($nomeCompleto) ?> — Formador</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@500;700;800&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#E9EEEB; --sheet:#FFFFFF; --ink:#16222B; --muted:#5A6670; --line:#D3DBD7;
  --green:#0F5A46; --green-soft:#DDEBE4; --ochre:#8A5A00; --ochre-soft:#F6E8C8;
  --red:#A12B2B; --red-soft:#F7E1E1; --focus:#1B6FD1;
  --head:'Schibsted Grotesk',system-ui,sans-serif; --body:'Atkinson Hyperlegible',system-ui,sans-serif;
}
@media (prefers-color-scheme: dark){
  :root{--bg:#10171A; --sheet:#182226; --ink:#E7EEEA; --muted:#9BA9A3; --line:#2B3A3F;
    --green:#5FBF9F; --green-soft:#1C3A31; --ochre:#E8B85A; --ochre-soft:#3A2F14;
    --red:#F08A8A; --red-soft:#3C1E1E; --focus:#7DB2F5}
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:400 1rem/1.55 var(--body)}
h1,h2,h3{font-family:var(--head);margin:0;line-height:1.15}
a{color:inherit}
:focus-visible{outline:3px solid var(--focus);outline-offset:2px}
.wrap{max-width:1180px;margin:0 auto;padding:1.25rem}
.back{display:inline-block;margin:0 0 .9rem;color:var(--muted);text-decoration:none}
.back:hover{color:var(--ink);text-decoration:underline}
.sheet{display:grid;grid-template-columns:300px 1fr;background:var(--sheet);border:1px solid var(--line);border-radius:6px;overflow:hidden}

/* ficha lateral */
.ficha{background:var(--green-soft);padding:1.5rem;border-right:1px solid var(--line)}
.sticky{position:sticky;top:1rem}
.mono{width:84px;height:84px;display:grid;place-items:center;background:var(--green);color:var(--sheet);
  font:800 2rem var(--head);letter-spacing:.02em;border-radius:4px;margin-bottom:1rem}
.ficha h1{font-size:1.6rem;font-weight:800}
.ficha .sub{color:var(--muted);margin:.25rem 0 1.25rem}
dl{margin:0}
dt{font-size:.85rem;color:var(--muted);margin-top:.85rem}
dd{margin:0;font-weight:700;overflow-wrap:anywhere}
.acoes{display:grid;gap:.5rem;margin-top:1.5rem}
.btn{font:700 .95rem var(--body);padding:.6rem .9rem;border-radius:4px;border:1.5px solid var(--ink);background:transparent;
  color:var(--ink);cursor:pointer;text-align:center;text-decoration:none;display:inline-block}
.btn:hover{background:var(--ink);color:var(--sheet)}
.btn.main{background:var(--green);border-color:var(--green);color:var(--sheet)}
.btn.main:hover{filter:brightness(1.1);background:var(--green);color:var(--sheet)}
.btn.danger{border-color:var(--red);color:var(--red)}
.btn.danger:hover{background:var(--red);color:var(--sheet)}
.btn.sm{padding:.3rem .65rem;font-size:.88rem}

/* conteúdo */
.main-col{padding:1.5rem 2rem 2rem;min-width:0}
.resumo{font:500 1.25rem/1.4 var(--head);max-width:46ch;margin:0 0 .75rem}
.tags{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
.tag{padding:.2rem .65rem;border-radius:3px;font-weight:700;font-size:.9rem}
.tag.dir{background:var(--ochre-soft);color:var(--ochre)}
.tag.coord{background:var(--green-soft);color:var(--green)}
section{border-top:2px solid var(--ink);padding-top:.9rem;margin-top:2rem}
.sec-h{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.6rem}
.sec-h h2{font-size:1.2rem;font-weight:700}
.sec-h p{margin:0;color:var(--muted)}
.scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:520px}
th{text-align:left;font-weight:700;color:var(--muted);font-size:.9rem;padding:.4rem .6rem .4rem 0;border-bottom:1px solid var(--line)}
td{padding:.65rem .6rem .65rem 0;border-bottom:1px solid var(--line);vertical-align:top}
td.num,th.num{text-align:right;padding-right:0}
.vazio{padding:1rem;background:var(--bg);border-radius:4px;margin:0}
.chips span{display:inline-block;background:var(--bg);padding:.05rem .5rem;border-radius:3px;margin:0 .3rem .2rem 0;font-size:.92rem}

/* semana: o elemento marcante */
.semana{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));border:1px solid var(--line);border-radius:4px;overflow:hidden}
.dia{border-right:1px solid var(--line);min-height:210px;display:flex;flex-direction:column}
.dia:last-child{border-right:0}
.dia h3{font-size:.95rem;padding:.5rem .6rem;background:var(--bg);border-bottom:1px solid var(--line)}
.dia.hoje h3{background:var(--green);color:var(--sheet)}
.bloco{margin:.5rem;padding:.45rem .55rem;border-left:5px solid var(--green);background:var(--green-soft);border-radius:2px;font-size:.88rem}
.bloco.noite{border-color:var(--ochre);background:var(--ochre-soft)}
.bloco b{display:block;font-family:var(--head);font-size:.95rem}
.livre{margin:auto;color:var(--muted);font-size:.88rem;padding:.5rem}
.legenda{color:var(--muted);font-size:.9rem;margin:.5rem 0 0}

ul.hist{list-style:none;margin:0;padding:0}
ul.hist li{display:grid;grid-template-columns:150px 1fr;gap:1rem;padding:.55rem 0;border-bottom:1px solid var(--line)}
ul.hist time{color:var(--muted)}

/* diálogos */
dialog{border:1px solid var(--line);border-radius:6px;background:var(--sheet);color:var(--ink);padding:1.5rem;max-width:min(420px,92vw)}
dialog::backdrop{background:rgba(10,18,20,.55)}
dialog h2{font-size:1.2rem;margin-bottom:.5rem}
label{display:block;font-weight:700;margin-top:.9rem}
input{width:100%;font:inherit;padding:.55rem .6rem;border:1.5px solid var(--muted);border-radius:4px;background:var(--sheet);color:var(--ink);margin-top:.25rem}
.dlg-btns{display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.25rem}

@media (max-width:900px){
  .sheet{grid-template-columns:1fr}
  .ficha{border-right:0;border-bottom:1px solid var(--line)}
  .sticky{position:static}
  .main-col{padding:1.25rem}
  .semana{grid-template-columns:1fr}
  .dia{min-height:0;border-right:0;border-bottom:1px solid var(--line)}
  ul.hist li{grid-template-columns:1fr;gap:0}
}
@media print{
  body{background:#fff;color:#000}
  .acoes,.btn,.back{display:none!important}
  .sheet{border:0;display:block}
  .ficha{background:#fff;border:0;padding:0 0 1rem}
  .sticky{position:static}
  .main-col{padding:0}
}
</style>
</head>
<body>
<div class="wrap">
  <a class="back" href="<?= e($rotas['lista']) ?>">Voltar à lista de formadores</a>

  <div class="sheet">
    <aside class="ficha">
      <div class="sticky">
        <div class="mono" aria-hidden="true"><?= e($iniciais) ?></div>
        <h1><?= e($nomeCompleto) ?></h1>
        <p class="sub">Formador n.º <?= $id ?></p>

        <dl>
          <dt>Contacto</dt><dd><?= e($formador['contacto']) ?></dd>
          <dt>E-mail</dt><dd><a href="mailto:<?= e($formador['email']) ?>"><?= e($formador['email']) ?></a></dd>
          <dt>Bilhete de identidade</dt><dd><?= e($formador['bi']) ?></dd>
          <dt>Salário calculado</dt><dd><?= number_format((float)$formador['salario'], 2, ',', '.') ?> MZN</dd>
          <dt>Horas esta semana</dt><dd><?= e($horas ?: '0') ?> h</dd>
        </dl>

        <div class="acoes">
          <a class="btn main" href="<?= e($rotas['editar']) ?>">Editar dados</a>
          <button class="btn" type="button" data-open="dlg-contacto">Alterar contactos</button>
          <a class="btn" href="<?= e($rotas['licao']) ?>">Marcar lição</a>
          <button class="btn" type="button" onclick="window.print()">Imprimir ficha</button>
          <button class="btn danger" type="button" data-open="dlg-eliminar">Eliminar formador</button>
        </div>
      </div>
    </aside>

    <main class="main-col">
      <p class="resumo">
        Leciona <?= count($modulos) ?> módulo<?= count($modulos) === 1 ? '' : 's' ?> em <?= $nTurmas ?> turma<?= $nTurmas === 1 ? '' : 's' ?>.
        <?= $turmasDirigidas ? 'Dirige ' . count($turmasDirigidas) . ' turma' . (count($turmasDirigidas) === 1 ? '' : 's') . '.' : 'Não dirige nenhuma turma.' ?>
        <?= $coordenacoes ? 'Coordena ' . count($coordenacoes) . ' qualificaç' . (count($coordenacoes) === 1 ? 'ão' : 'ões') . '.' : '' ?>
      </p>
      <div class="tags">
        <?php if ($turmasDirigidas): ?><span class="tag dir">Diretor de turma</span><?php endif; ?>
        <?php if ($coordenacoes): ?><span class="tag coord">Coordenador</span><?php endif; ?>
      </div>

      <section>
        <div class="sec-h">
          <h2>Semana de trabalho</h2>
          <a class="btn sm" href="<?= e($rotas['licao']) ?>">Marcar lição</a>
        </div>
        <div class="semana">
          <?php foreach ($dias as $i => $nome):
            $hoje = ((int)date('N') - 1) === $i; ?>
            <div class="dia<?= $hoje ? ' hoje' : '' ?>">
              <h3><?= e($nome) ?><?= $hoje ? ' (hoje)' : '' ?></h3>
              <?php if (!$semana[$i]): ?><p class="livre">Sem lições</p><?php endif; ?>
              <?php foreach ($semana[$i] as $l): ?>
                <div class="bloco<?= (int)substr($l['inicio'], 0, 2) >= 17 ? ' noite' : '' ?>">
                  <b><?= e($l['inicio']) ?>–<?= e($l['fim']) ?></b>
                  <?= e($l['modulo']) ?><br><?= e($l['turma']) ?> · <?= e($l['sala']) ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="legenda">Verde: lições de dia. Amarelo: lições a partir das 17:00.</p>
      </section>

      <section>
        <div class="sec-h">
          <h2>Turmas que dirige</h2>
          <a class="btn sm" href="<?= e($rotas['dirigir']) ?>">Gerir direcção de turmas</a>
        </div>
        <?php if (!$turmasDirigidas): ?>
          <p class="vazio">Este formador não é diretor de nenhuma turma. Use “Gerir direcção de turmas” para atribuir uma.</p>
        <?php else: ?>
        <div class="scroll"><table>
          <thead><tr><th>Turma</th><th>Qualificação</th><th>Ano lectivo</th><th>Turno</th><th class="num">Formandos</th></tr></thead>
          <tbody>
          <?php foreach ($turmasDirigidas as $t): ?>
            <tr>
              <td><a href="<?= e($rotas['turma'] . (int)$t['id']) ?>"><b><?= e($t['nome']) ?></b></a></td>
              <td><?= e($t['qualificacao']) ?>, <?= e($t['nivel']) ?></td>
              <td><?= e($t['ano']) ?></td>
              <td><?= e($t['turno']) ?></td>
              <td class="num"><?= (int)$t['formandos'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php endif; ?>
      </section>

      <section>
        <div class="sec-h">
          <h2>Qualificações que coordena</h2>
          <a class="btn sm" href="<?= e($rotas['coord']) ?>">Gerir coordenação</a>
        </div>
        <?php if (!$coordenacoes): ?>
          <p class="vazio">Este formador não coordena nenhuma qualificação.</p>
        <?php else: ?>
        <div class="scroll"><table>
          <thead><tr><th>Qualificação</th><th>Campo</th><th>Níveis</th></tr></thead>
          <tbody>
          <?php foreach ($coordenacoes as $c): ?>
            <tr>
              <td><b><?= e($c['qualificacao']) ?></b></td>
              <td><?= e($c['campo']) ?></td>
              <td class="chips"><?php foreach ($c['niveis'] as $n): ?><span><?= e($n) ?></span><?php endforeach; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php endif; ?>
      </section>

      <section>
        <div class="sec-h"><h2>Módulos que leciona</h2></div>
        <?php if (!$modulos): ?>
          <p class="vazio">Ainda não tem módulos atribuídos. Marque uma lição para o associar a um módulo e a uma turma.</p>
        <?php else: ?>
        <div class="scroll"><table>
          <thead><tr><th>Módulo</th><th>Qualificação</th><th>Turmas</th><th class="num">Carga horária</th></tr></thead>
          <tbody>
          <?php foreach ($modulos as $m): ?>
            <tr>
              <td><b><?= e($m['nome']) ?></b></td>
              <td><?= e($m['qualificacao']) ?>, <?= e($m['nivel']) ?></td>
              <td class="chips"><?php foreach ($m['turmas'] as $t): ?><span><?= e($t) ?></span><?php endforeach; ?></td>
              <td class="num"><?= (int)$m['carga'] ?> h</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php endif; ?>
      </section>

      <section>
        <div class="sec-h"><h2>Registo de alterações</h2></div>
        <?php if (!$logs): ?>
          <p class="vazio">Sem alterações registadas.</p>
        <?php else: ?>
        <ul class="hist">
          <?php foreach ($logs as $g): ?>
            <li><time><?= e($g['quando']) ?></time><span><?= e($g['accao']) ?> <span style="color:var(--muted)">por <?= e($g['utilizador']) ?></span></span></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </section>
    </main>
  </div>
</div>

<dialog id="dlg-contacto" aria-labelledby="t-contacto">
  <form method="post" action="<?= e($rotas['rapido']) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <h2 id="t-contacto">Alterar contactos</h2>
    <label for="f-contacto">Contacto</label>
    <input id="f-contacto" name="contacto" value="<?= e($formador['contacto']) ?>" required>
    <label for="f-email">E-mail</label>
    <input id="f-email" name="email" type="email" value="<?= e($formador['email']) ?>" required>
    <div class="dlg-btns">
      <button class="btn" type="button" data-close>Cancelar</button>
      <button class="btn main" type="submit">Guardar alterações</button>
    </div>
  </form>
</dialog>

<dialog id="dlg-eliminar" aria-labelledby="t-eliminar">
  <form method="post" action="<?= e($rotas['eliminar']) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <h2 id="t-eliminar">Eliminar <?= e($nomeCompleto) ?>?</h2>
    <p>Esta acção não pode ser desfeita. Se o formador tiver lições, turmas ou coordenação associadas, o sistema impede a eliminação.</p>
    <div class="dlg-btns">
      <button class="btn" type="button" data-close>Cancelar</button>
      <button class="btn danger" type="submit">Eliminar formador</button>
    </div>
  </form>
</dialog>

<script>
document.querySelectorAll('[data-open]').forEach(b =>
  b.addEventListener('click', () => document.getElementById(b.dataset.open).showModal()));
document.querySelectorAll('[data-close]').forEach(b =>
  b.addEventListener('click', () => b.closest('dialog').close()));
</script>
</body>
</html>
