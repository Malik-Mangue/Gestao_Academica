# Proposta de Melhoria

## Análise comparativa entre o Documento de Análise de Sistemas (especificação) e a implementação atual

**Projeto:** Gestão Académica (Sistema de Gestão de Turmas)
**Tecnologias atuais:** HTML5, CSS3, PHP (sem framework) e MySQL/MySQLi
**Base de análise:** Documento *"Sistema de Gestão de Turmas — Documento de Análise de Sistemas v1.0"* confrontado com o código-fonte em `controller/`, `Dao/`, `model/`, `services/`, `view/` e com o esquema em `database/`.
**Versão deste documento:** 1.0
**Data:** 2026-10-16

---

## 1. Objetivo

Este documento tem dois propósitos:

1. **Verificar** se as funcionalidades descritas na especificação já estão presentes na aplicação atual, identificando o que está **implementado**, o que está **parcial** e o que está **ausente**.
2. **Propor melhorias** concretas e priorizadas para fechar as lacunas encontradas, tendo em conta o esforço, o risco e a coerência com a arquitetura existente.

O documento está organizado por requisitos (funcionais, não funcionais e regras de negócio) para permitir a rastreabilidade direta com a especificação.

---

## 2. Metodologia

A análise foi feita por leitura direta do código, verificando:

- Os controladores (`controller/*.php`) e os métodos públicos disponibilizados.
- A camada de acesso a dados (`Dao/*.php`) e as consultas SQL efetivamente executadas.
- O modelo de dados real (`database/*.sql`) e as migrações existentes.
- As vistas (`view/**`) para confirmar o que é exposto ao utilizador.
- O serviço de sessão/permissões (`services/Sessao.php`) para autenticação e autorização.

### Legenda de classificação

| Símbolo | Significado |
|---|---|
| ✅ | **Implementado** — a funcionalidade existe e cumpre o essencial da especificação. |
| 🟡 | **Parcial** — existe uma base funcional, mas faltam campos, validações ou operações previstas. |
| ❌ | **Ausente** — não existe suporte na aplicação atual. |

> Observação importante: existe um **desalinhamento de vocabulário** entre a especificação e o sistema real (detalhado na secção 3.3). Por isso, a análise mapeia termos equivalentes antes de classificar cada requisito.

---

## 3. Panorama do sistema atual

### 3.1. Arquitetura

O sistema segue uma separação em camadas próxima do padrão MVC, sem framework:

- **Apresentação:** `view/**` (HTML + PHP embutido) e `assets/css/**`.
- **Aplicação:** `controller/*.php` (validação de entrada e coordenação).
- **Domínio:** `model/*.php` (entidades).
- **Persistência:** `Dao/*.php` (SQL com *prepared statements* MySQLi).
- **Transversal:** `services/Sessao.php` (autenticação, autorização RBAC) e `services/Validador.php`.

Ponto positivo: a separação de responsabilidades exigida no **RNF05** está, em larga medida, respeitada.

### 3.2. Módulos implementados

Existem vistas e controladores para: Dashboard, Autenticação (login/logout/redefinição), Utilizadores, Perfis, Permissões, Formadores, Formandos, Campos, Qualificações, Níveis, Módulos, Salas, Turmas, Matrículas, Inscrições (em módulos), Lições/Horários e Logs.

### 3.3. Modelo de dados atual e equivalências

O sistema atual usa nomes e conceitos diferentes dos da especificação. O quadro abaixo traduz os termos:

