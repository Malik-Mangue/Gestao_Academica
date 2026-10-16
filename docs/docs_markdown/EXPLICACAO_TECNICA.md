# Explicação Técnica — Mecanismos de Implementação

> Segundo documento do projeto, **complementar** ao `GUIA_DO_CODIGO.md`.
> Aqui não se explica "o que é o sistema", mas sim **como as peças técnicas funcionam por dentro**:
> prepared statements, sessões, hashing, RBAC, transações, overlaping de horários, XSS/SQLi, PRG,
> modais, migrações, e as armadilhas específicas deste código.
>
> Público-alvo: quem já leu (ou vai ler) o guia geral e quer o **detalhe de engenharia**.

---

## Índice

1. [Panorama técnico e stack](#1-panorama-técnico)
2. [Bootstrap: como um pedido HTTP percorre o código](#2-bootstrap-e-ciclo-de-vida)
3. [Carregamento de dependências (`require_once`, `__DIR__`)](#3-carregamento-de-dependências)
4. [Ligação à base de dados (mysqli + Singleton)](#4-ligação-à-base-de-dados)
5. [Prepared statements e `bind_param` (referência completa)](#5-prepared-statements-e-bind_param)
6. [Tratamento de erros e exceções](#6-tratamento-de-erros-e-exceções)
7. [Transações (atomicidade)](#7-transações)
8. [Sessões e segurança de sessão](#8-sessões-e-segurança)
9. [Passwords e hashing](#9-passwords-e-hashing)
10. [Autenticação e autorização (RBAC por dentro)](#10-autenticação-e-autorização)
11. [Validação de dados](#11-validação-de-dados)
12. [Segurança: SQL Injection, XSS e listas brancas](#12-segurança-sqli-xss-listas-brancas)
13. [PRG e flash messages](#13-prg-e-flash-messages)
14. [Modais: "pseudo-navegação" sem JavaScript](#14-modais-pseudo-navegação)
15. [Composição de objetos nos Models](#15-composição-de-objetos-nos-models)
16. [SQL avançado usado no projeto](#16-sql-avançado)
17. [Deteção de conflitos de horário (Lição)](#17-deteção-de-conflitos-de-horário)
18. [Integridade de dados em código (bloqueios de remoção)](#18-integridade-em-código)
19. [Migrações](#19-migrações)
20. [Tipos de dados problemáticos](#20-tipos-de-dados-problemáticos)
21. [Armadilhas técnicas específicas deste código](#21-armadilhas-específicas)
22. [Checklist de revisão técnica](#22-checklist-de-revisão-técnica)

---

## 1. Panorama técnico

| Aspeto | Escolha do projeto | Consequência |
|---|---|---|
| Linguagem | PHP (com `mysqli` procedural-orientado a objetos) | Sem `PDO`, sem ORM |
| Base de dados | MySQL 8 / MariaDB | `mysqli_sql_exception` disponível |
| Padrão arquitetural | MVC + DAO | Camadas explícitas |
| Gestão de dependências | `require_once` manual | Sem Composer/autoload |
| Roteamento | Nenhum — acesso direto a ficheiros `.php` | URLs apontam para caminhos reais |
| Sessão | Sessões nativas PHP (`$_SESSION`) | Estado no servidor |
| Hashing | `password_hash`/`password_verify` (bcrypt) | Seguro |
| Frontend | HTML5 + CSS puro; modais via CSS `:target` | Sem framework JS |
| Migrações | Scripts SQL manuais | Não há runner automático |

---

## 2. Bootstrap e ciclo de vida

Não existe `index.php` central. **Cada URL é um ficheiro**. O ciclo típico de um ecrã:

```
1. O browser pede GET /view/formadores/index.php
2. O PHP executa esse ficheiro de cima para baixo:
   a. require_once services/Sessao.php        → define Sessao, Database, Perfil, etc.
   b. Sessao::exigirLogin('../login/index.php')  → inicia sessão; se não logado, redireciona e faz exit
   c. require_once controller/...Controller.php → define o controller e carrega o DAO
   d. $controller = new XController()         → o construtor do DAO abre/recupera a ligação MySQL
   e. Se for POST → trata o formulário, grava, define flash, header('Location') + exit
   f. Se for GET   → chama listar() e obtém os dados
   g. require partials/header.php → imprime <html> + topbar
   h. require partials/sidebar.php → imprime o menu
   i. imprime a tabela/modais
   j. require partials/footer.php → fecha
```

Pontos técnicos fundamentais:

- **O construtor do DAO abre a ligação.** Mas, graças ao Singleton em `Database`, é sempre a **mesma**
  ligação — mesmo que 5 DAOs sejam instanciados no mesmo pedido.
- **`exit` é obrigatório** depois de cada `header('Location: ...')`. Se esqueceres o `exit`, o PHP
  continua a executar e pode gravar/inprimir coisas inesperadas antes de o browser seguir o redirecionamento.
- **`$_SERVER['REQUEST_METHOD']`** é a forma de distinguir `GET` de `POST`.

---

## 3. Carregamento de dependências

O projeto não usa `autoload`. Cada ficheiro declara explicitamente o que precisa:

```php
require_once __DIR__ . '/../Dao/FormadorDao.php';
require_once __DIR__ . '/../model/Formador.php';
```

- **`__DIR__`** é a pasta do ficheiro atual (absoluto em runtime). É por isso que `require` com
  `__DIR__ . '/../...'` funciona independentemente de onde o script foi chamado. Sem `__DIR__`, o
  caminho seria relativo ao *Current Working Directory* e partiria facilmente.
- **`require_once`** garante que o ficheiro só é incluído **uma vez** por pedido (evita
  "Cannot redeclare class").
- **Ordem importa.** Como as classes são usadas no corpo do ficheiro, um `require` tem de vir **antes**
  do uso. Ex.: não se pode instanciar `Database` antes de `config/conexao.php` estar incluído.

> ⚠️ **Dependências implícitas (ver §21):** alguns DAOs usam classes sem as `require`-ar diretamente,
> contando com o facto de já terem sido carregadas por outro ficheiro. É frágil.

---

## 4. Ligação à base de dados

```php
class Database {
    private static $conn = null;             // estado da classe (não do objeto)

    public function getConnection(){
        if (self::$conn === null) {          // só cria uma vez
            self::$conn = new mysqli($host, $user, $pass, $db);
            if (self::$conn->connect_error) throw new Exception(...);
        }
        return self::$conn;                  // devolve sempre a mesma
    }
}
```

Detalhes técnicos:

- **`static $conn`** pertence à **classe**, não a cada objeto → verdadeiro Singleton no pedido.
- **`mysqli`** é a extensão MySQL melhorada. Aqui é usada em **estilo orientado a objetos**
  (`$conn->prepare`, `$stmt->execute`, `$stmt->get_result()`).
- **`$stmt->get_result()`** devolve um `mysqli_result`, que percorres com `fetch_assoc()` (array
  associativo coluna→valor).
- **`$conn->insert_id`** devolve o ID `AUTO_INCREMENT` gerado pelo último `INSERT` — usado para
  preencher o `codigo` do objeto logo após criar.
- **Tipos de retorno:** `execute()` devolve `true`/`false`; `fetch_assoc()` devolve `array` ou `null`
  (fim de resultados / sem linhas).

### `connect_error` vs lançar exceção

O mysqli costuma **lançar** `mysqli_sql_exception` em erros de SQL quando em modo estrito. O
`connect_error` é verificado explicitamente logo após criar a ligação, e a falha termina com `die()`.
Ou seja: **não há recuperação elegante** de falha de ligação — a página morre.

---

## 5. Prepared statements e `bind_param`

### Como funciona

```php
$sql  = "insert into Formador (nome, apelido, ...) values (?, ?, ...)";
$stmt = $db->prepare($sql);        // o servidor MySQL já conhece a "estrutura" da query
$stmt->bind_param("sssssiiid", $nome, $apelido, ...);  // agora liga os valores
$stmt->execute();                  // executa
```

O primeiro argumento de `bind_param` é uma **string de tipos**, com **uma letra por cada `?`**:

| Letra | Tipo esperado | Exemplo |
|---|---|---|
| `s` | string | nome, email, data em texto |
| `i` | inteiro | códigos, telefone, ano |
| `d` | *double* (decimal) | valores monetários/peso |

> Regra: a ordem das variáveis em `bind_param` tem de corresponder **exatamente** à ordem dos `?`.
> A contagem de letras tem de ser igual ao número de `?`. Trocar isto é a causa nº 1 de bugs de SQL.

### Referência completa por método (extraída do código)

| Método | Tipos | Variáveis (ordem) |
|---|---|---|
| `FormadorDao::create` | `sssssiiid` | nome, apelido, email, genero, estadoCivil, contacto, valor_hora, horas_mes, salario |
| `FormadorDao::update` | `sssssiiidi` | (iguais) + codigo |
| `FormadorDao::getAll` | `ssssss` | busca ×6 |
| `UsuarioDao::create` | `ssssisssss` | nome, username, password, apelido, idPerfil, estadoCivil, genero, telefone, email, BI |
| `UsuarioDao::update` | `sssissssssii` | nome, username, apelido, idPerfil, estadoCivil, genero, telefone, email, BI, password, primeiroAcesso, idUser |
| `UsuarioDao::atualizarPassword` | `sii` | password, primeiroAcesso, idUser |
| `PerfilDao::create / update / delete` | `s` / `si` / `i` | nome / nome,id / id |
| `CampoDao::create / update / delete` | `s` / `si` / `i` | nome / nome,id / id |
| `QualificacaoDao::create / update` | `si` / `sii` | titulo,coordenador / titulo,coordenador,id |
| `Quali_NivelDao::create / update` | `ii` / `iii` | quali,nivel / quali,nivel,id |
| `ModuloDao::create / update` | `sii` / `siii` | nome,carga,qualiNivel / + id |
| `TurmaDao::create / update` | `sisii` / `sisiii` | nome,ano,turno,diretor,qualiNivel / + id |
| `InscricaoDao::create / update` | `iiss` / `iissi` | formando,modulo,semestre,data / + id |
| `MatriculaDao::create / update` | `iiis` / `iiisi` | formando,quali,qualiNivel,data / + id |
| `LicaoDao::create / update` | `iiiisss` / `iiiisssi` | modulo,formador,sala,turma,data,horaIni,horaFim / + id |
| `LicaoDao::existeConflito` | `iiiss` (+`i`) | sala,formador,data,horaFim,horaIni (+ id ignorado) |
| `LogDao::salvar` | `isss` | idUsuario, acao, descricao, data |

### Truque: `bind_param` variádico

Em `LicaoDao::existeConflito` a query é montada **condicionalmente** e os parâmetros são passados
com o operador *splat* (`...`):

```php
$params = [$codSala, $codFormador, $data, $horaFim, $horaInicio];
$types  = "iiiss";
if ($codigoIgnorado !== null) {
    $sql    .= " and codigo <> ?";
    $params[] = $codigoIgnorado;   // acrescenta o parâmetro
    $types   .= "i";               // acrescenta o tipo
}
$stmt->bind_param($types, ...$params);   // espalha o array como argumentos
```

Isto é uma técnica limpa para queries dinâmicas **sem** perder a segurança dos prepared statements.

### Um nome de tabela NÃO pode ser `?`

Em SQL, um placeholder `?` só serve para **valores**, nunca para **nomes de tabela/coluna**. Por isso
`DashboardDao::contar($tabela)` **concatena** o nome da tabela — e protege-se com uma **lista branca**:

```php
$permitidas = ['Formador','Formando','Turma',...];
if (!in_array($tabela, $permitidas, true)) throw new InvalidArgumentException(...);
$sql = "select count(*) as total from `" . $tabela . "`";
```

---

## 6. Tratamento de erros e exceções

O projeto usa **três mecanismos** de erro, para finalidades diferentes:

### 6.1 `die()` — falha fatal de infraestrutura

Em `config/conexao.php`: se a base de dados não ligar, `die("Falha na base de dados: ...")`.
Sem recuperação. É a "parede final".

### 6.2 `mysqli_sql_exception` — erros de SQL esperados

Sempre que uma escrita pode falhar por uma *constraint* (ex.: `UNIQUE` duplicado), o DAO envolve o
`execute()`:

```php
try {
    $result = $stmt->execute();
} catch (mysqli_sql_exception $e) {
    return false;         // erro "tratado" → o controller devolve false → View mostra flash de erro
}
```

Para `Usuario`, a exceção é **convertida** numa mensagem amigável:

```php
try { $result = $stmt->execute(); }
catch (mysqli_sql_exception $e) {
    throw new Exception("Esse nome de utilizador já existe. Escolha outro username.");
}
```

### 6.3 `Exception` de negócio — regras violadas

O `FormadorController::apagarFormador` **lança** `Exception` quando há dependências:

```php
throw new Exception("Não é possível eliminar este formador pois está sendo usado em uma ou mais lições.");
```

A View captura com `try { ... } catch (Exception $e) { flash = $e->getMessage(); }`.

### 6.4 `Dao/ExceptionDao.php`

```php
class ExceptionDao extends Exception {
    public function __construct($mensagem) { parent::__construct($mensagem); }
}
```

É só um tipo de exceção próprio do domínio de dados (não adiciona comportamento). Define uma
"família" de erro para distinguir erros de dados de erros genéricos.

### Resumo dos níveis

| Nível | Mecanismo | Usado para |
|---|---|---|
| Infraestrutura | `die()` | Ligação à BD falhou |
| Persistência | `catch (mysqli_sql_exception) → return false` | Constraint violada, execução falhou |
| Negócio | `throw new Exception(...)` | Regra de negócio impede a operação |
| Apresentação | `$_SESSION['flash']` | Mostrar a mensagem ao utilizador |

---

## 7. Transações

Duas operações fazem escrita em **múltiplas tabelas** e por isso usam transações (tudo ou nada).

### 7.1 `QualificacaoDao::deleteCompleto()`

```php
$this->db->begin_transaction();
try {
    foreach ($sqls as $sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        if (!$stmt->execute()) throw new mysqli_sql_exception("Falha: " . $sql);
    }
    $this->db->commit();     // confirma tudo
    $this->registarLog(...);
    return true;
} catch (mysqli_sql_exception $e) {
    $this->db->rollback();   // desfaz tudo
    return false;
}
```

Apaga, na ordem correta: `Classificacao` → `Quali_Nivel` → `Qualificacao`. Se alguma falhar, tudo é
revertido. Isto evita deixar "órfãos" numa tabela associativa.

### 7.2 `PerfilPermissaoDao::substituir()`

Aplica a estratégia **"apagar tudo e reinserir"** para as permissões de um perfil:

```php
begin_transaction()
DELETE FROM perfil_permissao WHERE perfil_id = ?
foreach ($concessoes as $c) { INSERT ... }   // com de-duplicação via array $vistos
commit()   // ou rollback() em caso de erro
```

A de-duplicação (`$chave = recursoId . ':' . permissaoId`) evita violar a `UNIQUE KEY`.

> **Porque é que "apagar+reinserir" é aceitável aqui?** A matriz de permissões é pequena e as escritas
> são raras; a operação é atómica; e evita ter de calcular diffs. É uma escolha pragmática.

---

## 8. Sessões e segurança

### Configuração em `Sessao::iniciar()`

```php
ini_set('session.gc_maxlifetime', 7200);          // 2 horas de vida
session_set_cookie_params([
    'lifetime' => 7200,
    'path'     => '/',
    'httponly' => true,     // JS não lê o cookie → mitiga roubo via XSS
    'samesite' => 'Lax',    // mitiga CSRF
]);
session_start();
```

- **`session_status() === PHP_SESSION_NONE`** evita avisos de "session already started" quando
  `iniciar()` é chamado várias vezes (e é — em `Sessao`, no `header`, no `sidebar`...).
- **`httponly`** impede `document.cookie` de aceder ao cookie de sessão.
- **`samesite=Lax`** envia o cookie nas navegações normais, mas **não** em pedidos cross-site — ajuda
  contra CSRF.

### Regeneração de ID no login

```php
public static function login(Usuario $usuario) {
    Sessao::iniciar();
    session_regenerate_id(true);   // <-- mata o ID antigo e cria um novo
    ...
}
```

Isto previne **Session Fixation**: um atacante que consiga forçar um ID de sessão conhecido não
consegue "herdar" a sessão autenticada, porque o ID muda no login.

### O que vai para `$_SESSION`

```
user_id, username, nome, apelido, idPerfil, perfil, primeiro_acesso, permissoes[recurso][acao]
```

### O que **não** vai

A password. Nunca é guardada em sessão.

---

## 9. Passwords e hashing

- **Criação:** `password_hash(Usuariocontroller::SENHA_PADRAO, PASSWORD_BCRYPT)` → senha padrão **1234**.
- **Verificação:** `password_verify($password, $hash)` — compara a senha em claro com o hash.
- **Alteração:** o utilizador troca a senha; o mínimo é **6 caracteres** e tem de ser **diferente da antiga**
  (verificado em `view/login/redefinir.php`).
- **Reset pelo admin:** repõe para `1234` e define `primeiroAcesso = 1`.

### Fluxo do `primeiroAcesso`

```
Criação de utilizador  → primeiroAcesso = 1  (forçado no SQL do INSERT)
Login com primeiroAcesso=1 → Sessao::login → view/login/index.php redireciona para redefinir.php
Sessao::exigirLogin() em qualquer página → bloquearPrimeiroAcesso() → redefinir.php
Ao trocar a senha → atualizarPassword(..., 0) → primeiroAcesso = 0
```

É uma máquina de estados simples baseada num `TINYINT(1)`.

> ⚠️ **Atenção à largura da coluna:** `Usuario.password` é `varchar(60)`. Um hash bcrypt tem
> exatamente 60 caracteres — cabe, mas **sem margem**. Se algum dia mudares para `PASSWORD_DEFAULT`
> apontando para Argon2 (hashes mais longos), tens de aumentar a coluna.

> ⚠️ `controller/FicheiroDefault.php` grava a password **em claro** num `.txt`. É código morto (não é
> referenciado por ninguém), mas deve ser eliminado — nunca guardar credenciais em ficheiros de texto.

---

## 10. Autenticação e autorização

### 10.1 Autenticação (quem és)

```
UsuarioController::autenticar($username, $password)
  └─ dao->getByUsername($username)          // SELECT ... JOIN Perfil
  └─ password_verify($password, hash)        // confirma
  └─ dao->registarLogin($usuario)            // grava Log
  └─ devolve o objeto Usuario
```

A comparação de username é feita pela BD (coluna `UNIQUE username`), e a de password em PHP
(`password_verify`). **Nunca** se compara a password dentro do SQL.

### 10.2 Autorização (o que podes fazer) — RBAC

Modelo de dados:

```
recurso(nome, grupo)             ex: 'formadores' (grupo 'Academico')
permissao(nome)                  ex: 'criar','consultar','editar','eliminar'
perfil_permissao(perfil_id, recurso_id, permissao_id)
```

Cálculo da matriz (`PerfilPermissaoDao::getMatrizByPerfil`):

```sql
select r.nome as recurso, p.nome as permissao
from perfil_permissao pp
join recurso  r on r.id = pp.recurso_id
join permissao p on p.id = pp.permissao_id
where pp.perfil_id = ?
```

Resultado guardado em sessão:

```php
$matriz['formadores']['criar'] = true;
$matriz['formadores']['consultar'] = true;
$matriz['logs']['consultar'] = true;
```

### 10.3 A tradução C/R/U/D ↔ nomes

As constantes `ACAO_*` são letras (`'C','R','U','D'`), mas na matriz as chaves são por extenso.
`normalizarAcao()` faz a ponte via `$apelidoAcao`:

```
'C' → 'criar' ; 'R' → 'consultar' ; 'U' → 'editar' ; 'D' → 'eliminar'
```

### 10.4 Verificação

`Sessao::pode($acao, $recurso)`:
1. Garante que `$_SESSION['permissoes']` existe (`garantirPermissoesCarregadas` — *lazy load*).
2. Normaliza a ação.
3. Devolve `!empty($_SESSION['permissoes'][$recurso][$permissao])`.

As guardas de página (`exigirLogin`, `exigirLeitura`, `exigirAcao`, `exigirPerfil`) chamam `pode()` e
ou deixam passar, ou fazem `header(...); exit;`.

### 10.5 Fallback legado

Se `getMatrizByPerfil()` devolver vazio (base antiga, sem seed de permissões), usa-se `$matrizLegada`,
uma matriz fixa em código, construída por **grupo** (`*Academico`) com overrides por recurso:

```php
self::ADMIN => [
    '*Academico'   => ['consultar','criar','editar','eliminar'],
    'utilizadores' => [...],
    'perfis'       => [...],
],
```

`construirMatrizLegada()` percorre os recursos reais e aplica o grupo, permitindo *overrides* pontuais.

### 10.6 Cache de permissões — a pegadinha

As permissões vivem em `$_SESSION` e **só são recarregadas no login**. Se um administrador alterar as
permissões de um perfil, os utilizadores desse perfil **não veem a mudança** até fazer novo login.

Mitigação existente: `PerfilController::guardarPermissoes()` recarrega automaticamente **se** estiveres
a editar **o teu próprio** perfil:

```php
if ($ok && $idPerfil === (int) ($_SESSION['idPerfil'] ?? 0)) {
    Sessao::recarregarPermissoes();
}
```

Nos restantes casos, `Sessao::recarregarPermissoes()` pode ser chamado manualmente (ou força-se logout).

---

## 11. Validação de dados

> Os validadores abaixo fazem parte do trait `Validacao` (`controller/Validacao.php`), usado pelos
> controllers (`$this->...`) e invocado pelas views através do respectivo controller (`$controller->...`).

### Expressões regulares

`texto()`:
```php
preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\p{Zs}.,'’()\-\/]*$/u", $valor)
```
- `\p{L}` = qualquer letra (inclui acentos); `\p{N}` = qualquer número; `\p{Zs}` = espaço.
- O modificador **`/u`** ativa Unicode (indispensável para acentos).
- O primeiro carácter tem de ser letra ou número (não permite começar por pontuação).

`hora()`:
```php
preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor)   // HH:MM de 00:00 a 23:59
```

`data()`:
```php
$d = DateTime::createFromFormat('Y-m-d', $valor);
return $d !== false && $d->format('Y-m-d') === $valor;
```
O `createFromFormat` sozinho aceitaria datas inválidas em alguns casos; a igualdade de formatação
garante que é **exatamente** `Y-m-d` e uma data real.

`inteiroPositivo()`:
```php
filter_var($valor, FILTER_VALIDATE_INT) !== false && (int)$valor > 0
```

### Dupla validação (View + Controller)

A mesma regra aparece em dois sítios:
- **View:** validação "de cortesia" para dar mensagens bonitas (ex.: "Nome obrigatório").
- **Controller:** validação "de verdade" (regras de negócio) — mesmo que alguém salte a View, os
  dados não entram errados.

> Como a View (`$controller->...`) e o Controller (`$this->...`) partilham o mesmo trait `Validacao`,
> a regra está definida num único ficheiro: alterar um limite faz-se só em `controller/Validacao.php`.

---

## 12. Segurança: SQLi, XSS, listas brancas

### SQL Injection

- **Prevenção principal:** *prepared statements* em **todas** as queries com valores.
- **Exceção controlada:** nomes de tabela concatenados (DashboardDao) protegidos por **lista branca**.
- **Regra:** nunca interpolar `$_GET`/`$_POST` diretamente numa string SQL.

### XSS (Cross-Site Scripting)

Sempre que se imprime conteúdo potencialmente controlado pelo utilizador, usa-se:

```php
<?= htmlspecialchars($formador->getNome()) ?>
```

`htmlspecialchars()` converte `< > & " '` em entidades, impedindo que um nome como
`<script>alert(1)</script>` seja executado. O código é consistente nesta prática nas Views.

### Outras boas práticas presentes

- `filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT)` para validação de inteiros na entrada.
- `(int)` / `(string)` casts explícitos antes de usar valores.
- `session_regenerate_id(true)` no login.
- Cookies `httponly` + `samesite`.

### Lacunas conhecidas

- **Não há token CSRF** nos formulários. A proteção é indireta (`samesite=Lax` + verificação de sessão).
- **Não há rate-limiting** de tentativas de login.
- **Credenciais e `display_errors`** em código/config (ver §20, §21).

---

## 13. PRG e flash messages

**PRG = Post/Redirect/Get.** O padrão é:

```php
// dentro do if (REQUEST_METHOD === 'POST')
$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Formador cadastrado com sucesso!'];
header('Location: index.php');
exit;
```

Porquê: sem o redirecionamento, um F5 reenviaria o POST (`INSERT` duplicado). Com PRG, o browser acaba
num GET, e atualizar apenas recarrega a lista.

**Flash message:**

```php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);          // lê e apaga de imediato → aparece só uma vez
```

Depois é renderizada:
```php
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <span><?= htmlspecialchars($flash['msg']) ?></span>
</div>
```

O `type` corresponde a classes CSS (`success`/`danger`), não tem lógica de negócio.

---

## 14. Modais: "pseudo-navegação"

Não há JavaScript a abrir modais. O truque é **âncoras + CSS `:target`**:

- Cada linha tem um link `<a href="#modal-editar-3">Editar</a>`.
- Existe um `<div id="modal-editar-3" class="modal-overlay">` (normalmente escondido).
- O CSS `style.css` mostra o overlay quando o elemento é o `:target` do URL
  (ex.: `.../index.php#modal-editar-3`).
- Fechar = link para `#` (remove o fragmento do URL).

Implicações técnicas:

- O ID do modal tem de ser **único na página** → usa-se o código como sufixo (`modal-editar-<?= $codigo ?>`).
- Repetir a mesma estrutura para "novo" (`#modal-novo`) e "deletar" (`#modal-deletar-<codigo>`).
- **Sem JS**: vantagens de simplicidade/acessibilidade; desvantagens de não haver validação em tempo
  real nem controlo fino de foco.

---

## 15. Composição de objetos nos Models

Modelos representam relações por **composição**. Exemplos:

```php
Quali_Nivel:  cod_quali, cod_nivel, qualificacao (Qualificacao), nivel (Nivel)
Turma:        ..., diretor_turma (Diretor_turma), qualificacao (Qualificacao), quali_nivel (Quali_Nivel)
Licao:        modulo (Modulo), formador (Formador), sala (Sala), turma (Turma)
Matricula:    formando (Formando), qualificacao (Qualificacao), nivel (Nivel), id_quali_nivel (int)
```

O DAO constrói esse grafo no método `montarX()`/`getAll()`: lê colunas do `JOIN` e **injeta objetos**
nos setters. Isto permite `$turma->getDiretorTurma()->getFormador()->getNome()`.

### Objetos "parciais"

Muitas vezes só se preenchem os campos necessários para a UI, deixando outros a `null`:

```php
$formador = new Formador($rs['id_Diretor_Turma'], $rs['diretor_turma'], null, null, ...);
```

É rápido, mas **frágil**: se alguém chamar um getter num campo que ficou `null`, obtém `null`
(ex.: `getContacto()` numa Turma listada). Só é seguro usar os campos que a query efetivamente traz.

---

## 16. SQL avançado

### JOINs de múltiplas tabelas

`TurmaDao::getAll` encadeia **5 JOINs** para "desnormalizar" a turma para apresentação:

```sql
from Turma
join Diretor_Turma  on cod_Formador = id_Diretor_Turma
join Formador       on Formador.codigo = cod_Formador
join Quali_Nivel    on id_Quali_Nivel = codigo_Quali_Nivel
join Nivel          on cod_Nivel = Nivel.codigo
join Qualificacao   on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
where Turma.nome like ?
```

**Porquê:** a tabela `Turma` só guarda IDs (`id_Diretor_Turma`, `id_Quali_Nivel`). Para o ecrã mostrar
nomes, é preciso "juntar" as tabelas referenciadas.

### `LEFT JOIN` para relações opcionais

`ModuloDao::getAll` usa `left join Quali_modulo` porque um módulo **pode** não ter registo em
`Quali_modulo`. Com `LEFT JOIN`, o módulo aparece mesmo sem essa ligação (o `semestre` vem `NULL`).

### Subqueries com `group_concat`

`QualificacaoDao::getAll` junta **todos** os campos e **todos** os níveis numa única string por
qualificação:

```sql
(select group_concat(c.nome order by c.nome separator ', ')
   from Classificacao cl join Campo c on c.codigo = cl.cod_Campo
  where cl.cod_Qualificacao = q.cod_Quali) as campo
```

Resultado: `campo = "Informatica, Redes"`. Evita *N+1 queries* e o `GROUP BY` na query principal.
`Quali_NivelDao::getPares` usa ainda `ifnull(group_concat(...), 'Sem campo')` para tratar o vazio.

### `CAST` para pesquisa em datas

`InscricaoDao::getAll` procura também por data, convertendo-a para texto:

```sql
cast(i.data_inscricao as CHAR) like ?
```

### `concat` para listas de opções

Os combos (dropdowns) usam `concat` para construir a descrição:

```sql
select f.codigo as codigo, concat(f.nome, ' ', f.apelido) as descricao from Formador f
```

Contrato implícito: os `listarOpcoes()`/`listaOpcoes()` esperam sempre as colunas **`codigo`** e
**`descricao`**.

---

## 17. Deteção de conflitos de horário

Uma das peças mais interessantes: `LicaoDao::existeConflito()`.

```sql
select count(*) as total from Licao
where cod_Sala = ? and cod_Formador = ? and data = ?
  and hora_inicio < ? and hora_fim > ?
```

Os dois últimos `?` recebem, **por esta ordem**, `horaFim` (nova) e `horaInicio` (nova):

```php
$params = [$codSala, $codFormador, $data, $horaFim, $horaInicio];
```

### A matemática (sobreposição de intervalos)

Dois intervalos `[A_ini, A_fim]` e `[B_ini, B_fim]` **sobrepõem-se** se:

```
A_ini < B_fim  E  A_fim > B_ini
```

Aqui, a nova lição é `B` e a existente é `A`:
`A.hora_inicio < B.hora_fim` **e** `A.hora_fim > B.hora_inicio`.

Se houver interseção **na mesma sala, no mesmo formador e no mesmo dia** → conflito. Na edição,
exclui-se a própria lição com `and codigo <> ?`.

Complementarmente, `LicaoController::dadosValidos()` valida:
- `$this->data($data)`, `$this->hora($inicio)`, `$this->hora($fim)` (trait `Validacao`);
- `$hora_fim > $hora_inicio` (comparação de strings `HH:MM` funciona lexicograficamente).

### Normalização de `TIME`

A coluna `hora_*` é `TIME` (`HH:MM:SS`), mas o formulário envia `HH:MM`. Então:
- **Ao gravar:** `$horaInicio = $licao->getHoraInicio() . ":00"` (acrescenta segundos).
- **Ao ler:** `substr($rs['hora_inicio'], 0, 5)` (corta os segundos para apresentar `HH:MM`).

---

## 18. Integridade em código

As chaves estrangeiras **já** impedem apagar registos usados, mas o projeto valida **antes** para dar
mensagens amigáveis e controlar a ordem das operações.

### Padrão `existeXxx()` (COUNT > 0)

Centenas de verificações deste tipo:

```php
public function existeLicaoPorFormador($codigoFormador) {
    $sql = "select count(*) as total from Licao where cod_Formador = ?";
    ...
    return $rs['total'] > 0;
}
```

### Bloqueio de remoção no Controller

`FormadorController::apagarFormador`:

```php
if ($licaoController->existeLicaoPorFormador($codigo)) throw new Exception("...está sendo usado em lições.");
if ($coordenadorController->existeCoordenadorEmQualificacao($codigo)) throw new Exception("...é coordenador...");
if ($turmaController->existeTurmaComDiretor($codigo)) throw new Exception("...é diretor de turma...");
// só depois de todas as verificações:
$coordenadorController->apagarCoordenador($codigo);
$diretorTurmaController->apagarDiretor($codigo);
$this->dao->delete($codigo);
```

**Ordem importa:** apagar primeiro das tabelas "filhas" (`Coordenador`, `Diretor_Turma`) e só no fim a
"mãe" (`Formador`), respeitando as FKs.

### `QualificacaoDao` tem verificações para todas as dependências

`existeQualificacaoEmClassificacao`, `...EmQualiNivel`, `...EmQualiModulo`, `...EmModulo`, `...EmTurma`.
Cobre cada caminho pelo qual uma qualificação pode estar "presa".

---

## 19. Migrações

O ficheiro `database/migrations/20261009_qualificacao_nivel.sql` **tem extensão `.sql` mas é PHP**:

```php
<?php
defined('DOCUMENT_ROOT') or exit('Acesso directo directo não permitido.');
$sql_add_coluna = "ALTER TABLE `Matricula` ADD COLUMN `id_Quali_Nivel` INT NOT NULL DEFAULT 0 ...";
...
echo "Migração ... aplicada.\n";
```

Detalhes técnicos:

- O `defined('DOCUMENT_ROOT') or exit(...)` impede acesso direto pelo browser (só corre incluído por
  outro script). É um "guarda" simples.
- É uma migração **documentada, não automatizada**: define as strings SQL e imprime instruções; o
  administrador executa os comandos no MySQL pela ordem indicada.
- A migração trata o caso **ambíguo** (qualificação com vários níveis) deixando a decisão ao humano,
  o que é correto: uma migração automática "adivinharia" mal.

> Não existe tabela de controlo de migrações (tipo `schema_migrations`). É um processo manual.

---

## 20. Tipos de dados problemáticos

| Coluna | Tipo no schema | Problema / nota |
|---|---|---|
| `Log.data` | `varchar(19)` | Guarda `d/m/Y H:i:s` como texto; não permite comparações de data em SQL. |
| `Matricula.data` | `varchar(10)` | Guarda data como texto; sem validação de tipo na BD. |
| `Formador.email` | `varchar(40)` | O formulário usa `maxlength="100"` → **incompatível**; emails > 40 podem falhar/truncar. |
| `Formador.contacto` | `int` + `UNIQUE` | Não permite números com `+`/espaços; dois formadores não podem ter o mesmo contacto. |
| `Usuario.password` | `varchar(60)` | Exatamente o tamanho de um hash bcrypt; sem margem para outros algoritmos. |
| `Licao.hora_*` | `time` | Exige `HH:MM:SS`; o código acrescenta `:00`. |

**Lição:** quando um tipo não corresponde ao domínio (datas como texto), a validação passa a depender
**inteiramente** do código, e parte-se a capacidade de fazer queries por data de forma nativa.

---

## 21. Armadilhas específicas

### 21.1 Dependências implícitas entre ficheiros

- `Dao/DashboardDao.php` usa `new Database()` mas **não** faz `require config/conexao.php`. Funciona
  apenas porque `DashboardController` é carregado depois de `Sessao` (que carrega `conexao`).
- `Dao/InscricaoDao.php` chama `Sessao::obterUtilizador()` mas **não** faz `require services/Sessao.php`.
  Idem: depende de já vir carregado.
- **Risco:** se reutilizares estes DAOs num contexto diferente, dá *fatal error: class not found*.
  **Boa prática:** cada ficheiro deve declarar as suas próprias dependências.

### 21.2 Variável partilhada `header` → `sidebar`

`view/partials/sidebar.php` usa `$sessao_atual`, que é definida **em `header.php`**. Portanto:

- **A ordem é obrigatória:** `require header.php` **antes** de `sidebar.php`.
- Se um dia separares os partials, tens de recriar `$sessao_atual` no `sidebar`.

### 21.3 `$page_title` e `$active_menu`

Ambas são definidas **pela página** antes de requerer o `header`/`sidebar`:

```php
$page_title  = 'Gestão de Formadores';
$active_menu = 'professor';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
```

O `header` lê `$page_title`; o `sidebar` lê `$active_menu`. Se não as definires, usa *defaults*
(`'Gestao Academica'` e `'dashboard'`).

### 21.4 Caminhos relativos

Todos os `require`/`header('Location: ...')`/`<img src>`/`<link href>` são **relativos à pasta do
ficheiro atual**. Mover um `index.php` de nível obriga a rever **todos** os caminhos. É a principal
fonte de erros "file not found" e redirecionamentos partidos.

### 21.5 Case-sensitivity

- **Classes** PHP: *case-insensitive* (`Usuariocontroller` = `UsuarioController`) → funciona, mas é
  inconsistente e confuso.
- **Métodos/propriedades**: *case-sensitive* → `$obj->getname()` **não** é o mesmo que `$obj->getNome()`.
- **Sistema de ficheiros** (Linux): `require 'Dao/FormadorDao.php'` falha se o ficheiro for
  `FormadorDAO.php`. **Nos caminhos, o nome tem de ser exato.**

### 21.6 `display_errors` ligado

`Dao/PerfilDao.php`, `view/login/*.php` e `view/Campo/index.php` fazem `ini_set('display_errors', 1)`.
Em produção, isto **expõe caminhos e detalhes** ao utilizador. Deve ser controlado por `php.ini`, não
por ficheiro.

### 21.7 `getAll()` sem valor por defeito

Métodos como `ModuloDao::getAll($nome)`, `TurmaDao::getAll($nome)`, `InscricaoDao::getAll(...)` **exigem**
o parâmetro. Se chamares `getAll()` sem argumento → *ArgumentCountError*. Os controllers já passam
`$pesquisa ?? ''`.

### 21.8 `mysqli_sql_exception` não apanhada

Alguns `execute()` (ex.: `TurmaDao::create`) **não** estão em `try/catch`. Em caso de violação de
constraint (ex.: FK inexistente), a exceção **propaga** e pode rebentar a página. Os controllers que
cobrem esses casos envolvem-nos em `try { ... } catch (Exception $e)`.

---

## 22. Checklist de revisão técnica

Ao alterar código, verifica:

- [ ] Os `require_once` necessários estão no topo do ficheiro alterado?
- [ ] Caminhos relativos corretos (nível de pasta)?
- [ ] `bind_param`: a **contagem** de tipos = número de `?`, e a **ordem** corresponde?
- [ ] Escritas sensíveis a constraint protegidas com `try/catch (mysqli_sql_exception)`?
- [ ] Operações multi-tabela dentro de `begin_transaction`/`commit`/`rollback`?
- [ ] Todas as queries com valores usam **prepared statements** (sem interpolar `$_POST`)?
- [ ] Nomes de tabela dinâmicos protegidos por **lista branca**?
- [ ] Saída de dados do utilizador com `htmlspecialchars()`?
- [ ] Depois de gravar: `flash` + `header('Location: ...')` + **`exit`**?
- [ ] A página está protegida (`exigirLogin`/`exigirLeitura`) e o POST com `exigirAcao`?
- [ ] Se alteraste permissões: preciso de **logout/login** (ou `recarregarPermissoes()`)?
- [ ] O `$page_title`/`$active_menu` estão definidos antes dos partials?
- [ ] Alteraste limites de validação nos **dois** sítios (View e Controller)?
- [ ] Se mudaste um schema: atualizaste o `.sql`, o Model (construtor), o DAO (`INSERT`/`UPDATE`/`montarX`/`bind_param`) e a View?

---

## Apêndice — Mapa "conceito técnico → onde está no código"

| Conceito | Ficheiro/linhas de referência |
|---|---|
| Singleton de ligação | `config/conexao.php` |
| Sessão + cookie seguro | `services/Sessao.php` (`iniciar`, `login`) |
| RBAC / matriz de permissões | `Dao/PerfilPermissaoDao.php`, `services/Sessao.php` (`pode`, `carregarPermissoes`) |
| Fallback de permissões | `services/Sessao.php` (`$matrizLegada`, `construirMatrizLegada`) |
| Hashing de password | `controller/UsuarioController.php`, `Dao/UsuarioDao.php` (`autenticar`, `atualizarPassword`) |
| Validação | `controller/Validacao.php` (trait `Validacao`) |
| Prepared statements (variádico) | `Dao/LicaoDao.php` (`existeConflito`) |
| Transações | `Dao/QualificacaoDao.php` (`deleteCompleto`), `Dao/PerfilPermissaoDao.php` (`substituir`) |
| Lista branca de tabelas | `Dao/DashboardDao.php` (`contar`) |
| `group_concat`/subqueries | `Dao/QualificacaoDao.php` (`getAll`), `Dao/Quali_NivelDao.php` (`getPares`) |
| Deteção de conflito de horários | `Dao/LicaoDao.php` (`existeConflito`), `controller/LicaoController.php` |
| Bloqueio de remoção | `controller/FormadorController.php` (`apagarFormador`), `controller/TurmaController.php` |
| PRG + flash | qualquer `view/<modulo>/index.php` |
| Modais (CSS `:target`) | `view/<modulo>/index.php` + `assets/css/style.css` |
| Migração manual | `database/migrations/20261009_qualificacao_nivel.sql` |

---

## Resumo técnico numa frase

> O sistema é **PHP puro com MVC+DAO**: cada página faz `require` explícito, valida com o trait `Validacao`,
> persiste por **prepared statements** (`mysqli`) protegidos por *listas brancas*, envolve operações
> delicadas em **transações** e `try/catch`, guarda o acesso em **sessões** com RBAC em matriz
> (`recurso × ação`), protege a saída com `htmlspecialchars`, e usa **PRG + flash** e **modais por CSS**
> para dar uma experiência de SPA sem JavaScript.
