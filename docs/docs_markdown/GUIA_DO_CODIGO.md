# Guia Completo do Código — Sistema de Gestão Académica

> Documento feito para uma pessoa que **está a aprender a programar** ("leigo meio programador").
> Objetivo: entender **o código de ponta a ponta**, saber **onde está cada coisa**, **o que acontece
> quando clicas num botão** e **como resolver erros**.
>
> Lê este ficheiro de cima para baixo na primeira vez. Depois usa o **índice** e o **catálogo de
> ficheiros** como dicionário.
>
> 📎 Documento complementar (detalhe de engenharia): **`EXPLICACAO_TECNICA.md`** — mesmo diretório,
> focado nos mecanismos internos (prepared statements, sessões, RBAC, transações, segurança, etc.).

---

## Índice

1. [O que é este sistema (visão de negócio)](#1-o-que-é-este-sistema)
2. [A arquitetura MVC explicada para leigos](#2-arquitetura-mvc-para-leigos)
3. [Mapa das pastas](#3-mapa-das-pastas)
4. [Como o sistema corre (ambiente)](#4-como-o-sistema-corre)
5. [Camada de Configuração — `config/`](#5-camada-de-configuração)
6. [Camada de Serviços — `services/`](#6-camada-de-serviços)
7. [Camada Model — `model/`](#7-camada-model)
8. [Camada DAO — `Dao/` (acesso ao banco)](#8-camada-dao)
9. [Camada Controller — `controller/`](#9-camada-controller)
10. [Camada View — `view/` (o ecrã)](#10-camada-view)
11. [A base de dados — `database/`](#11-a-base-de-dados)
12. [Permissões (quem pode fazer o quê)](#12-permissões-rbac)
13. [Logs / auditoria](#13-logs--auditoria)
14. [Fluxos ponta a ponta (walkthroughs)](#14-fluxos-ponta-a-ponta)
15. [Guia de depuração e resolução de erros](#15-guia-de-depuração-e-erros)
16. [Receitas: como fazer tarefas comuns](#16-receitas)
17. [Convenções e dívidas técnicas](#17-convenções-e-dívidas-técnicas)
18. [Glossário](#18-glossário)

---

## 1. O que é este sistema

É um **sistema de gestão académica** (estilo LMS) escrito em **PHP puro** (sem frameworks como Laravel)
com **MySQL/MariaDB**. Serve para gerir:

| Conceito | Explicação simples |
|---|---|
| **Formador** | O professor/instrutor. É a entidade "base". |
| **Diretor de Turma** | Um **tipo especial** de formador (aponta para a tabela `Diretor_Turma`). |
| **Coordenador** | Outro **tipo especial** de formador (tabela `Coordenador`). |
| **Formando** | O aluno. |
| **Campo** | Área de formação (ex: "Informática"). |
| **Qualificação** | Curso/qualificação (ex: "Técnico de Programação Web") — pertence a um Campo e tem um Coordenador. |
| **Nível** | Nível da qualificação (ex: CV3, CV4, CV5). |
| **Quali_Nivel** | A **ligação** entre uma Qualificação e um Nível (uma qualificação pode ter vários níveis). |
| **Módulo** | Unidade curricular (ex: "SUBDS") — pertence a um par Qualificação+Nível. |
| **Turma** | Grupo de formandos numa qualificação+nível, com um Diretor de Turma. |
| **Matrícula** | O formando matricula-se numa Qualificação+Nível. |
| **Inscrição** | O formando inscreve-se num **Módulo**, num semestre. |
| **Lição** | Aula: módulo + formador + sala + turma + data/hora. |
| **Sala** | Local da aula. |
| **Utilizador** | Quem entra no sistema (login). Tem um **Perfil**. |
| **Perfil** | Papel: `Operador`, `SuperOperador`, `Administrador`, `Auditor`. |
| **Permissão** | O que cada perfil pode fazer em cada recurso (criar/consultar/editar/eliminar). |
| **Log** | Registo de auditoria (quem fez o quê e quando). |

O fluxo de dados do negócio é, em resumo:

```
Formador ─(é)─> Coordenador ─(coordena)─> Qualificacao ─(tem)─> Quali_Nivel <─(liga)─ Nivel
                                                                    │
                                                              Modulo ─┬─> Inscricao <─ Formando
Qualificacao ─(classificada em)─> Classificacao <─ Campo               │
                                                              Turma ───┴─> Licao <─ Sala
Turma ─(dirigida por)─> Diretor_Turma (um Formador)
```

---

## 2. Arquitetura MVC para leigos

**MVC** = *Model – View – Controller*. É uma forma de separar responsabilidades para o código não virar
uma bola de lama. Neste projeto a separação é:

- **Model** (`model/`) → a **forma dos dados**. Uma classe simples com atributos (`$nome`, `$email`) e
  métodos `get`/`set`. Não sabe nada de base de dados nem de HTML.
- **DAO** (`Dao/`) → *Data Access Object*. É quem **fala com a base de dados** (SQL). Converte linhas da
  tabela MySQL em objetos Model, e vice-versa.
- **Controller** (`controller/`) → o **cérebro da operação**. Recebe pedidos, valida, chama o DAO e
  decide o que devolver. Não escreve HTML diretamente.
- **View** (`view/`) → o que o **utilizador vê**. HTML + PHP que imprime dados. Aqui vivem também os
  ecrãs, formulários e modais.

> **Importante neste projeto:** a View não é só "bonita". Cada `view/.../index.php` é um
> **mini controlador de página**: ele trata do `POST`, chama o Controller e depois desenha o HTML.
> Isto é chamado de *Front Controller pattern* simplificado.

### Analogia de restaurante

Pensa num restaurante:

- **View** = o menu e o prato que vês na mesa.
- **Controller** = o empregado de mesa: recebe o teu pedido, verifica se faz sentido, leva à cozinha.
- **DAO** = o cozinheiro que vai à despensa (base de dados) buscar os ingredientes.
- **Model** = a ficha padronizada do ingrediente (nome, quantidade, preço).

---

## 3. Mapa das pastas

```
Gestao_Academica/
├── config/            # ligação à base de dados
│   └── conexao.php
├── services/          # serviços transversais (sessão/login)
│   └── Sessao.php
├── model/             # 21 modelos (só dados + getters/setters)
├── Dao/               # 23 DAOs (SQL + logs)
├── controller/        # 21 controllers (regras + orquestração)
│   └── Validacao.php  # trait de validação herdado pelos controllers
├── view/              # ecrãs (HTML+PHP) organizados por módulo
│   ├── partials/      # header.php, sidebar.php, footer.php (partilhados)
│   ├── login/         # index.php, redefinir.php, logout.php
│   ├── dashboard/
│   ├── formadores/  formando/  turma/  modulo/  qualificacao/
│   ├── nivel/  sala/  Campo/  inscricao/  matricula/  licao/
│   ├── usuario/  perfil/  permissoes/  log/
├── assets/            # css e ícones (não é PHP)
├── database/          # scripts SQL (schema + dados + migrações)
├── docs/              # documentação (este guia vive em docs/docs_markdown/)
├── README.md
└── .php-preview-router.php   # ficheiro auxiliar de pré-visualização (ver §4)
```

Regra de ouro do projeto: **um módulo = 1 model + 1 DAO + 1 controller + 1 (ou mais) view(s)**.

---

## 4. Como o sistema corre

- **Linguagem:** PHP (usa `mysqli` e sessões nativas).
- **Base de dados:** MySQL 8 / MariaDB, base de dados chamada `GestaoProfessor`.
- **Sem framework, sem Composer**: as dependências são carregadas com `require_once` explícito no topo
  de cada ficheiro.
- **Ponto de entrada:** o utilizador abre diretamente os ficheiros da pasta `view/` no browser, por
  exemplo `.../view/login/index.php` ou `.../view/dashboard/index.php`. **Não há um único `index.php`
  central** — cada ecrã é acedido diretamente.

### Servidor de desenvolvimento rápido

```bash
# na raiz do projeto
php -S localhost:8000
# depois abrir: http://localhost:8000/view/login/index.php
```

### `.php-preview-router.php`

É um **router auxiliar** usado por ferramentas de pré-visualização (por exemplo o preview do editor).
Ele responde a pedidos `POST` com um JSON de eco (`method`, `data`, `files`, `timestamp`) e serve
ficheiros estáticos com o *content-type* correto. **Não faz parte da lógica de negócio** — podes
ignorá-lo em produção.

### Erros ligados/desligados

Alguns ficheiros fazem:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Isto **mostra os erros no ecrã** (bom para desenvolver, mau para produção). Aparece no
`view/login/index.php`, `view/login/redefinir.php`, `view/Campo/index.php`, `Dao/PerfilDao.php`.
Se vês sempre os erros ligados em todos os ecrãs, é porque `php.ini` também os tem ligados.

---

## 5. Camada de Configuração

### `config/conexao.php`

```php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "GestaoProfessor";
    private $user_name = "root";
    private $password = "Malikdb123!";      // ⚠️ password em texto simples no código
    private static $conn = null;            // padrão Singleton

    public function getConnection(){
        if (self::$conn === null) {
            try {
                self::$conn = new mysqli($this->host, $this->user_name, $this->password, $this->db_name);
                if (self::$conn->connect_error) { throw new Exception(...); }
            } catch (Exception $e) {
                die("Falha na base de dados: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
```

**O que precisas de saber:**

- É um **Singleton**: a ligação só é criada **uma vez** por pedido e reaproveitada (`self::$conn`).
  Todos os DAOs fazem `new Database(); $this->db = $database->getConnection();` no construtor.
- Se a base de dados não ligar, o `die()` **mata a página** e mostra "Falha na base de dados: ...".
  → Este é o erro nº 1 que vais ver. Ver §15.
- ⚠️ **Dívida técnica:** as credenciais estão *hardcoded*. O ideal seria lê-las de variáveis de
  ambiente (`getenv(...)`) ou de um ficheiro `.env` fora do controlo de versões.

---

## 6. Camada de Serviços e validação

### 6.1 Validação — o trait `controller/Validacao.php`

A validação vive num **trait** (`Validacao`), integrado diretamente nas classes que a usam
(`use Validacao;`). Já **não existe** a antiga classe de serviço `services/Validador.php`.

- **No controller:** usa-se `$this->texto(...)`, `$this->data(...)`, `$this->inteiroPositivo(...)`.
- **Na View:** usa-se `$controller->texto(...)`, `$controller->data(...)`, ... (os métodos passam a
  fazer parte da instância do controller graças ao trait).

Devolve `true`/`false`.

| Método | O que valida |
|---|---|
| `texto($valor, $maximo=60)` | Não vazio, dentro do limite, e só letras/números/espaços e alguns sinais `.,'’()-/`. Devolve `false` se tiver caracteres estranhos. |
| `digitos($valor, $maximo=20)` | Vazio OK; senão só dígitos, dentro do limite. |
| `email($valor)` | Vazio OK; senão formato de e-mail válido. |
| `emailObrigatorio($valor)` | **Não** pode ser vazio e tem de ser e-mail válido. |
| `inteiroPositivo($valor)` | É inteiro **e** > 0. |
| `data($valor)` | Formato `Y-m-d` real (ex: `2026-10-16`). |
| `hora($valor)` | Formato `HH:MM` válido |

Exemplo de uso (em controllers): `$this->texto($nome, 40) && $this->emailObrigatorio($email)`.

> **Porquê validar em dois sítios?** A validação "leve" (obrigatório, tamanho) aparece também na View
> para dar mensagens bonitas; a validação "a sério" (regras de negócio) está no Controller
> para nunca deixar entrar dados errados na base de dados. Como View e Controller usam o **mesmo
> trait**, a regra está definida num único sítio.

### 6.2 `services/Sessao.php` — o coração da autenticação e permissões

Classe `final` (não pode ser estendida) só com métodos **estáticos**. É ela que:

1. **Inicia a sessão PHP** (`iniciar()`), com cookie `httponly` e `samesite=Lax`, duração 7200s.
2. **Guarda quem está logado** em `$_SESSION`: `user_id`, `username`, `nome`, `apelido`, `idPerfil`,
   `perfil` (nome do perfil) e `primeiro_acesso`.
3. **Carrega as permissões** para `$_SESSION['permissoes']`.
4. **Protege páginas** com `exigirLogin`, `exigirLeitura`, `exigirAcao`, `exigirPerfil`.

#### Constantes (atalhos que evita escrever strings à mão)

```php
const ACAO_CRIAR='C'; const ACAO_LEITURA='R'; const ACAO_EDITAR='U'; const ACAO_REMOVER='D';
const RECURSO_FORMADORES='formadores'; const RECURSO_TURMAS='turmas'; ... etc
const ADMIN='Administrador'; const SUPER='SuperOperador'; const OPERADOR='Operador'; const AUDITOR='Auditor';
```

#### Métodos principais

| Método | Para que serve |
|---|---|
| `iniciar()` | Arranca a sessão (só se ainda não estiver iniciada). |
| `esta_logado()` | `true` se existe `$_SESSION['user_id']`. |
| `utilizador()` | Devolve um **array** com os dados da sessão. |
| `obterUtilizador()` | Devolve um **objeto `Usuario`** reconstruído (usado pelos DAOs para logs). |
| `login(Usuario $u)` | Regenera o ID de sessão (previne *session fixation*) e grava os dados + carrega permissões. |
| `logout()` | Limpa e destrói a sessão. |
| `exigirLogin($destino)` | Se não estiver logado → redireciona para `$destino`. Também bloqueia "primeiro acesso". |
| `exigirLeitura($recurso,$destino)` | Exige estar logado **e** ter permissão de leitura nesse recurso. |
| `exigirAcao($acao,$recurso,$destino)` | Exige estar logado **e** ter a ação (C/R/U/D) no recurso; senão mostra flash de erro. |
| `pode($acao,$recurso)` | Devolve `true`/`false` (usado nas Views para mostrar/esconder botões). |
| `podeCriar/podeLer/podeEditar/podeRemover($recurso)` | Atalhos para `pode()`. |
| `primeiroAcesso()` / `bloquearPrimeiroAcesso()` | Se o utilizador está no primeiro acesso, força-o a ir para `redefinir.php`. |

#### Como as permissões são guardadas

`carregarPermissoes()` vai ao `PerfilPermissaoDao` buscar uma **matriz** para o perfil e guarda-a em
`$_SESSION['permissoes']` com esta forma:

```php
$_SESSION['permissoes'] = [
    'formadores' => ['consultar' => true, 'criar' => true, 'editar' => true, 'eliminar' => true],
    'logs'       => ['consultar' => true],
    // ...
];
```

`pode('C', 'formadores')` traduz `'C'` → `'criar'` (via `$apelidoAcao`) e verifica a matriz.

> **Compatibilidade (*fallback* legado):** se a base de dados não tiver permissões para o perfil,
> o código usa uma matriz fixa em `$matrizLegada` (Administrador/SuperOperador/Operador/Auditor).
> Isto garante que o sistema funciona mesmo em bases antigas.

---

## 7. Camada Model

Os modelos são **muito simples**: guardam dados e expõem `getX()`/`setX()`. Não têm SQL nem HTML.

### Catálogo de modelos

| Ficheiro | Classe | Chave/atributos principais |
|---|---|---|
| `model/Formador.php` | `Formador` | codigo, nome, apelido, email, genero, estadoCivil, contacto, valor_horas, horas_mes, salario |
| `model/Diretor_turma.php` | `Diretor_turma` | formador (um `Formador`) |
| `model/Coordenador.php` | `Coordenador` | formador (um `Formador`) |
| `model/Formando.php` | `Formando` | codigo, nome, apelido, email, bi, contacto |
| `model/Campo.php` | `Campo` | codigo, nome |
| `model/Qualificacao.php` | `Qualificacao` | codigo, titulo, cod_coordenador, campo, niveis |
| `model/Nivel.php` | `Nivel` | codigo, nome |
| `model/Quali_Nivel.php` | `Quali_Nivel` | codigo, cod_quali, cod_nivel, qualificacao, nivel |
| `model/Modulo.php` | `Modulo` | codigo, nome, carga_horaria, quali_modulo, quali_nivel |
| `model/Quali_modulo.php` | `Quali_modulo` | codigo, semestre, modulo, qualificacao |
| `model/Turma.php` | `Turma` | codigo, nome, ano_ingresso, turno, diretor_turma, qualificacao, quali_nivel |
| `model/Matricula.php` | `Matricula` | codigo, formando, qualificacao, nivel, id_quali_nivel, data_matricula |
| `model/Inscricao.php` | `Inscricao` | codigo, formando, modulo, semestre, data_inscricao |
| `model/Licao.php` | `Licao` | codigo, modulo, formador, sala, turma, data, hora_inicio, hora_fim |
| `model/Sala.php` | `Sala` | codigo, designacao, tipo_sala |
| `model/Classificacao.php` | `Classificacao` | codigo, campo, qualificacao |
| `model/Logs.php` | `Logs` | codigo, acao, descricao, usuario, data |
| `model/Usuario.php` | `Usuario` | codigo, idPerfil, nome, username, password, apelido, estadoCivil, genero, telefone, email, bi, perfil, primeiroAcesso |
| `model/Perfil.php` | `Perfil` | id, nome (tem `__toString()` → devolve o nome) |
| `model/Permissao.php` | `Permissao` | id, nome |
| `model/Recurso.php` | `Recurso` | id, nome, grupo |

**Detalhe importante:** alguns modelos guardam **objetos** como atributos (composição), por exemplo
`Matricula` guarda um `Formando` e uma `Qualificacao`; `Licao` guarda `Modulo`, `Formador`, `Sala`,
`Turma`. Isto é o que permite escrever `$licao->getFormador()->getCodigo()`.

---

## 8. Camada DAO

Todos os DAOs seguem o **mesmo esqueleto**. Aprende um e aprendeste todos.

### Esqueleto-tipo

```php
<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';   // para saber quem fez a ação (logs)
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class FormadorDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Formador $formador) {
        $sql = "insert into Formador (...) values (?, ?, ...)";
        $stmt = $this->db->prepare($sql);
        $nome = $formador->getNome();
        // ...
        $stmt->bind_param("sssssiiid", $nome, ...);   // "s"=string, "i"=inteiro, "d"=decimal
        $result = $stmt->execute();
        if ($result) {
            $formador->setCodigo($this->db->insert_id); // captura o ID gerado
            $this->registarLog("INSERT", "...");
        }
        return $result;
    }

    public function getAll($pesquisa = null) { /* select ... (+ like ? se pesquisa) */ }
    public function getById($codigo)          { /* select ... where codigo = ? */ }
    public function update(Formador $f)       { /* update ... where codigo = ? */ }
    public function delete($codigo)           { /* delete ... where codigo = ? */ }

    private function montarFormador($rs) { /* linha MySQL → new Formador(...) */ }
    private function registarLog($acao, $descricao) { /* cria Logs e chama LogDao->salvar */ }
}
```

### Regras que TODOS os DAOs seguem

1. **Prepared statements sempre** (`prepare` + `bind_param`). Nunca concatenar valores do utilizador
   no SQL → evita *SQL Injection*. Os `bind_param` usam a letra do tipo:
   - `s` = string, `i` = inteiro, `d` = *double*/decimal.
2. **Um método `montarX($rs)`** que transforma o array da linha (`fetch_assoc`) no objeto Model.
3. **`getAll($pesquisa)`** monta o `WHERE ... LIKE ?` **apenas** se a pesquisa não for vazia.
4. **Escritas (create/update/delete) gravam log** com o utilizador da sessão
   (`Sessao::obterUtilizador()`).
5. **Erros de constraint** (ex: `username` duplicado) são apanhados com `catch (mysqli_sql_exception $e)`
   e/ou convertidos em `Exception` com mensagem amigável.

### Catálogo de DAOs

| DAO | Tabela(s) | Operações notáveis |
|---|---|---|
| `FormadorDao` | `Formador` | CRUD completo; `insert_id` guardado no objeto. |
| `FormandoDao` | `Formando` | CRUD; pesquisa por nome/apelido/email/BI. |
| `UsuarioDao` | `Usuario` + `Perfil` | CRUD; `getByUsername`, `atualizarPassword`, `registarLogin`, `registarLogout`, `contarPorPerfil`. |
| `PerfilDao` | `Perfil` | CRUD de perfis. |
| `RecursoDao`, `PermissaoDao`, `PerfilPermissaoDao` | `recurso`, `permissao`, `perfil_permissao` | Base do RBAC. `PerfilPermissaoDao::getMatrizByPerfil()` devolve a matriz de permissões. |
| `CampoDAO`, `NivelDAO`, `SalaDAO` | `Campo`, `Nivel`, `Sala` | CRUD simples + pesquisa. |
| `ClassificacaoDao`, `Quali_NivelDao`, `Quali_moduloDao` | tabelas de associação | Ligar entidades. |
| `QualificacaoDao` | `Qualificacao` | `getAll` com `group_concat` para juntar campos e níveis. |
| `ModuloDao` | `Modulo` (+ `Quali_modulo`) | CRUD; valida par Qualificação+Nível. |
| `TurmaDao` | `Turma` (+ `Quali_Nivel`, `Diretor_Turma`) | CRUD; `existeTurmaComDiretor`. |
| `MatriculaDao` | `Matricula` | CRUD; junta Formando/Qualificação/Nível. |
| `InscricaoDao` | `Inscricao` | CRUD; junta Formando/Módulo. |
| `LicaoDao` | `Licao` | CRUD; `existeLicaoPorFormador`. |
| `LogDao` | `Log` | `salvar()` e listagens com filtro. |
| `Diretor_TurmaDao`, `CoordenadorDao` | `Diretor_Turma`, `Coordenador` | `create`, `isDiretor/isCoordenador`, `getAll`, `delete`. |
| `DashboardDao` | várias | `contar($tabela)` — **só** com uma lista branca de tabelas permitidas. |
| `ExceptionDao` | — | Subclasse de `Exception` (mensagens de erro dos DAOs). |

### Exemplo concreto: `DashboardDao::contar()`

```php
public function contar($tabela) {
    $permitidas = ['Formador','Formando','Turma','Modulo','Qualificacao','Nivel','Sala','Campo','Matricula','Inscricao','Licao','Usuario'];
    if (!in_array($tabela, $permitidas, true)) {
        throw new InvalidArgumentException("Tabela nao permitida na contagem: " . $tabela);
    }
    $sql = "select count(*) as total from `" . $tabela . "`";
    ...
}
```

Repara: aqui o nome da tabela é **concatenado** no SQL (não pode ser `?` num `prepare`),
por isso existe a **lista branca** — só nomes conhecidos são aceites. Isto é uma boa prática de
segurança. Vais rever este padrão (lista branca) noutros sítios.

---

## 9. Camada Controller

Os controllers **orquestram**: validam, criam o Model e chamam o DAO. São finos de propósito.

### Dois estilos que vais encontrar

**Estilo A — "método por operação" (a maioria):**

```php
class NivelController {
    private $dao;
    public function __construct() { $this->dao = new NivelDao(); }
    public function listar($pesquisa = null) { return $this->dao->getAll($pesquisa); }
    public function buscar($codigo)          { return $this->dao->getById($codigo); }
    public function store()   { $n = new Nivel(null, $_POST['nome']); return $this->dao->create($n); }
    public function update($codigo)        { return $this->dao->update(new Nivel($codigo, $_POST['nome'])); }
    public function delete($codigo)        { return $this->dao->delete($codigo); }
}
```

**Estilo B — "regras de negócio complexas"** (ex: `FormadorController`, `TurmaController`,
`ModuloController`, `QualificacaoController`): além do CRUD, coordenam **vários** DAOs.

### `FormadorController` — o exemplo mais rico

- `cadastrarFormador(...)`:
  1. Valida tudo com o trait `Validacao` (`$this->texto(...)`, `$this->emailObrigatorio(...)`, ...).
  2. Calcula `salario = valor_horas * horas_mes`.
  3. Cria o `Formador` no banco.
  4. Se `$isDiretor` → cria também em `Diretor_Turma`; senão se `$isCoordenador` → cria em `Coordenador`.
- `atualizarFormador(...)`: atualiza o formador e **sincroniza** os papéis especiais
  (se passou a diretor, cria; se deixou de ser, apaga). Usa `isDiretor()`/`isCoordenador()` para
  saber o estado atual.
- `apagarFormador($codigo)`: bloqueia a remoção se o formador **estiver a ser usado**:
  - tem lições (`LicaoController::existeLicaoPorFormador`), ou
  - é coordenador de qualificação, ou
  - é diretor de turma.
  Senão, apaga primeiro das tabelas especiais e só depois da tabela `Formador`.
- `getStatusFormador($codigo)`: devolve `[éDiretor, éCoordenador]` — usado pela View para marcar os
  checkboxes e mostrar os *badges*.

> ⚠️ **Nota:** na criação, a lógica usa `if/elseif` (um formador criado como Diretor **não** fica
> Coordenador na mesma ação, mesmo que ambos estejam marcados). Já na edição, os dois papéis são
> verificados de forma independente. É uma assimetria a ter em conta se mexeres aqui.

### Catálogo de controllers

| Controller | Papel |
|---|---|
| `UsuarioController` | Login (`autenticar`), CRUD de utilizadores, `resetSenha`, `redefinirSenha`. Senha padrão = **1234**. |
| `FormadorController` | CRUD + papéis especiais + bloqueios de remoção. |
| `Diretor_turmaController`, `CoordenadorController` | Gerir as especializações de formador. |
| `FormandoController` | CRUD de formandos. |
| `CampoController`, `NivelController`, `SalaController` | CRUD simples. |
| `QualificacaoController` | CRUD + relações (Campo via Classificação, Níveis via Quali_Nivel). |
| `Quali_NivelController`, `Quali_moduloController`, `ClassificacaoController` | Gerir ligações. |
| `ModuloController` | CRUD + validação de par Qualificação+Nível. |
| `TurmaController` | CRUD + Diretor de Turma + Quali_Nivel. |
| `MatriculaController`, `InscricaoController` | CRUD de matrículas/inscrições. |
| `LicaoController` | CRUD de horários + deteção de conflitos (`existeLicaoPorFormador`). |
| `DashboardController` | Contagens para o painel. |
| `PerfilController` | Perfis + permissões (recursos × permissões). |
| `LogController` | Listagem de logs com filtro. |
| `FicheiroDefault` | ⚠️ **Código morto** — escreve `senhasDefault/Nome.txt`. Já não é chamado por ninguém. |

---

## 10. Camada View

### 10.1 O padrão de uma página de listagem (`view/<modulo>/index.php`)

Todos os ecrãs de listagem seguem esta ordem. **Decora este "esqueleto"**, porque é 90% do projeto:

```php
<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');          // 1) guarda de segurança

require_once __DIR__ . '/../../controller/XController.php';
$controller = new XController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {        // 2) tratar formulários
    if (isset($_POST['gravar']))  { Sessao::exigirAcao(Sessao::ACAO_CRIAR,  RECURSO_X, 'index.php');
        /* ler $_POST, validar, chamar controller->store() */
        $_SESSION['flash'] = ['type'=>'success','msg'=>'...'];
        header('Location: index.php'); exit;        // PRG: Post/Redirect/Get
    }
    if (isset($_POST['editar']))  { /* ... editar ... */ }
    if (isset($_POST['deletar'])) { /* ... apagar ... */ }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');          // 3) ler dados de lista
$itens    = $controller->listar($pesquisa !== '' ? $pesquisa : null);
$flash    = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);  // 4) ler e limpar flash

$pode_criar = Sessao::podeCriar(RECURSO_X);         // 5) o que o utilizador pode fazer
// ...

$page_title  = 'Título';
$active_menu = 'menu';                              // 6) realçar item da sidebar
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <!-- 7) HTML: cabeçalho, alerta flash, pesquisa, tabela, modais -->
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
```

**Conceitos-chave desta estrutura:**

- **PRG (Post/Redirect/Get):** depois de gravar, o código faz `header('Location: index.php'); exit;`.
  Assim, se o utilizador atualizar a página (F5), **não reenvia** o formulário. Muito importante.
- **Flash messages:** a mensagem de sucesso/erro é guardada em `$_SESSION['flash']` e mostrada **uma
  única vez** no próximo carregamento (`unset` logo a seguir).
- **Botões condicionais:** `$pode_criar`, `$pode_editar`, `$pode_remover` escondem botões que o
  utilizador não pode usar. **Atenção:** esconder o botão é só *interface*; a segurança real está no
  `Sessao::exigirAcao(...)` no início de cada bloco POST.
- **Modais:** cada linha da tabela tem um `<div id="modal-editar-<codigo>">` e/ou
  `modal-deletar-<codigo>`. O botão é um link `#modal-editar-3`. O abrir/fechar é feito por CSS/JS de
  `assets/css/style.css`.

### 10.2 Ecrãs "não-listagem"

- `view/login/index.php` — formulário de login; em POST chama `UsuarioController::autenticar()`,
  faz `Sessao::login($u)` e redireciona (para `redefinir.php` se for primeiro acesso, senão dashboard).
- `view/login/redefinir.php` — trocar a senha (primeiro acesso ou voluntário). Mínimo de 6 caracteres
  e diferente da antiga.
- `view/login/logout.php` — registra o log de saída e destrói a sessão.
- `view/dashboard/index.php` — painel com contagens vindas de `DashboardController::resumo()`.
- `view/perfil/index.php` + `view/perfil/visualizar.php` — listar/criar perfis e ver a matriz de permissões.
- `view/permissoes/index.php` — **matriz de permissões** (linhas = recursos, colunas = criar/consultar/
  editar/eliminar); grava em `perfil_permissao`.
- `view/log/index.php` — listagem de auditoria com filtro por texto.
- `view/Campo/` — subpasta com `index.php`, `criar.php`, `editar.php`, `deletar.php` (versão mais
  antiga, com páginas separadas em vez de modais; o `index.php` já usa modais).

### 10.3 Catálogo de Views por módulo

| Pasta | Ficheiro(s) | Recurso de permissão |
|---|---|---|
| `view/dashboard/` | `index.php` | (só login) |
| `view/formadores/` | `index.php` | `formadores` |
| `view/formando/` | `index.php` | `formandos` |
| `view/turma/` | `index.php` | `turmas` |
| `view/modulo/` | `index.php` | `modulos` |
| `view/qualificacao/` | `index.php` | `qualificacoes` |
| `view/nivel/` | `index.php` | `niveis` |
| `view/sala/` | `index.php` | `salas` |
| `view/Campo/` | `index.php`,`criar.php`,`editar.php`,`deletar.php` | `campos` |
| `view/inscricao/` | `index.php` | `inscricoes` |
| `view/matricula/` | `index.php` | `matriculas` |
| `view/licao/` | `index.php` | `licoes` |
| `view/usuario/` | `index.php` | `utilizadores` |
| `view/perfil/` | `index.php`,`visualizar.php` | `perfis` |
| `view/permissoes/` | `index.php` | `perfis` |
| `view/log/` | `index.php` | `logs` |
| `view/login/` | `index.php`,`redefinir.php`,`logout.php` | (público) |

> **Nota sobre `_campos.php`:** antes existiam ficheiros partilhados (ex: `view/formadores/_campos.php`,
> `view/turma/_campos.php`, `view/licao/_campos.php`) com o formulário comum a "novo" e "editar". Foram
> **removidos** — o formulário está agora **inline** dentro de cada `index.php` (duplicado no modal de
> novo e no de editar). Mantém os dois em sincronia quando alterares campos.

### 10.4 Partial(s) partilhados — `view/partials/`

- `header.php` — abre o `<html>`, faz `Sessao::iniciar()`, lê o utilizador, imprime a barra superior
  (topbar). Usa `$page_title` (se definido) no `<title>`.
- `sidebar.php` — menu lateral. Usa `$active_menu` para marcar o item ativo e chama
  `Sessao::gestaoUtilizadores()`, `Sessao::auditoriaLogs()`, `Sessao::pode(...)` para **mostrar ou
  esconder** entradas do menu.
- `footer.php` — roda-pé, fecha `<body>`/`</html>`.

**Ordem obrigatória no fim de cada página:**
`header.php` → `sidebar.php` → o teu HTML → `footer.php`.

---

## 11. A base de dados

Ficheiros em `database/`:

| Ficheiro | Uso |
|---|---|
| `GestaoProfessorPHP2.sql` | **Schema + dados mais completo** (inclui RBAC). Usa este para criar a base. |
| `GestaoProfessordbPHP.sql` | Variante intermédia. |
| `GestaoProfessor.sql` | Versão antiga/inicial. |
| `migrations/20261009_qualificacao_nivel.sql` | Migração: adiciona `id_Quali_Nivel` à `Matricula` em bases antigas. |

### Tabelas e chaves

```
Campo(codigo PK, nome)
Classificacao(cod_Campo PK+FK→Campo, cod_Qualificacao PK+FK→Qualificacao)   -- N:M
Qualificacao(cod_Quali PK, titulo, cod_Coordenador FK→Coordenador)
Nivel(codigo PK, nome)
Quali_Nivel(codigo_Quali_Nivel PK, cod_Quali FK→Qualificacao, cod_Nivel FK→Nivel)
Modulo(codigo PK, nome_modulo, carga_horaria, id_Quali_Nivel FK→Quali_Nivel)
Quali_modulo(codigo PK, cod_modulo FK→Modulo, cod_Quali FK→Qualificacao, semestre)
Formador(codigo PK, nome, apelido, email, genero, estadoCivil, contacto UNIQUE, salario, valor_hora, horas_mes)
Diretor_Turma(cod_Formador PK+FK→Formador)          -- ISA: é um formador
Coordenador(cod_Formador PK+FK→Formador)            -- ISA: é um formador
Formando(codigo_formando PK, nome_formando, apelido_formando, contacto_formando, email, BI)
Turma(codigo PK, nome, ano_lectivo, turno, id_Diretor_Turma FK→Diretor_Turma, id_Quali_Nivel FK→Quali_Nivel)
Matricula(codigo PK, cod_formando FK→Formando, data, id_Quali_Nivel FK→Quali_Nivel, cod_Quali FK→Qualificacao)
Inscricao(codigo_inscricao PK, codigo_formando FK→Formando, codigo_modulo FK→Modulo, semestre, data_inscricao)
Sala(codigo PK, designacao, tipo_sala)
Licao(codigo PK, cod_Modulo FK→Modulo, cod_Formador FK→Formador, cod_Sala FK→Sala, cod_Turma FK→Turma, data, hora_inicio, hora_fim)
Perfil(id PK, nome)
Usuario(idUser PK, idPerfil FK→Perfil, nome, username UNIQUE, apelido, estadoCivil, genero, telefone, email, BI, password, primeiroAcesso)
Log(codigo PK, id_Usuario FK→Usuario, acao, descricao, data)
recurso(id PK, nome UNIQUE, grupo)
permissao(id PK, nome UNIQUE)        -- consultar/criar/editar/eliminar
perfil_permissao(id PK, perfil_id FK, recurso_id FK, permissao_id FK, UNIQUE(perfil,recurso,permissao))
```

### Padrões de modelação a notar

1. **Especialização "ISA" dos Formadores:** `Diretor_Turma` e `Coordenador` são tabelas com **apenas
   uma coluna** (`cod_Formador`), que é simultaneamente PK e FK para `Formador`. Ou seja, um diretor
   **é** um formador com uma "etiqueta" extra.
2. **Associações N:M** resolvidas por tabelas do meio: `Classificacao` (Campo↔Qualificação) e
   `Quali_Nivel` (Qualificação↔Nível).
3. **`Log.data` é `varchar(19)`** (texto `d/m/Y H:i:s`), não é um tipo `DATETIME`. Isso explica porque
   os DAOs fazem `date('d/m/Y H:i:s')` ao gravar.
4. **`Matricula.data` é `varchar(10)`**, não `DATE`.
5. **Restrições de integridade:** as FKs estão todas definidas, então apagar algo "usado" dá erro —
   daí os controllers verificarem antes de apagar e devolverem mensagens amigáveis.

### Como (re)criar a base de dados

```bash
mysql -u root -p < database/GestaoProfessorPHP2.sql
```
Isso **apaga** e recria todas as tabelas e insere dados de exemplo (perfis, permissões, utilizadores,
formadores, etc.).

> As passwords dos utilizadores de exemplo estão em `bcrypt`. O utilizador `admin` (perfil
> Administrador) e outros existem no `INSERT` da tabela `Usuario`.

---

## 12. Permissões (RBAC)

RBAC = *Role-Based Access Control* (controlo de acesso por papéis). Aqui há **duas camadas**:

1. **Base de dados (novo/flexível):** `PerfilPermissaoDao::getMatrizByPerfil($idPerfil)` faz um `JOIN`
   de `perfil_permissao` → `recurso` → `permissao` e devolve
   `['formadores' => ['criar'=>true, ...], ...]`.
2. **Matriz legada (fallback):** se não houver nada na BD, `Sessao` usa `$matrizLegada`
   (código duro). Isto evita "ninguém consegue nada" em bases antigas.

### Perfis de sistema (IDs fixos)

| ID | Nome | Acesso típico |
|---|---|---|
| 1 | Operador | Recursos académicos: **consultar + criar**. |
| 2 | SuperOperador | Recursos académicos: **CRUD**. |
| 3 | Administrador | Académicos CRUD + utilizadores + perfis (sem logs). |
| 4 | Auditor | Académicos CRUD + logs (só consultar). |

O `PerfilController` considera `PERFIS_SISTEMA = [1,2,3,4]` — estes perfis têm regras especiais
(não podem ser apagados livremente, etc.).

### Como o fluxo de permissão funciona, passo a passo

```
1. Login → Sessao::login($u)
2. → carregarPermissoes(idPerfil, nomePerfil)
3. → PerfilPermissaoDao::getMatrizByPerfil(idPerfil)
4. → $_SESSION['permissoes'] = matriz
5. Numa página: Sessao::exigirLeitura('formadores', ...)
6. → Sessao::pode('R','formadores') → true/false → deixa passar ou redireciona
7. Na sidebar/botões: Sessao::podeCriar('formadores') etc.
```

### Os "4 verbos" e a sua tradução

| Símbolo no código | Apelido | Significa |
|---|---|---|
| `ACAO_CRIAR` = `'C'` | `criar` | Inserir |
| `ACAO_LEITURA` = `'R'` | `consultar` | Ler/listar |
| `ACAO_EDITAR` = `'U'` | `editar` | Atualizar |
| `ACAO_REMOVER` = `'D'` | `eliminar` | Apagar |

O `Sessao::normalizarAcao()` converte `'C'`→`'criar'` etc., porque na matriz os nomes estão por
extenso.

---

## 13. Logs / auditoria

- **Tabela:** `Log(codigo, id_Usuario, acao, descricao, data)`.
- **Model:** `Logs` (codigo, acao, descricao, usuario, data).
- **DAO:** `LogDao::salvar(Logs $log)` — grava; e métodos de listagem com filtro.
- **Ações registadas:** `LOGIN`, `LOGOUT`, `INSERT`, `UPDATE`, `DELETE`.
- **Quem grava:** quase todos os DAOs têm um método privado `registarLog()` que chama
  `Sessao::obterUtilizador()` para saber o autor. Se não houver sessão (ex: durante o login),
  não se registra log de utilizador (exceto o próprio `registarLogin`).
- **Ver:** `view/log/index.php` (recurso `logs`, normalmente só Auditor/Administrador).

Exemplo de descrição: `"Formador Malik mangue foi cadastrado"` ou
`"Senha do utilizador OMini (ID: 69) foi resetada"`.

> ⚠️ **Nota de segurança (importante!):** `controller/FicheiroDefault.php` escreve a password em
> **texto simples** num ficheiro `senhasDefault/<Nome>.txt`. Como não é chamado por ninguém, é
> "código morto" — mas se algum dia o ligares, apaga-o ou protege-o bem. Nunca guardes passwords
> em ficheiros de texto.

---

## 14. Fluxos ponta a ponta

### Fluxo A — Login

```
Utilizador abre view/login/index.php
  └─ Sessao::iniciar()
  └─ já logado? → redireciona para dashboard
  └─ é POST?
       ├─ lê username/password
       ├─ UsuarioController::autenticar(username, password)
       │     └─ UsuarioDao::getByUsername()            (vai ao MySQL)
       │     └─ password_verify(password, hash)        (bcrypt)
       │     └─ UsuarioDao::registarLogin()            (grava Log)
       │     └─ devolve objeto Usuario
       ├─ Sessao::login($usuario)
       │     └─ session_regenerate_id(true)            (segurança)
       │     └─ grava $_SESSION[...]
       │     └─ carregarPermissoes() → $_SESSION['permissoes']
       ├─ primeiroAcesso == 1 ? → redefinir.php
       └─ senão → dashboard/index.php
  └─ não é POST → desenha o formulário HTML
```

### Fluxo B — Cadastrar um Formador

```
View view/formadores/index.php  (POST com name="gravar")
  └─ Sessao::exigirAcao('C','formadores','index.php')     (tem permissão?)
  └─ lê $_POST: nome, apelido, email, genero, estadoCivil, contacto, valorHora, horasMes, salario,
                isDiretor (checkbox), isCoordenador (checkbox)   [isFormador é ignorado!]
  └─ valida com o trait Validacao ($controller->texto(...), ...)
  └─ FormadorController::cadastrarFormador(...)
        └─ valida outra vez
        └─ salario = valor_horas * horas_mes
        └─ new Formador(...) → FormadorDao::create() → INSERT + Log
        └─ se isDiretor → Diretor_TurmaController::cadastrarDiretor_Turma()
        └─ senão se isCoordenador → CoordenadorController::cadastrarCoordenador()
  └─ $_SESSION['flash'] = sucesso/erro
  └─ header('Location: index.php'); exit;
GET index.php
  └─ FormadorController::listar() → FormadorDao::getAll()
  └─ para cada linha: getStatusFormador() → mostra badges Diretor/Coordenador
  └─ desenha a tabela + modais
```

> **Exemplo didático de campo "só interface":** o checkbox **"Formador"** no grupo "Função"
> (`name="isFormador"`) é puramente visual — está `checked` por defeito e **não é lido nem gravado**
> pelo controller (o `index.php` só lê `isDiretor` e `isCoordenador`). É útil quando queres um campo
> que aparece no ecrã mas não vai para a base de dados.

### Fluxo C — Cadastrar um Módulo (com entidades relacionadas)

```
View view/modulo/index.php (POST gravar)
  └─ exige ação 'C' em 'modulos'
  └─ lê nome, carga_horaria, semestre, qualificacao, nivel
  └─ ModuloController::cadastrarModulo(...)
        └─ $this->texto + $this->inteiroPositivo + qualificacao/nivel não nulos
        └─ Quali_NivelController::buscarCodigo($qualificacao, $nivel)
        │      ↳ descobre o id_Quali_Nivel (ligação Qualificação+Nível)
        └─ new Modulo(null, nome, carga_horaria) + setQualiNivel(...)
        └─ ModuloDao::create() → INSERT em Modulo
        └─ Quali_moduloController::cadastrarQuali_modulo(semestre, modulo, qualificacao)
               ↳ INSERT em Quali_modulo
  └─ flash + redirect
```

Aqui vês a **orquestração**: um único "guardar módulo" grava em **duas tabelas** e resolve uma
relação. É por isso que existe um Controller dedicado em vez de a View falar diretamente com o DAO.

---

## 15. Guia de depuração e erros

### 15.1 Como ver o erro real

No topo do ficheiro que está a dar problema (temporariamente):

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Ou, melhor, no `php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /tmp/php_errors.log
```

### 15.2 Erros mais comuns e o que significam

| Mensagem / sintoma | Causa provável | O que fazer |
|---|---|---|
| **"Falha na base de dados: ..."** | MySQL desligado, credenciais erradas, base `GestaoProfessor` inexistente. | Confirmar MySQL a correr; ver `config/conexao.php`; importar `database/GestaoProfessorPHP2.sql`. |
| **Página em branco** | Erro PHP fatal com `display_errors` desligado. | Ligar `display_errors`; ver o log (`/tmp/php_errors.log`). |
| **"Call to a member function ... on null"** | Chamaste um método numa variável que é `null` (ex: `getById()` não encontrou nada). | Verificar se a query devolveu linha; validar `$codigo` antes. |
| **"Call to undefined function/method"** | `require_once` em falta, ou nome de método/classe errado (maiúsculas contam!). | Confirmar os `require` no topo; classes em PHP são case-insensitive, mas **métodos não**. |
| **Sou redirecionado para o login sem querer** | `Sessao::exigirLogin()` chamado e `esta_logado()` deu `false` (sessão perdida). | Ver cookie/`session.save_path`; confirmar que fizeste login. |
| **"Não tem permissão para..."** | `exigirAcao`/`exigirLeitura` falhou (sem permissão no perfil). | `view/permissoes/index.php` → dar a permissão; ou limpar e recarregar a sessão (re-login). |
| **Alterei permissões mas nada muda** | As permissões estão em `$_SESSION` e só recarregam no login. | Fazer logout/login; ou usar `Sessao::recarregarPermissoes()`. |
| **"Esse nome de utilizador já existe."** | `UNIQUE username` violado → `mysqli_sql_exception` apanhada. | Escolher outro username. |
| **Erro ao apagar (FK constraint)** | Estás a apagar algo referenciado noutra tabela. | Apaga primeiro os dependentes (lições, matrículas...). Os controllers já bloqueiam alguns casos. |
| **JSON `{"method":"POST",...}` aparece em vez da página** | Estás a passar pelo `.php-preview-router.php` (preview do editor). | Acede via servidor PHP real (`php -S`/Apache), não pelo preview. |
| **Acentos aparecem "trocidos"** | UTF-8 desalinhado. | Garantir `<meta charset="UTF-8">` e `SET NAMES utf8mb4` na ligação. |

### 15.3 Método de depuração recomendado

1. **Lê a mensagem completa**, incluindo o **ficheiro e a linha**.
2. Abre esse ficheiro na linha indicada.
3. Faz **var_dump/die** localizado para "ver o que está dentro" da variável:
   ```php
   var_dump($codigo); die('aqui');
   ```
   Ou imprimir o objeto:
   ```php
   echo '<pre>'; print_r($formador); echo '</pre>'; die();
   ```
4. Verifica o **caminho** de dados: View → Controller → DAO → MySQL. O erro está quase sempre no
   limite entre dois destes.
5. Inspeciona a base de dados diretamente:
   ```bash
   mysql -u root -p
   USE GestaoProfessor;
   SELECT * FROM Formador;
   SELECT * FROM Log ORDER BY codigo DESC LIMIT 20;
   ```
6. Confirma que os `require_once` estão todos no topo do ficheiro que dá erro.

### 15.4 Bug clássico deste projeto: caminhos relativos

As Views usam caminhos relativos (`../../services/Sessao.php`, `../login/index.php`). Isso significa
que **a profundidade da pasta importa**. Se moveres um `index.php` para outro nível, tens de ajustar
**todos** os `require` e `header('Location: ...')`. Erros de "file not found" ou redirecionamentos
partidos vêm quase sempre daqui.

---

## 16. Receitas

### Receita 1 — Adicionar um módulo CRUD novo (ex: "Estágios")

1. **Model** `model/Estagio.php`: classe com atributos + `get/set`.
2. **DAO** `Dao/EstagioDao.php`: copia `NivelDAO.php` e adapta `create/getAll/getById/update/delete` +
   `montarEstagio()` + `registarLog()`.
3. **Controller** `controller/EstagioController.php`: copia `NivelController.php` e adapta.
4. **Recurso de permissão** (se quiseres controlar acesso): adiciona a `Sessao` uma constante
   `RECURSO_ESTAGIOS='estagios'` e insere a linha correspondente em `recurso` na BD.
5. **View** `view/estagio/index.php`: copia `view/nivel/index.php` e adapta os campos. Renomeia os
   `name` dos inputs para os teus campos.
6. **Menu:** adiciona um `<li>` em `view/partials/sidebar.php`, com um ícone em `assets/icons/`.
7. **Tabela SQL:** adiciona o `CREATE TABLE Estagio (...)` em `database/GestaoProfessorPHP2.sql` e
   cria-a na BD.

### Receita 2 — Adicionar um campo a um formulário e à BD

1. `ALTER TABLE X ADD COLUMN novo_campo ...;` (ou edita o `.sql` e recria).
2. No **Model**: adiciona o atributo no construtor + `get/set`.
3. No **DAO**: acrescenta o campo no `INSERT`, no `UPDATE`, no `montarX()` e no `bind_param`
   (confirma a ordem e as letras `s/i/d`!).
4. No **Controller** (se preciso): passa o novo valor.
5. Na **View**: adiciona o `<input>`/`<select>` (e o dummy de valor para o modal de edição).

### Receita 3 — Campo que aparece no ecrã mas NÃO vai para a BD

Basta pôr o input na View sem o ler no Controller. Exemplo real no projeto: o checkbox
`<input type="checkbox" name="isFormador" value="1" checked> Formador`, que é ignorado no `POST`.

### Receita 4 — Dar permissões a um perfil

`view/permissoes/index.php` → escolher perfil → marcar as células (recurso × ação) → Guardar.
Depois **fazer logout/login** para as permissões novas entrarem na sessão.

### Receita 5 — Resetar a password de um utilizador

- Pelo sistema: `view/usuario/index.php` → botão de reset → repõe para a senha padrão **1234** e marca
  `primeiroAcesso=1` (obriga a trocar no próximo login).
- Direto na BD: gerar o hash com `php -r "echo password_hash('1234', PASSWORD_BCRYPT);"` e atualizar
  a coluna `password`.

---

## 17. Convenções e dívidas técnicas

### Convenções a respeitar

- **Nomes:** DAOs terminam em `Dao`; controllers em `Controller`; views são `index.php` dentro da pasta
  do módulo. Alguns nomes históricos têm capitalização inconsistente (`FormadorDao` vs `FormadorDAO`,
  `CampoDAO` vs `CampoDao`) — **mantém o que já existe no ficheiro que editas**.
- **Indentação:** 4 espaços. Chavetas na linha de baixo (estilo que o projeto já usa).
- **Nunca** pôr SQL nas Views. **Nunca** pôr HTML nos DAOs.
- **Sempre** `prepare` + `bind_param` nas queries com valores.
- **Sempre** redirecionar (PRG) após um POST que grava.
- **Sempre** usar `htmlspecialchars()` ao imprimir dados vindos do utilizador (previne XSS).

### Dívidas técnicas (coisas a melhorar, mas que funcionam)

1. **Credenciais hardcoded** em `config/conexao.php`.
2. **`password` em texto simples** no código morto `controller/FicheiroDefault.php`.
3. **`Log.data` e `Matricula.data` como `varchar`** em vez de `DATETIME`/`DATE`.
4. **Assimetria de papéis** na criação de formador (`if/elseif`) vs edição (independente).
5. **Duplicação de formulários** (novo vs editar inline) após remoção dos `_campos.php`.
6. **Validação duplicada** (View + Controller) — aceitável, mas há que manter as duas sincronizadas.
7. **`display_errors` ligado** em alguns ficheiros — desligar em produção.
8. **Sem CSRF token** nos formulários (só há proteção por sessão/permissão).
9. **Caminhos relativos** frágeis a reorganizações de pastas.

---

## 18. Glossário

| Termo | Significado |
|---|---|
| **MVC** | Model-View-Controller: separação de dados, apresentação e controlo. |
| **DAO** | Objeto que encapsula o acesso à base de dados. |
| **Model** | Classe que representa dados (atributos + getters/setters). |
| **Controller** | Classe que contém a lógica e orquestra os DAOs. |
| **View** | Ficheiro que produz o HTML que o utilizador vê. |
| **Prepared statement** | Query SQL parametrizada (`?`), segura contra SQL Injection. |
| **`bind_param`** | Liga valores aos `?`; 1ª letra por tipo: `s`tring, `i`nt, `d`ouble. |
| **`require_once`** | Inclui um ficheiro PHP **uma só vez**. Mecanismo de "imports" do projeto. |
| **Sessão** | Memória no servidor (`$_SESSION`) que liga vários pedidos ao mesmo utilizador. |
| **Flash message** | Mensagem guardada na sessão e mostrada **uma vez**. |
| **PRG** | Post/Redirect/Get: redirecionar após gravar para evitar reenvio com F5. |
| **RBAC** | Controlo de acesso por papéis/perfis. |
| **Hash (bcrypt)** | Transformação irreversível da password (`password_hash`/`password_verify`). |
| **ISA** | Relação "é um" (Diretor_Turma *é um* Formador). |
| **N:M** | Relação muitos-para-muitos (ex: Qualificação↔Nível via `Quali_Nivel`). |
| **Fallback** | Plano B quando o caminho principal não tem dados. |
| **Código morto** | Código que já não é usado por ninguém (`FicheiroDefault`). |

### Comandos úteis

```bash
# verificar sintaxe de um ficheiro PHP
php -l caminho/para/ficheiro.php

# servidor de desenvolvimento
php -S localhost:8000

# procurar onde algo é usado
grep -rn "FormadorDao" --include=*.php .

# entrar no MySQL
mysql -u root -p GestaoProfessor

# gerar um hash de password
php -r "echo password_hash('1234', PASSWORD_BCRYPT), PHP_EOL;"
```

---

## Resumo de uma frase

> Cada ecrã é um `view/<modulo>/index.php` que **protege a página**, **trata o POST**, chama um
> **Controller** que valida e usa um **DAO** para gravar/ler no MySQL (guardando **Log**), e no fim
> desenha a **tabela + modais** usando os **partials** partilhados — tudo protegido por **`Sessao`** e
> pelas **permissões** do perfil.

Se percebeste este parágrafo, percebeste o sistema inteiro. O resto é repetição do mesmo padrão,
módulo a módulo.