| Termo da especificação | Equivalente no sistema atual | Observações |
|---|---|---|
| Curso | **Qualificação** (`Qualificacao`) + **Nível** (`Nivel`) | O par curso/nível é materializado em `Quali_Nivel`. |
| Área/campo de formação | **Campo** (`Campo`) + `Classificacao` | `Classificacao` liga Campo ↔ Qualificação. |
| Ano letivo | **Ausente** — apenas `Turma.ano_lectivo` (inteiro) | Não há entidade nem datas/estado. |
| Turma | **Turma** (`Turma`) | Tem `nome`, `ano_lectivo`, `turno`, diretor, `id_Quali_Nivel`. |
| Formando | **Formando** (`Formando`) | `nome`, `apelido`, `contacto`, `email`, `BI`. |
| Inscrição de formando em turma | **Ausente** | Existem `Matricula` (formando → qualificação/nível) e `Inscricao` (formando → módulo). |
| Transferência | **Ausente** | — |
| Formador | **Formador** (`Formador`) | Inclui cálculo de salário; flag diretor/coordenador noutras tabelas. |
| Módulo | **Modulo** (`Modulo`) | `nome_modulo`, `carga_horaria`, `id_Quali_Nivel`. |
| Atribuição de formador a módulo | **Parcial** via `Licao` | Não existe tabela de atribuição dedicada. |
| Sala | **Sala** (`Sala`) | `designacao`, `tipo_sala`. |
| Aula / horário | **Licao** (`Licao`) | `data`, `hora_inicio`, `hora_fim` + turma/módulo/formador/sala. |
| Presença | **Ausente** | — |
| Avaliação | **Ausente** | `Classificacao` não é avaliação de alunos. |
| Utilizador / Perfil | **Usuario**, **Perfil** + `recurso`/`permissao`/`perfil_permissao` | RBAC granular já implementado. |
| Histórico de operações | **Log** (`Log`) + `LogDao` | Registo simples de INSERT/UPDATE/DELETE. |

---

## 4. Análise dos Requisitos Funcionais (RF)

| Requisito | Estado | Evidência / o que falta |
|---|:---:|---|
| **RF01 — Autenticação** | ✅ | `view/login/*`, `UsuarioController::autenticar`, `password_hash`/`password_verify`, `session_regenerate_id`, sessão 2h HttpOnly/SameSite. Falta proteção CSRF. |
| **RF02 — Gestão de utilizadores** | 🟡 | CRUD em `view/usuario/index.php`, perfis e *reset* de senha (`UsuarioController::resetSenha`). Falta **ativar/desativar conta** (não existe campo `estado`) e recuperação por e-mail. |
| **RF03 — Gestão de cursos** | 🟡 | CRUD de Qualificação/Nível/Campo (`QualificacaoController`, `NivelController`, `CampoController`). Dependências verificadas antes de eliminar. Faltam **duração, estado, data de criação** e ativar/desativar. |
| **RF04 — Gestão de anos letivos** | ❌ | Não existe entidade. Só `Turma.ano_lectivo` (inteiro), sem datas, estado nem "ano em vigor". Histórico de anos anteriores não é tratável como período. |
| **RF05 — Gestão de turmas** | 🟡 | CRUD, pesquisa e diretor de turma (`TurmaController`). Faltam **capacidade máxima, estado (Planeada/Ativa/…), datas de início/fim, observações**, contagem de inscritos/vagas e página de detalhes (formandos, módulos, formadores, horário). |
| **RF06 — Gestão de formandos** | 🟡 | CRUD e pesquisa (`FormandoController`). Faltam **número de estudante**, **estado** do formando e histórico de turmas. |
| **RF07 — Inscrição de formandos em turmas** | ❌ | Não existe vínculo formando → turma. `Matricula` liga a qualificação/nível; `Inscricao` liga a módulo. Não há verificação de **capacidade**, **estado da turma** nem regra de inscrição ativa duplicada na turma. |
| **RF08 — Transferência de formandos** | ❌ | Não implementado. Não há conceito de transferência nem transação associada. |
| **RF09 — Gestão de formadores** | 🟡 | CRUD, pesquisa e cálculo de salário (`FormadorController`). Falta **estado ativo/inativo** e vista de turmas/módulos atribuídos. |
| **RF10 — Gestão de módulos** | 🟡 | CRUD e carga horária (`ModuloController`). Falta **associação explícita módulo ↔ turma** (`ModuloTurma`) e consulta dos formadores responsáveis. |
| **RF11 — Atribuição de formadores a módulos** | 🟡 | O formador é definido por `Licao` (por aula). Não há tabela de atribuição (turma+módulo+formador+datas) nem histórico de substituições. |
| **RF12 — Gestão de salas** | 🟡 | CRUD (`SalaController`). Faltam **capacidade, localização e estado**; não há vista de disponibilidade/ocupação. |
| **RF13 — Gestão de horários** | 🟡 | `Licao` com módulo/formador/sala/turma/data/horas e validação `hora_fim > hora_inicio`. **Deteção de conflitos está incorreta** (ver secção 8) e não há vista de horário semanal. |
| **RF14 — Registo de presenças** | ❌ | Não existe tabela nem vista. (A especificação admite ser opcional.) |
| **RF15 — Gestão de avaliações** | ❌ | Não existe. (`Classificacao` refere-se a campo/qualificação, não a notas.) |
| **RF16 — Pesquisa, filtros e paginação** | 🟡 | Pesquisa textual em todos os módulos (via `GET pesquisa`). Faltam **filtros** (curso/ano/estado), **ordenação** e **paginação** (sem `LIMIT/OFFSET`). |
| **RF17 — Emissão de relatórios** | ❌ | Não há relatórios, impressão (`window.print`) nem exportação (PDF/CSV). |
| **RF18 — Histórico de operações** | 🟡 | `Log` + `LogDao` + `view/log/index.php` com filtro; registos em vários DAOs. Falta cobertura total, campos estruturados (entidade/ID) e proteção contra alteração. |

