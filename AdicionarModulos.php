<?php
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

/* Dados de exemplo: no projeto real, vêm da base de dados (PDO + SELECT) */
$cadeiras = [
  'mat1'  => ['Matemática I',          '1.º Semestre', 6],
  'prog1' => ['Programação I',         '1.º Semestre', 6],
  'fis1'  => ['Física Geral',          '1.º Semestre', 5],
  'mat2'  => ['Matemática II',         '2.º Semestre', 6],
  'prog2' => ['Programação II',        '2.º Semestre', 6],
  'bd1'   => ['Bases de Dados',        '2.º Semestre', 5],
  'est'   => ['Estruturas de Dados',   '3.º Semestre', 6],
  'redes' => ['Redes de Computadores', '3.º Semestre', 5],
  'so'    => ['Sistemas Operativos',   '3.º Semestre', 5],
];

$sel = $_SESSION['sel'] ?? [];

/* ---------- Ações (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(403); exit('Pedido inválido.'); }

  $acao = $_POST['acao'] ?? '';
  $id   = $_POST['id'] ?? '';

  if ($acao === 'add' && isset($cadeiras[$id]) && !in_array($id, $sel, true)) {
    $sel[] = $id;                                   // sai do dropdown e entra na tabela
  } elseif ($acao === 'remover') {
    $sel = array_values(array_diff($sel, [$id]));   // volta ao dropdown
  } elseif ($acao === 'confirmar' && $sel) {
    // Aqui: INSERT na base de dados, tudo numa transação
    $_SESSION['msg'] = 'Inscrição registada em ' . count($sel) . ' cadeira(s).';
    $sel = [];
  }
  $_SESSION['sel'] = $sel;

  $qs = http_build_query(array_filter(
    ['q' => trim($_POST['q'] ?? ''), 'sem' => $_POST['sem'] ?? ''], 'strlen'));
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($qs ? '?' . $qs : ''));
  exit;
}

/* ---------- Pesquisa + filtro por semestre (GET) ---------- */
$semestres = array_values(array_unique(array_column($cadeiras, 1)));
$q   = trim($_GET['q'] ?? '');
$sem = in_array($_GET['sem'] ?? '', $semestres, true) ? $_GET['sem'] : '';   // '' = todos

$disponiveis = array_filter($cadeiras, function ($c, $id) use ($sel, $q, $sem) {
  if (in_array($id, $sel, true)) return false;      // já escolhida: não aparece
  if ($sem !== '' && $c[1] !== $sem) return false;  // filtro de semestre
  return $q === '' || mb_stripos($c[0], $q) !== false;
}, ARRAY_FILTER_USE_BOTH);

