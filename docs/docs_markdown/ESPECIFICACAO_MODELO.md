# ESPECIFICAÇÃO DO MODELO DE IMPLEMENTAÇÃO DO PROJETO
## Sistema de Gestão Acadêmica

---

### 1. Visão Geral e Princípio de Justificativa Vinculada
O presente documento estabelece as diretrizes de engenharia e regras de implementação para o Sistema de Gestão Acadêmica.

**Cláusula Mandatória de Engenharia**:
Qualquer decisão de implementação técnica, arquitetural ou de interface tomada no projeto deve ser estritamente justificada e diretamente vinculada aos objetivos do sistema e aos materiais de referência pedagógica disponibilizados (Fichas 8 a 15 e Documento Descritivo do Grupo 1). Nenhuma escolha arbitrária ou recurso não justificado é admitido.

---

### 2. Matriz de Decisões de Implementação e Justificativas Técnicas

| Decisão de Implementação | Justificativa Técnica | Vínculo com o Objetivo do Projeto |
| :--- | :--- | :--- |
| **HTML5 Semântico Puro** | Garantir acessibilidade, padronização W3C e estrutura documental limpa sem a sobrecarga de pré-processadores. | Representar de forma direta e semântica as tabelas de listagem, formulários e modais da instituição de ensino. |
| **CSS3 Puro em Arquitetura Global e Modular** (`assets/css/style.css` e `assets/css/screens/`) | Isolar variáveis mestres (`:root`), componentes universais (Topbar, Sidebar, Modais, Tabelas) no CSS global e permitir personalização atômica não conflitante via folhas de estilo por tela. | Reproduzir fielmente os modelos visuais da pasta `docs/dashboard/` mantendo consistência absoluta e leveza máxima sem frameworks. |
| **Vedação Absoluta ao CSS Inline** | Nenhum atributo `style="..."`, bloco `<style>` ou manipulação de estilo por script é admitido em `view/`. Toda regra reside em arquivo externo: universalidades em `assets/css/style.css` e especificidades em `assets/css/screens/{tela}.css`, carregadas exclusivamente pelos `@import` declarados no topo da folha global. Modificadores que sobrepõem regras globais usam seletor composto (ex: `.modal-header.modal-header-danger`) para vencer a cascata, sendo vedado o uso de `!important`. | Garantir separação estrita entre estrutura HTML e apresentação CSS, permitindo auditoria visual centralizada e reutilização das 11 entidades sem reescrita de regras. |
| **PHP 8 Puro Orientado a Objetos sem Bibliotecas** | Eliminar vulnerabilidades de supply chain, dependências de Composer e complexidade de configuração em servidores locais de avaliação. | Seguir estritamente o modelo de ensino preconizado nas Fichas 10, 12 e 15. |
| **Persistência Exclusiva via MySQLi** | Uso da extensão nativa `mysqli` orientada a objetos com Prepared Statements (`prepare`, `bind_param`, `execute`, `get_result`). | Atender à exigência de banco relacional e manipulação nativa demonstrada nas fichas práticas de PHP sem camadas de abstração externas. |
| **Arquitetura MVC em Pastas Separadas** (`config/`, `model/`, `Dao/`, `controller/`, `view/`) | Desacoplar regras de negócio, persistência de dados e camadas de apresentação. | Conformidade direta com a Ficha 15 ("MVC: Em pastas diferentes"). |
| **Ausência de Rotas Virtuais / Chamadas Diretas** | Eliminar dependência de módulos de reescrita de servidor (`mod_rewrite`, `.htaccess` complexo) que variam entre ambientes de execução. | Permitir execução portátil em qualquer ambiente web com links previsíveis e envio de ações explícitas (`?action=...`). |
| **Sidebar como Eixo Dorsal de Navegação** | Centralizar visualmente o acesso a todos os 11 recursos do sistema em uma barra lateral permanente com feedback ativo (`$active_menu`). | Garantir usabilidade imediata ao usuário sem necessidade de árvore de menus oculta ou rotas dinâmicas. |
| **Uso de Modais Nativos para Pop-ups** (via `:target`) | Prover janelas sobrepostas de confirmação rápida de exclusão e formulários pontuais sem recarregar o contexto da tela. | Alinhar o fluxo de confirmação e revisão da Ficha 8 com a permissão expressa de modais pop-up, sem violar a proibição de bibliotecas JS. |
| **Ícones Vetoriais Locais em `assets/icons/`** | Proibir dependências de CDNs (como FontAwesome) e eliminar o uso informal de emojis. | Padronizar a iconografia institucional com arquivos SVG vetoriais leves, escaláveis e estritamente controlados localmente. |
| **Código Limpo de Comentários e Emojis** | Código-fonte enxuto, autoexplicativo através de tipagem estrita e nomenclatura expressiva. | Garantir conformidade com as regras estritas de entrega profissional do código. |