**Resumo RF:** 1 implementado · 10 parciais · 7 ausentes.

---

## 5. Análise dos Requisitos Não Funcionais (RNF)

| Requisito | Estado | Observações |
|---|:---:|---|
| **RNF01 — Segurança** | 🟡 | ✅ *Prepared statements*, *hash* de senhas, RBAC verificado no servidor, `session_regenerate_id`, cookies HttpOnly/SameSite. ❌ Falta **CSRF**, verificação de **conta ativa**, HTTPS obrigatório. Mensagens de erro de BD usam `die(...getMessage())` — podem **expor detalhes internos**. |
| **RNF02 — Desempenho** | 🟡 | Uso correto de consultas preparadas. ❌ Ausência de **paginação** e de índices para campos muito pesquisados. |
| **RNF03 — Usabilidade** | ✅ | Interface consistente, mensagens `flash`, confirmação de remoção via modal, estados visuais. Ponto a melhorar: mensagens de erro mais orientadoras. |
| **RNF04 — Compatibilidade** | 🟡 | Compatível com navegadores atuais. ❌ **Sem responsividade**: não existem *media queries* no CSS. |
| **RNF05 — Manutenibilidade** | ✅ | Camadas bem separadas (controller/dao/model/service/view). |
| **RNF06 — Integridade dos dados** | 🟡 | ✅ Chaves primárias, estrangeiras e *unique* (`username`, `contacto`, e a matriz de permissões). ✅ Transações usadas em `PerfilPermissaoDao` e `QualificacaoDao`. ❌ Sem *unique* para inscrição ativa nem transações nas operações académicas (por não existirem ainda). |
| **RNF07 — Disponibilidade e recuperação** | ❌ | Não há política de *backups*, recuperação ou registo de erros em ficheiro. |
| **RNF08 — Privacidade** | 🟡 | Recolha limitada de dados, mas sem controlo de retenção nem minimização formal. |

---

## 6. Análise das Regras de Negócio (RN)

| Regra | Estado | Observação |
|---|:---:|---|
| RN01 Código único de turma | ❌ | `Turma.nome` não tem restrição de unicidade nem validação. |
| RN02 Curso obrigatório | ✅ | `Turma.id_Quali_Nivel` é FK obrigatória. |
| RN03 Ano letivo obrigatório | 🟡 | Existe o campo, mas não há entidade ano letivo validada. |
| RN04 Capacidade válida | ❌ | Não existe campo de capacidade. |
| RN05 Limite de inscrições | ❌ | Sem capacidade nem inscrição em turma. |
| RN06 Inscrição ativa duplicada | ❌ | Regra aplicável à inscrição em turma, que não existe. |
| RN07 Transferência histórica | ❌ | Sem transferências. |
| RN08 Turma encerrada | ❌ | Não existe estado de turma. |
| RN09 Formador válido/ativo | 🟡 | FK garante existência, mas não há estado ativo. |
| RN10 Módulo válido | ✅ | `Modulo.id_Quali_Nivel` é FK. |
| RN11 Sala disponível | 🟡 | Existe verificação, mas **com defeito** (ver secção 8). |
| RN12 Formador disponível | 🟡 | Idem — dependente da consulta de conflitos defeituosa. |
| RN13 Turma disponível (sem sobreposição) | ❌ | A verificação de conflitos **não considera a turma**. |
| RN14 Horário válido (fim > início) | ✅ | Validado no controlador e na vista. |
| RN15 Preservação histórica | 🟡 | Algumas dependências impedem eliminação; não é sistemático. |
| RN16 Autorização | ✅ | Verificada no servidor por `Sessao::exigir*`. |
| RN17 Auditoria | 🟡 | Registo em `Log`, mas cobertura incompleta. |
| RN18 Consistência das transferências | ❌ | Sem transferências nem transação. |
| RN19 Arquivo de turma | ❌ | Sem estado/arquivo. |
| RN20 Validação no servidor | ✅ | Validação ocorre nos controladores. |

