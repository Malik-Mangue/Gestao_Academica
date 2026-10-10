# ESPECIFICAÇÃO DE AUTENTICAÇÃO, AUTORIZAÇÃO E PERMISSÕES
## Sistema de Gestão Académica

---

### 1. Objetivo
Definir as regras de identidade do sistema: como o utilizador se autentica (login),
como a sessão é mantida e terminada, quem pode aceder a quê (autorização por perfil)
e quais as operações permitidas em cada recurso (permissões).

### 2. Âmbito
Aplica-se a todas as "rotas do sistema principal" (área privada autenticada) e às
rotas públicas de autenticação (login, redefinição de senha e logout).

### 3. Entidades e Modelo de Dados
| Entidade | Tabela | Colunas relevantes |
| :--- | :--- | :--- |
| Utilizador | `Usuario` | `idUser`, `idPerfil` (FK→`Perfil`), `nome`(100), `username`(60, UNIQUE), `apelido`(60), `password`(60), `primeiroAcesso`(tinyint) |
| Perfil | `Perfil` | `id` (PK AUTO_INCREMENT), `nome`(60) |
| Recurso | `recurso` | `id` (PK AUTO_INCREMENT), `nome`(100, UNIQUE), `grupo`(50) |
| Permissão | `permissao` | `id` (PK AUTO_INCREMENT), `nome`(100, UNIQUE) |
| Associação | `perfil_permissao` | `id` (PK), `perfil_id`(FK→`Perfil`), `recurso_id`(FK→`recurso`), `permissao_id`(FK→`permissao`), UNIQUE(`perfil_id`,`recurso_id`,`permissao_id`) |
| Sessão | (memória) | chaves `user_id`, `username`, `nome`, `apelido`, `idPerfil`, `perfil`, `primeiro_acesso`, `permissoes` |
| Registo (Log) | `Log` | `codigo`(PK auto), `id_Usuario`(FK→`Usuario.idUser`), `acao`(100), `descricao`(200), `data`(varchar(19), formato `d/m/Y H:i:s`) |

> - `password varchar(60)` = tamanho exato de um hash bcrypt. Nunca se guarda a senha em texto simples.
> - Os perfis existentes são sempre **rastreados a partir da BD** através da classe `model/Perfil.php`
>   (via `Dao/PerfilDao.php`) — nunca com listas fixas no código.
> - **Autorização dinâmica:** `recurso` (o quê), `permissao` (que ação) e `perfil_permissao`
>   (quem pode) são a fonte de verdade. As FK de `perfil_permissao` têm `ON DELETE CASCADE`.
> - **Extensibilidade:** um recurso novo é apenas um novo `INSERT` em `recurso`; uma
>   permissão nova é um novo `INSERT` em `permissao` (depois usa-se
>   `Sessao::pode('nome', Sessao::RECURSO_X)`), sem alterar o motor de autorização.
> - Os perfis de sistema (ids **1-4**) são semeados em `database/GestaoProfessorPHP2.sql`
>   (bloco "Seed de `perfil_permissao`"), idempotente via `INSERT IGNORE`.

### 4. Autenticação
#### 4.1 Credenciais
- Utilizador: campo `username` (não email).
- Senha: campo `password`.

#### 4.2 Hashing
- Criação/atualização: `password_hash($senha, PASSWORD_BCRYPT)`.
- Verificação: `password_verify($senha, $hashGuardado)`.
- Proibido: guardar/comparar senhas em texto simples, MD5 ou SHA1.

#### 4.3 Fluxo de login
1. `view/login/index.php` (rota pública) recebe `POST`.
2. `UsuarioController::autenticar($username, $password)`:
   - `UsuarioDao::getByUsername($username)` (prepared statement).
   - `password_verify($password, $usuario->getPassword())`.
3. Sucesso → `Sessao::login($usuario)` (grava dados na sessão, `session_regenerate_id(true)`).
   - Se `primeiroAcesso = 1` → redireciona para `redefinir.php` (troca obrigatória).
   - Caso contrário → `view/dashboard/index.php`.
4. Falha → `$_SESSION['flash']` com mensagem **genérica** ("Credenciais inválidas.") e volta ao login.

> **Primeiro acesso é 100% interno:** o valor nunca é escolha do utilizador nem do
> Administrador (fixado no código: `1` na criação e no "Reset senha"). Para além do
> redirect no login, `Sessao::exigirLogin()`/`exigirPerfil()` mantêm **todas as rotas
> privadas bloqueadas** (redirect para `redefinir.php`) enquanto `$_SESSION['primeiro_acesso']`
> for verdadeiro — a flag só é limpa pelo próprio sistema após a troca bem-sucedida.

#### 4.4 Regras de senha
- Mínimo de 6 caracteres; a nova senha deve ser diferente da antiga.
- No primeiro acesso é obrigatória a redefinição.

