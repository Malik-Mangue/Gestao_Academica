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
| Perfil | `Perfil` | `id`, `nome`(60) |
| Sessão | (memória) | `$_SESSION['user_id'|'username'|'nome'|'idPerfil'|'perfil']` |
| Registo (Log) | `Log` | `codigo`(PK auto), `id_Usuario`(FK→`Usuario.idUser`), `acao`(100), `descricao`(200), `data`(datetime) |

> - `password varchar(60)` = tamanho exato de um hash bcrypt. Nunca se guarda a senha em texto simples.
> - Os perfis existentes são sempre **rastreados a partir da BD** através da classe `model/Perfil.php`
>   (via `Dao/PerfilDao.php`) — nunca com listas fixas no código.

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

### 6. Autorização — Modelo de Perfis (RBAC)
A autorização deriva do perfil do utilizador autenticado (`idPerfil` → nome resolvido
na BD pela classe `Perfil`).

| Perfil | Papel |
| :--- | :--- |
| **Administrador** | Faz **todo o CRUD** em todos os recursos **+ gestão de utilizadores**. **Não tem qualquer acesso a `Log`**. |
| **SuperOperador** | Faz **todo o CRUD** nos recursos académicos. Não gere utilizadores. |
| **Operador** | Apenas **criar e listar** (C + R). Sem editar nem eliminar. |
| **Auditor** | **Único** com acesso a `Log`: faz **CRUD para investigação**. Nos restantes recursos, apenas leitura. |

#### 6.1 Matriz de Permissões
Legenda: `C` criar · `R` listar/consultar · `U` editar · `D` eliminar · `—` sem acesso

| Recurso | Administrador | SuperOperador | Operador | Auditor |
| :--- | :---: | :---: | :---: | :---: |
| Dashboard | R | R | R | R |
| Formadores | CRUD | CRUD | CR | R |
| Formandos | CRUD | CRUD | CR | R |
| Matrículas | CRUD | CRUD | CR | R |
| Inscrições em Módulos | CRUD | CRUD | CR | R |
| Turmas | CRUD | CRUD | CR | R |
| Módulos | CRUD | CRUD | CR | R |
| Qualificações | CRUD | CRUD | CR | R |
| Níveis | CRUD | CRUD | CR | R |
| Campos | CRUD | CRUD | CR | R |
| Salas | CRUD | CRUD | CR | R |
| **Utilizadores (gestão)** | **Criar + Reset senha** | — | — | — |
| **Log (auditoria)** | **—** | **—** | **—** | **CRUD** |

> O acesso a `Log` é **exclusivo do Auditor**: nem o Administrador consulta logs.

#### 6.2 Aplicação das permissões (reutilizando a classe `Sessao`)
1. **Autenticação (rota privada):** `Sessao::exigirLogin('../login/index.php');`
2. **Autorização por perfil (rota restrita):**
   `Sessao::exigirPerfil([Sessao::ADMIN], '../dashboard/index.php');`
   - `view/usuario/index.php` → `[Sessao::ADMIN]`
   - `view/log/index.php` → `[Sessao::AUDITOR]`
3. **Permissão fina por ação:** a view **oculta** os controlos não permitidos
   (ex.: Operador vê só "Novo" e a tabela; Auditor é o único com acesso aos logs)
   e o controller **revalida** o perfil antes de qualquer mutação.

#### 6.3 Auditoria (`Log`)
Cada operação de escrita relevante (C/U/D) poderá registar `id_Usuario`, `acao`,
`descricao` e `data`. *(Modelo-alvo; implementação incremental e exclusiva do Auditor.)*

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
Todas as views em `view/{recurso}/index.php`, presentes e futuras.

### 8. Gestão de Utilizadores (ecrã `view/usuario/index.php`)
- Tabela com o mesmo modelo visual das restantes telas.
- **Uma única ação por linha:** "Reset senha" (sem botões de editar/eliminar).
- Botão "Novo Utilizador" → modal com Nome, Username, Apelido, **Perfil** (select
  alimentado pela classe `Perfil` a partir da BD) e Senha.
- `primeiroAcesso` é definido **no código** (sempre `1` na criação) — não é escolha do utilizador.
- "Reset senha" repõe a senha padrão e força `primeiroAcesso = 1`.
- Rota **exclusiva do Administrador**.

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
| Modelo | `model/Usuario.php`, `model/Perfil.php` |
| Persistência | `Dao/UsuarioDao.php`, `Dao/PerfilDao.php` |
| Controlo | `controller/UsuarioController.php` |
| Apresentação | `view/login/{index,redefinir,logout}.php`, `view/usuario/index.php` |
| Estilo | `assets/css/screens/login.css` |

### 11. Cláusula de Conformidade
Esta especificação subordina-se a `ESPECIFICACAO_MODELO.md` e `IDENTIDADE_VISUAL.md`:
PHP 8 puro orientado a objetos, MySQLi com prepared statements, sem frameworks/CDN,
sem rotas virtuais, sem CSS inline e sem `!important`.