---

## 7. Atores vs. perfis implementados

| Ator da especificação | Estado | Considerações |
|---|:---:|---|
| **Administrador** | ✅ | Perfil `Administrador` com acesso total + utilizadores/perfis. |
| **Coordenador pedagógico** | 🟡 | Existe a entidade `Coordenador` (ligada à qualificação), mas **sem permissões/perfil dedicados**. |
| **Funcionário da secretaria** | 🟡 | Mapeável ao perfil `Operador` (consultar+criar), mas não é um ator formal. |
| **Formador** | ❌ | Não há perfil/login de formador; os perfis atuais são genéricos (Administrador/SuperOperador/Operador/Auditor). |
| **Utilizador de consulta** | 🟡 | Aproximável com perfis de leitura, mas sem perfil dedicado. |

Existe uma diferença conceptual: o RBAC atual é **orientado a recursos** (ex.: `turmas`, `matriculas`), não a **atores de negócio**. Isto é flexível, mas obriga a definir os perfis de cada ator manualmente.

---

## 8. Principais lacunas e inconsistências detetadas

1. **Defeito na deteção de conflitos de horário (crítico).**
   Em `Dao/LicaoDao::existeConflito`, a condição usa `where cod_Sala = ? AND cod_Formador = ? AND ...`. Como usa **E lógico**, só deteta conflito quando **a mesma sala e o mesmo formador** se sobrepõem, deixando passar conflitos de sala isolada, de formador isolado e, sobretudo, **conflitos de turma** (RN11/RN12/RN13). Deve passar a usar verificações separadas (uma por recurso).

2. **Divergência de modelo de dados.**
   A especificação assume `cursos`, `anos_letivos`, `inscricoes_turma`, `aulas`, `presencas`, `avaliacoes`, `historico_operacoes`. O sistema usa `Qualificacao/Nivel`, `Matricula`, `Inscricao`, `Licao` e `Log`. É necessário **alinhar** especificação e implementação (renomear, mapear ou evoluir o esquema).

3. **Ausência total do núcleo académico de turmas.**
   Não existe vínculo formando ↔ turma, capacidade, estado da turma, datas nem transferências. Este é o maior bloco em falta face a "Sistema de Gestão de Turmas".

4. **Sem paginação, filtros e relatórios.**
   Listagens carregam todos os registos; não há filtros por curso/ano/estado, ordenação configurável, impressão ou exportação.

5. **Segurança a completar.**
   Falta proteção CSRF nos formulários que alteram dados, verificação de conta ativa, e as mensagens de erro de BD podem revelar detalhes internos.

6. **Sem responsividade.**
   Não existem *media queries*, apesar de o RNF04 exigir adaptação a tablets/telemóveis.

---

## 9. Proposta de melhoria (priorizada)

As melhorias estão organizadas por fases. A prioridade segue o critério **risco × impacto na missão do sistema**.

### Fase 0 — Correções críticas (curto prazo, baixo esforço)

1. **Corrigir a deteção de conflitos de horário** em `LicaoDao::existeConflito`:
   - Verificar separadamente sobreposição por **sala**, por **formador** e por **turma**.
   - Manter a janela temporal `hora_inicio < :fim AND hora_fim > :inicio` em cada verificação.
2. **Proteger formulários com CSRF:** gerar *token* por sessão (`Sessao`) e validá-lo em todos os POST que alteram dados.
3. **Suavizar mensagens de erro de BD:** registar detalhe em *log* e mostrar mensagem genérica ao utilizador.
4. **Adicionar responsividade básica:** *media queries* em `assets/css/style.css` para *grids*, tabelas e *sidebar*.
5. **Unicidade do nome/código de turma** e validação do ano letivo.