#### 4.5 Encerramento de sessão
- `view/login/logout.php` → `Sessao::logout()` (`session_unset` + `session_destroy`).

#### 4.6 Anti-enumeração e boas práticas
- Mensagem de erro única (não revela se o username existe).
- `session_regenerate_id(true)` após login (anti fixação de sessão).
- Cookies de sessão com `httponly = true` e `samesite = Lax`.

### 5. Política de Sessão
| Parâmetro | Valor |
| :--- | :--- |
| Duração | **2 horas** (`Sessao::$duracao = 7200s`) — adequada a sistema académico |
| `session.gc_maxlifetime` | igual à duração |
| Cookie `lifetime` | igual à duração |
| `path` | `/` · `httponly` = `true` · `samesite` = `Lax` |
| Renovação do ID | no login (`session_regenerate_id(true)`) |

A classe `services/Sessao.php` é **estática** e é o **único** ponto do sistema que
inicia/termina sessões. Não guarda a password nem executa regras de negócio.

### 6. Autorização — Modelo de Perfis (RBAC por recurso)
A autorização deriva do perfil do utilizador autenticado (`idPerfil` → nome resolvido
na BD pela classe `Perfil`). A granularidade é **por recurso**: cada perfil tem um
conjunto de permissões (`consultar`, `criar`, `editar`, `eliminar`) em cada `recurso`.

A fonte de verdade é a tabela `perfil_permissao`. Perfis **sem qualquer linha** nessa
tabela usam uma **matriz de fallback** definida no código (`Sessao::$matrizLegada`) —
rede de segurança para dados legados. Os perfis novos, criados no ecrã de gestão,
começam **sem permissões** (negam tudo até o Administrador atribuir).

| Perfil | Papel |
| :--- | :--- |
| **Administrador** | CRUD nos **recursos académicos**, nos **utilizadores** e nos **perfis**. **Sem acesso a `Log`**. |
| **SuperOperador** | CRUD nos **recursos académicos**. Não gere utilizadores, perfis nem logs. |
| **Operador** | **Criar e consultar** (`C + R`) nos recursos académicos. Sem editar nem eliminar. |
| **Auditor** | CRUD nos **recursos académicos** e **apenas consultar** em `Log`. **Único** com acesso a `Log`. |

#### 6.1 Matriz de Permissões (por recurso)
Legenda: `C` criar · `R` consultar · `U` editar · `D` eliminar · `—` sem acesso

**Recursos académicos** (grupo `Academico` — inclui dashboard, formadores, formandos,
matrículas, inscrições, turmas, módulos, qualificações, níveis, campos, salas, lições):

| Recurso | Administrador | SuperOperador | Operador | Auditor |
| :--- | :---: | :---: | :---: | :---: |
| Dashboard | CRUD* | CRUD* | CR | CRUD* |
| Formadores · Formandos · Matrículas · Inscrições · Turmas · Módulos · Qualificações · Níveis · Campos · Salas · Lições | CRUD | CRUD | CR | CRUD |

\* O `dashboard` é, na prática, só de leitura (não tem operações de escrita).

**Recursos administrativos** (grupo `Administrativos`):

| Recurso | Administrador | SuperOperador | Operador | Auditor |
| :--- | :---: | :---: | :---: | :---: |
| Utilizadores | CRUD | — | — | — |
| Log (auditoria) | — | — | — | **R** |
| Perfis | CRUD | — | — | — |

> O acesso a `Log` é **exclusivo do Auditor**: nem o Administrador consulta logs.
> A gestão de utilizadores e de perfis é exclusiva do Administrador.
> Os perfis de sistema (Operador, SuperOperador, Administrador, Auditor) **não podem
> ser renomeados nem removidos** — apenas a sua matriz de permissões é editável.

#### 6.2 Aplicação das permissões (classe `Sessao`)
1. **Autenticação (rota privada):** `Sessao::exigirLogin('../dashboard/index.php');`
2. **Guarda de leitura do recurso** (início de cada view de listagem):
   `Sessao::exigirLeitura(Sessao::RECURSO_X, '../dashboard/index.php');`
3. **Guarda de mutação** (processamento de POST — C/U/D):
   `Sessao::exigirAcao(Sessao::ACAO_CRIAR, Sessao::RECURSO_X, '../dashboard/index.php');`
   (idem `ACAO_EDITAR` e `ACAO_REMOVER`).
4. **Decisão fina na apresentação:** `Sessao::pode($acao, $recurso)` e atalhos
   `Sessao::podeCriar()`, `podeLer()`, `podeEditar()`, `podeRemover($recurso)`. A view
   **oculta** os controlos não permitidos e o processamento **revalida** antes de gravar.
5. `$acao` aceita o código curto (`'C'`,`'R'`,`'U'`,`'D'`) ou já o nome da permissão
   (`'criar'`, `'consultar'` ou uma permissão futura como `'exportar'`).