$link = fn($s) => '?' . http_build_query(array_filter(['q' => $q, 'sem' => $s], 'strlen'));

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);
$total = array_sum(array_map(fn($id) => $cadeiras[$id][2], $sel));
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inscrição em cadeiras</title>
<style>
  :root{--bg:#eef1f5;--surface:#fff;--ink:#14213d;--muted:#5b6577;--line:#d6dce6;
        --accent:#2457d6;--accent-soft:#e4ebfb;--fail:#b42318;--ok:#157347}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--ink);line-height:1.45;
       font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;padding:2rem 1rem}
  button,input{font:inherit;color:inherit}
  :focus-visible{outline:3px solid var(--accent);outline-offset:2px}
  main{max-width:760px;margin:auto;background:var(--surface);border-radius:14px;
       padding:1.5rem;box-shadow:0 8px 30px rgba(20,33,61,.12)}
  h1{margin:0 0 1rem;font-size:1.3rem}
  .ok{background:#e3f4ea;color:var(--ok);padding:.7rem 1rem;border-radius:8px;font-weight:600;margin-bottom:1rem}

  /* Pesquisa + dropdown */
  .search{position:relative;margin-bottom:1.5rem}
  .search form.busca{display:flex;gap:.5rem}
  .search input[type=search]{flex:1;padding:.7rem .9rem;border:1px solid var(--line);border-radius:8px}
  .btn{border:0;border-radius:8px;padding:.7rem 1.1rem;font-weight:600;cursor:pointer}
  .btn-primary{background:var(--accent);color:#fff}
  .btn-primary:disabled{background:#a9b4cc;cursor:not-allowed}

  /* Filtro de semestre (links, sem JS) */
  .sems{display:flex;flex-wrap:wrap;gap:.5rem;margin:.9rem 0 .6rem}
  .sems a{padding:.4rem .9rem;border:1px solid var(--line);border-radius:99px;
          text-decoration:none;color:var(--ink);font-weight:600;font-size:.9rem}
  .sems a:hover{background:var(--accent-soft)}
  .sems a[aria-current=true]{background:var(--accent);border-color:var(--accent);color:#fff}

  /* Lista de cadeiras disponíveis: sempre visível */
  .drop{background:#fff;border:1px solid var(--line);border-radius:10px;max-height:260px;overflow-y:auto}
  .drop form{margin:0}
  .drop button{all:unset;box-sizing:border-box;width:100%;display:flex;justify-content:space-between;
               gap:1rem;padding:.65rem 1rem;cursor:pointer;border-bottom:1px solid var(--line)}
  .drop form:last-child button{border-bottom:0}
  .drop button:hover,.drop button:focus-visible{background:var(--accent-soft)}
  .drop small{color:var(--muted)}
  .vazio{padding:.8rem 1rem;color:var(--muted)}
  .sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}

  /* Tabela */
  .scroll{overflow-x:auto}
  table{width:100%;border-collapse:collapse}
  th,td{text-align:left;padding:.65rem .5rem;border-bottom:1px solid var(--line)}
  th{font-size:.85rem;color:var(--muted)}
  td.num,th.num{text-align:right}
  .rm{background:none;border:1px solid var(--line);border-radius:6px;padding:.25rem .6rem;cursor:pointer;color:var(--fail)}
  .rm:hover{background:#fde8e6}
  tfoot td{font-weight:600;border-bottom:0}
  .rodape{display:flex;justify-content:flex-end;margin-top:1rem}
</style>
</head>
<body>
<main>
  <h1>Inscrição em cadeiras</h1>

  <?php if ($msg): ?><div class="ok" role="status"><?= $e($msg) ?></div><?php endif; ?>

  <!-- Campo de pesquisa + dropdown -->
  <div class="search">
    <form class="busca" method="get" role="search">
      <input type="hidden" name="sem" value="<?= $e($sem) ?>">
      <input type="search" name="q" value="<?= $e($q) ?>" list="sugestoes"
             placeholder="Pesquisar cadeira…" aria-label="Pesquisar cadeira" autocomplete="off">
      <button class="btn btn-primary" type="submit">Pesquisar</button>
      <!-- Sugestões nativas do navegador enquanto escreve (sem JS) -->
      <datalist id="sugestoes">
        <?php foreach ($disponiveis as $c): ?><option value="<?= $e($c[0]) ?>"><?php endforeach; ?>
      </datalist>
    </form>

    <nav class="sems" aria-label="Filtrar por semestre">
      <a href="<?= $e($link('')) ?>" <?= $sem === '' ? 'aria-current="true"' : '' ?>>Todos</a>
      <?php foreach ($semestres as $s): ?>
        <a href="<?= $e($link($s)) ?>" <?= $sem === $s ? 'aria-current="true"' : '' ?>><?= $e($s) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="drop">
      <?php if (!$disponiveis): ?>
        <div class="vazio">Nenhuma cadeira disponível<?= ($q !== '' || $sem !== '') ? ' para este filtro' : '' ?>.</div>
      <?php endif; ?>
      <?php foreach ($disponiveis as $id => $c): ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
          <input type="hidden" name="acao" value="add">
          <input type="hidden" name="id"   value="<?= $e($id) ?>">
          <input type="hidden" name="q"    value="<?= $e($q) ?>">
          <input type="hidden" name="sem"  value="<?= $e($sem) ?>">
          <button type="submit"><span><?= $e($c[0]) ?></span><small><?= $e($c[1]) ?> · <?= (int)$c[2] ?> créd.</small></button>
        </form>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tabela das cadeiras escolhidas -->
  <div class="scroll">
    <table>
      <thead><tr><th>Cadeira</th><th>Semestre</th><th class="num">Créditos</th><th><span class="sr">Ação</span></th></tr></thead>
      <tbody>
      <?php if (!$sel): ?>
        <tr><td colspan="4" class="vazio">Ainda sem cadeiras. Pesquise e clique numa opção para adicionar.</td></tr>
      <?php endif; ?>
      <?php foreach ($sel as $id): $c = $cadeiras[$id]; ?>
        <tr>
          <td><?= $e($c[0]) ?></td>
          <td><?= $e($c[1]) ?></td>
          <td class="num"><?= (int)$c[2] ?></td>
          <td class="num">
            <form method="post">
              <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
              <input type="hidden" name="acao" value="remover">
              <input type="hidden" name="id"   value="<?= $e($id) ?>">
              <input type="hidden" name="q"    value="<?= $e($q) ?>">
          <input type="hidden" name="sem"  value="<?= $e($sem) ?>">
              <button class="rm" type="submit" aria-label="Remover <?= $e($c[0]) ?>">Remover</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <?php if ($sel): ?>
      <tfoot><tr><td colspan="2">Total</td><td class="num"><?= (int)$total ?></td><td></td></tr></tfoot>
      <?php endif; ?>
    </table>
  </div>

  <form class="rodape" method="post">
    <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
    <input type="hidden" name="acao" value="confirmar">
    <button class="btn btn-primary" type="submit" <?= $sel ? '' : 'disabled' ?>>Confirmar inscrição</button>
  </form>
</main>
</body>
</html>