### Fase 1 — Núcleo de turmas (médio prazo)

6. **Estender a entidade Turma** com: `capacidade_maxima`, `estado` (Planeada/Ativa/Suspensa/Encerrada/Arquivada), `data_inicio`, `data_fim`, `observacoes` e `codigo` único.
7. **Criar entidade Ano Letivo** (`anos_letivos`: designação, datas, estado, em vigor) e migrar `Turma.ano_lectivo` para FK.
8. **Página de detalhes da turma** com separadores: dados gerais, formandos, módulos, formadores, horário.
9. **Cálculo dinâmico de inscritos e vagas** (nunca armazenado manualmente).

### Fase 2 — Inscrições e transferências (médio prazo)

10. **Criar `inscricoes_turma`** (formando ↔ turma, data, estado, motivo de saída) com:
    - Restrição de unicidade para **impedir inscrição ativa duplicada** (RN06).
    - Verificação de **capacidade** e **estado da turma** (RN05/RN08).
11. **Implementar transferência** com **transação**: encerrar inscrição atual + criar nova + registar histórico, com `ROLLBACK` em caso de erro (RN07/RN18).
12. **Números de estudante** e **estado** do formando (RN de gestão de formandos).

### Fase 3 — Horários, recursos e módulos (médio prazo)

13. **Associação módulo ↔ turma** (`modulos_turma`) e **atribuição formador ↔ módulo** com datas (histórico de substituições).
14. **Vista de horário semanal** por turma/formador/sala e verificação de ocupação de salas.
15. **Completar Salas** (capacidade, localização, estado) e Formadores (estado ativo/inativo).

### Fase 4 — Relatórios, auditoria e desempenho (médio/longo prazo)

16. **Paginação e filtros** em todas as listagens (servidor, com `LIMIT/OFFSET`).
17. **Relatórios**: lista de turmas, ficha da turma, lista de formandos/formadores, horário semanal, inscrições/transferências, com impressão e exportação CSV/PDF.
18. **Auditoria estruturada**: generalizar o `Log` a todas as operações relevantes, com utilizador, entidade, ID e resumo; tornar o histórico não editável pela aplicação.

### Fase 5 — Módulos opcionais (longo prazo)

19. **Presenças** (`presencas`: aula ↔ formando, estado, observação), com unicidade por aula+formando.
20. **Avaliações** (avaliações, notas, pautas), com validação de escala e registo de alterações.
21. **Política de backups e recuperação** e **registo de erros** em ficheiro protegido.

---

## 10. Roadmap resumido

| Fase | Foco | Requisitos cobertos | Prioridade |
|---|---|---|---|
| 0 | Correções críticas | RN11–RN13, RNF01, RNF04 | **Alta** |
| 1 | Núcleo de turmas | RF04, RF05 | **Alta** |
| 2 | Inscrições e transferências | RF06, RF07, RF08, RN05–RN08, RN18 | **Alta** |
| 3 | Horários, recursos, módulos | RF09–RF13, RN09–RN13 | Média |
| 4 | Relatórios, auditoria, desempenho | RF16, RF17, RF18, RNF02 | Média |
| 5 | Presenças e avaliações | RF14, RF15, RNF07 | Baixa (opcional) |

---

## 11. Conclusão

A aplicação atual constitui uma **base sólida**: já possui RBAC granular, autenticação com *hash* seguro, CRUD dos cadastros básicos, gestão de horários (com validação horária) e registo de auditoria, com boa separação de camadas.

Contudo, face à especificação do **Sistema de Gestão de Turmas**, o **núcleo académico está incompleto**: faltam anos letivos, capacidade/estado de turma, inscrição de formandos em turmas, transferências, relatórios, filtros/paginação e responsividade — além de um **defeito crítico** na deteção de conflitos de horário.

Recomenda-se executar as **Fases 0 a 2** com prioridade, por serem de maior risco e impacto, e só depois avançar para relatórios e módulos opcionais. Paralelamente, é essencial **alinhar a especificação e o esquema de dados**, decidindo se o vocabulário de negócio (cursos/anos letivos/turmas) passa a ser o do documento ou se a especificação é ajustada ao modelo atual (qualificações/níveis/matrículas).