> O motor lê `perfil_permissao` para o `idPerfil` da sessão (`$_SESSION['permissoes']`),
> atualizado no login e por `Sessao::recarregarPermissoes()` após editar o próprio perfil.
> A visibilidade dos menus usa as mesmas funções (`gestaoUtilizadores()`, `auditoriaLogs()`,
> `podeLer(RECURSO_PERFIS)`).

#### 6.3 Auditoria (`Log`)
Cada operação de escrita relevante (C/U/D) poderá registar `id_Usuario`, `acao`,
`descricao` e `data`. *(Modelo-alvo; implementação incremental e exclusiva do Auditor.)*

#### 6.4 Gestão de Perfis e Permissões
- `view/perfil/index.php` — lista de perfis + criação (modal). O botão "+ Novo Perfil"
  só aparece com permissão de `criar` em `perfis`; o POST revalida com `exigirAcao`.
- `view/perfil/visualizar.php` — **"Informações sobre o perfil"**: identidade e permissões
  associadas. *Não há descrição persistida na base de dados* (logo, não é editável).
- `view/permissoes/index.php` — matriz editável `recurso × permissão`, **agrupada por
  `recurso.grupo`**. Ao gravar, `PerfilPermissaoDao::substituir()` troca a matriz de
  forma **transacional**; se for o perfil da própria sessão, chama-se
  `Sessao::recarregarPermissoes()`. **Guard anti-lockout:** não é possível retirar
  `consultar` de `perfis` ao perfil da própria sessão.
- Suportado por `controller/PerfilController.php` + `Dao/RecursoDao.php`,
  `Dao/PermissaoDao.php`, `Dao/PerfilPermissaoDao.php` (perfis de sistema ids 1-4 não
  podem ser renomeados nem removidos).

### 7. Proteção de Rotas
#### 7.1 Regra geral
Toda a rota privada começa obrigatoriamente por:
```php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
```
#### 7.2 Rotas públicas (isentas de guard)
`view/login/index.php` · `view/login/redefinir.php` · `view/login/logout.php`

#### 7.3 Rotas protegidas
Todas as views em `view/{recurso}/index.php`, presentes e futuras — incluindo
`view/perfil/index.php`, `view/perfil/visualizar.php` e `view/permissoes/index.php`,
que se guardam com `exigirLeitura(Sessao::RECURSO_PERFIS, ...)`.

### 8. Gestão de Utilizadores (ecrã `view/usuario/index.php`)
- Tabela com o mesmo modelo visual das restantes telas.
- Acesso condicionado à permissão de `consultar` no recurso `utilizadores`
  (`Sessao::exigirLeitura(Sessao::RECURSO_UTILIZADORES, ...)`); cada mutação revalida
  com `exigirAcao` (C/U/D). Por defeito, exclusivo do Administrador.
- Ações por linha: **Editar**, **Remover** e **Reset senha** (conforme a permissão).
- Botão "Novo Utilizador" → modal com Nome, Username, Apelido, **Perfil** (select
  alimentado pela classe `Perfil` a partir da BD) e Senha.
- `primeiroAcesso` é definido **no código** (sempre `1` na criação) — não é escolha do utilizador.
- "Reset senha" repõe a senha padrão e força `primeiroAcesso = 1`.

### 9. Rastreamento de Erros
Durante o desenvolvimento, os ficheiros-chave declaram no topo:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
Ficheiros-chave: `config/conexao.php`, `services/Sessao.php`, `Dao/PerfilDao.php`,
`Dao/UsuarioDao.php`, `controller/UsuarioController.php`,
`view/login/index.php`, `view/login/redefinir.php`, `view/usuario/index.php`.

### 10. Estrutura de Ficheiros
| Camada | Ficheiro |
| :--- | :--- |
| Serviço | `services/Sessao.php` |
| Modelo | `model/Usuario.php`, `model/Perfil.php`, `model/Recurso.php`, `model/Permissao.php` |
| Persistência | `Dao/UsuarioDao.php`, `Dao/PerfilDao.php`, `Dao/RecursoDao.php`, `Dao/PermissaoDao.php`, `Dao/PerfilPermissaoDao.php` |
| Controlo | `controller/UsuarioController.php`, `controller/PerfilController.php` |
| Apresentação | `view/login/{index,redefinir,logout}.php`, `view/usuario/index.php`, `view/log/index.php`, `view/perfil/{index,visualizar}.php`, `view/permissoes/index.php` |
| Estilo | `assets/css/screens/login.css`, `assets/css/screens/perfil.css` |

### 11. Cláusula de Conformidade
Esta especificação subordina-se a `ESPECIFICACAO_MODELO.md` e `IDENTIDADE_VISUAL.md`:
PHP 8 puro orientado a objetos, MySQLi com prepared statements, sem frameworks/CDN,
sem rotas virtuais, sem CSS inline e sem `!important`.