---

### 3. Mapeamento de Entidades e Dicionário de Dados

| Entidade | Chave Primária | Atributos Principais | Descrição e Relacionamentos |
| :--- | :--- | :--- | :--- |
| **Professor** | `cod_professor` (Int) | `nome_professor`, `apelido_professor`, `genero`, `estado_civil`, `contacto`, `email`, `salario` | Superclasse docente. Calcula salário via horas trabalhadas × valor/hora. Relaciona-se com Lição (1,n). |
| **Diretor de Turma** | `cod_professor` (Int, FK) | Herança de Professor | Especialização (ISA). Cada Diretor de Turma supervisiona exatamente uma Turma (1,1). |
| **Coordenador** | `cod_professor` (Int, FK) | Herança de Professor | Especialização (ISA). Cada Coordenador coordena uma Qualificação (1,1). |
| **Formando** | `cod_formando` (Int) | `nome_formando`, `apelido_formando`, `contacto_formando`, `email_formando` | Estudantes cadastrados. Realiza Matrículas (1,n) e Inscrições em Módulos (1,n). |
| **Módulo** | `cod_modulo` (Int) | `nome_modulo`, `carga_horario` | Unidades curriculares lecionadas. Relaciona-se com Lição, Inscrição e Qualificação. |
| **Turma** | `cod_turma` (Int) | `nome_turma`, `ano_ingresso`, `turno`, `cod_quali`, `cod_professor` | Agrupamento de alunos por turno e qualificação. Supervisionada por Diretor de Turma. |
| **Sala** | `cod_sala` (Int) | `designacao_sala`, `tipo_sala` | Espaços físicos (ex: Teórica, Laboratório, Oficina, Manutenção). |
| **Nível** | `codigo` (Int) | `nome` (VARCHAR 60) | Níveis vocacionais da formação (ex: CV3, CV4, CV5). Referenciado por `Quali_Nivel.cod_Nivel`. |
| **Qualificação** | `cod_quali` (Int) | `titulo` | Cursos centrais da instituição. Relaciona-se com Matrícula, Turma e Níveis. |
| **Campo** | `codigo` (Int) | `nome` (VARCHAR 60) | Áreas de formação / categoria profissional. Categoriza qualificações através de `Classificacao.cod_Campo`. |
| **Lição** | `cod_licao` (Int) | `cod_professor`, `cod_modulo`, `cod_turma`, `cod_sala`, `data`, `hora_inicio`, `hora_fim` | Diário de classe com registro de aulas ministradas. |
| **Matrícula** | `cod_matricula` (Int) | `cod_formando`, `cod_quali`, `data_matricula`, `ano_letivo` | Ingresso oficial do formando na qualificação/curso. |
| **Inscrição** | `cod_inscricao` (Int) | `cod_formando`, `cod_modulo`, `data_inscricao`, `semestre` | Inscrição periódica do formando em módulo curricular. |
| **Quali_Nivel** | Composta (`cod_quali`, `cod_nivel`) | Atributos associativos | Associação N:M entre Qualificações e Níveis de formação. |
| **Modu_Quali** | Composta (`cod_modulo`, `cod_quali`) | Atributos associativos | Associação N:M entre Módulos curriculares e Qualificações. |

---

### 4. Padrão Estrutural de Renderização
Para evitar código quebrado ou duplicações estruturais de tags HTML (`<!DOCTYPE>`, `<html>`, `<head>`, `<body>`), todas as telas obedecem ao contrato de composição:

1. **`view/partials/header.php`**:
   - Inicia sessão se inativa.
   - Computa a raiz relativa do projeto.
   - Emite o `<!DOCTYPE html>`, `<head>`, a folha de estilos única `assets/css/style.css` e abre a tag `<body>`.
   - A folha global é a única requisição de estilo do header e atua como orquestradora, importando no seu topo as folhas de tela residentes em `assets/css/screens/`.
   - Renderiza a barra horizontal superior (Topbar) com logo local, título e perfil do usuário.
2. **`view/partials/sidebar.php`**:
   - Renderiza a barra vertical com avatar, saudação e toda a lista de navegação entre as 11 entidades.
   - Destaca o item correspondente à variável `$active_menu`.
3. **Arquivo da View (`view/{entidade}/index.php`)**:
   - Define `$page_title` e `$active_menu`.
   - Inclui `header.php` e `sidebar.php`.
   - Renderiza o elemento `<main class="main-wrapper">` contendo breadcrumb, cartões, tabelas e janelas modais nativas `:target`.
   - Fecha `</main>` e inclui `footer.php`.
4. **`view/partials/footer.php`**:
   - Renderiza a tag `<footer>` e fecha `</body>` e `</html>`.

---

### 5. Mecanismo de Modal Pop-up Nativo sem Bibliotecas
O modal substitui o redirecionamento de telas para confirmação ou edições simples:

```html
<a href="#modal-confirmar" class="btn btn-delete">Excluir</a>

<div id="modal-confirmar" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Confirmação de Exclusão</h3>
            <a href="#" class="modal-close">
                <img src="../../assets/icons/close.svg" alt="Fechar">
            </a>
        </div>
        <div class="modal-body">
            <p>Confirma a exclusão do registro selecionado?</p>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-delete">Confirmar</button>
        </div>
    </div>
</div>
```

---

### 6. Contrato de Tela de Listagem (CRUD Padrão)

Todas as 11 entidades do sistema implementam exatamente o mesmo esqueleto de gestão. Nenhuma tela inventa estrutura própria: os nomes de ficheiro, métodos, classes CSS, identificadores de modal e campos de formulário abaixo são obrigatórios.

#### 6.1. Quatro Ficheiros por Entidade

| Camada | Caminho | Responsabilidade |
| :--- | :--- | :--- |
| Entidade | `model/{Entidade}.php` | POJO com atributos privados espelhando as colunas da tabela, construtor e par get/set por atributo. Não contém SQL. |
| Persistência | `Dao/{Entidade}Dao.php` | Acesso à base de dados através de `Database::getConnection()`. |
| Controlo | `controller/{Entidade}Controller.php` | Orquestração entre a camada de apresentação e o DAO. |
| Apresentação | `view/{entidade}/index.php` | Ecrã único de gestão, com listagem e modais. A pasta da view é sempre em minúsculas. |

#### 6.2. Métodos Obrigatórios do DAO

`getAll()` · `getById($codigo)` · `create($entidade)` · `update($entidade)` · `delete($codigo)`

- Todos usam `prepare` → `bind_param` → `execute` → `get_result`/`fetch_assoc`.
- `getAll()` inclui sempre `order by codigo`.
- `getById()` devolve `null` quando o registo não existe.
- `create`, `update` e `delete` capturam `mysqli_sql_exception` e devolvem `false`, impedindo erro fatal em caso de violação de chave estrangeira ou estouro de `varchar`.

#### 6.3. Métodos Obrigatórios do Controller

`listar()` · `buscar($codigo)` · `store()` · `update($codigo)` · `delete($codigo)`

Todos devolvem o booleano devolvido pelo DAO.

#### 6.4. Processamento de Ações na View

A própria `view/{entidade}/index.php` trata o `POST` antes de qualquer saída HTML, através de três gatilhos:

| Campo POST | Método invocado | Mensagem de sucesso |
| :--- | :--- | :--- |
| `gravar` | `store()` | Registo cadastrado com sucesso. |
| `editar` | `update($codigo)` | Registo atualizado com sucesso. |
| `deletar` | `delete($codigo)` | Registo removido com sucesso. |

Depois da operação segue-se `header('Location: index.php')` e `exit`. A mensagem é transportada em `$_SESSION['flash'] = ['type' => ..., 'msg' => ...]` e consumida com `unset` durante a renderização.

#### 6.5. Identificadores e Classes Obrigatórias

| Elemento | Valor obrigatório |
| :--- | :--- |
| Modal de criação | `id="modal-novo"` |
| Modal de edição | `id="modal-editar-{codigo}"` |
| Modal de exclusão | `id="modal-deletar-{codigo}"` |
| Cabeçalho do modal de exclusão | `class="modal-header modal-header-danger"` |
| Coluna de código | `class="col-codigo"` |
| Coluna de ações | `class="col-opcoes"` |
| Barra de ações de linha | `class="table-actions table-actions-end"` |
| Estado vazio | `class="table-empty"` no `<td>` com `colspan` total |
| Bloco de confirmação | `class="confirm-question"`, seguido de `class="confirm-detail-box"` e de `class="confirm-warning"` |
| Alerta de feedback | `class="alert alert-success"` ou `class="alert alert-danger"` |

#### 6.6. Campos de Formulário

- Os inputs textuais usam `class="form-control"` e declaram `maxlength` igual ao limite `varchar` real da coluna.
- As labels usam `<span class="required">*</span>` nos campos obrigatórios.
- Rodapé do modal de criação: Cancelar (`.btn-secondary`), Limpar (`.btn-reset` com `type="reset"`) e Salvar (`.btn-success`).
- Rodapé do modal de exclusão: Cancelar (`.btn-secondary`) e Sim, Deletar (`.btn-delete`).
- O botão fechar de qualquer modal usa a imagem `assets/icons/close.svg`.

#### 6.7. Telas Sem CSS Próprio

Enquanto a tela reutilizar exclusivamente os componentes universais, não deve existir folha própria em `assets/css/screens/`. Só se cria `assets/css/screens/{tela}.css` — e se acrescenta o respetivo `@import` no topo de `assets/css/style.css` — quando existir uma regra genuinamente exclusiva daquela tela.
