# GUIA DE IDENTIDADE VISUAL E DESIGN SYSTEM
## Sistema de Gestão Acadêmica

---

### 1. Filosofia Visual e Referências de Interface
A identidade visual deste projeto foi rigorosamente extraída e sintetizada a partir dos artefatos fornecidos na pasta `docs/dashboard/`:
1. **Painel Geral e Estrutura Principal (`WhatsApp Image 2026-09-13 at 02.07.42.jpeg`)**:
   - Barra superior horizontal (Top Header) em azul marinho profundo (`#083e60`) com tipografia clara e identificação de perfil com ícone de sino e usuário.
   - Barra lateral vertical (Sidebar) em tom ardósia escuro (`#253545`) contendo avatar de perfil circular, mensagem "Welcome! admin" e itens de navegação agrupados por contexto funcional.
   - Cards de métricas do Dashboard (Widgets) com cores sólidas e ícones de alto contraste (Ciano, Verde, Vermelho, Amarelo-Dourado).
2. **Organização de Listagens e Tabelas (`WhatsApp Image 2026-09-13 at 13.52.02 (1).jpeg`)**:
   - Tabelas de dados com cabeçalho limpo, listras alternadas suaves (`zebra-striping`), bordas horizontais finas e botões de ação compactos ("Edit" azul e "Delete" vermelho).
   - Painéis com cantos sutilmente arredondados (`border-radius: 4px`), sombras leves e cabeçalhos em caixas brancas bem delimitadas.
3. **Formulários, Abas e Controles de Interface (`WhatsApp Image 2026-09-13 at 13.52.02.jpeg`)**:
   - Navegação por abas horizontais (`nav-tabs`) com aba ativa destacada em azul profundo e inativas em tom neutro.
   - Padrão triplo de botões de formulário: `Cancel` (Cinza), `Reset` (Azul-claro/Secundário), `Submit` (Verde-esmeralda vibrante).
   - Campos de texto com altura de 36px, borda cinza suave, labels com asterisco de obrigatoriedade e foco com anel suave.

---

### 2. Paleta Cromática Institucional (Valores Oficiais Extraídos)

| Nome da Cor | Hexadecimal | RGB | Função Semântica / Aplicação |
| :--- | :--- | :--- | :--- |
| **Navy Topbar** | `#083e60` | `rgb(8, 62, 96)` | Cabeçalho superior horizontal principal e destaques corporativos |
| **Sidebar Background** | `#253545` | `rgb(37, 53, 69)` | Fundo da barra de navegação lateral (Sidebar) |
| **Sidebar Hover/Active** | `#1e2b38` | `rgb(30, 43, 56)` | Item de menu lateral selecionado ou em hover |
| **Sidebar Border Highlight**| `#00c0ef` | `rgb(0, 192, 239)` | Indicador vertical esquerdo do menu ativo (borda verde/ciano) |
| **App Canvas Background** | `#f4f6f9` | `rgb(244, 246, 249)` | Fundo geral de todas as telas e páginas |
| **Panel Surface** | `#ffffff` | `rgb(255, 255, 255)` | Fundo de cartões, painéis, tabelas e modais pop-up |
| **Card Cyan (Info)** | `#00c0ef` | `rgb(0, 192, 239)` | Cards de Formandos, Alunos e Consultas |
| **Card Green (Success)** | `#00a65a` | `rgb(0, 166, 90)` | Cards de Turmas ativas, Lições e botão Submeter/Salvar |
| **Card Red (Danger)** | `#dd4b39` | `rgb(221, 75, 57)` | Cards de Alertas, exclusões e botão Cancelar/Excluir |
| **Card Orange/Gold (Warning)**| `#f39c12` | `rgb(243, 156, 18)` | Cards de Professores, Salários, Valores pendentes |
| **Primary Text** | `#333333` | `rgb(51, 51, 51)` | Títulos de seções, textos corridos, labels |
| **Secondary Text** | `#777777` | `rgb(119, 119, 119)` | Breadcrumbs, legendas, placeholders, rodapés |
| **Table & Card Border** | `#e5e5e5` | `rgb(229, 229, 229)` | Bordas delimitadoras de tabelas, inputs e separadores |

---

### 3. Tipografia e Escala de Fontes
Utilização estrita de tipografia sem serifa de renderização nativa (sem requisições externas a fontes do Google ou CDNs):

```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
```

#### Hierarquia Visual de Textos:
- **Logo Institucional**: 18px a 20px, negrito (`600`), cor `#ffffff`.
- **Cabeçalho de Página (`h1`)**: 22px, peso `600`, cor `#333333`.
- **Subtítulos e Títulos de Painéis (`h2`, `h3`)**: 16px a 18px, peso `600`, cor `#444444`.
- **Métricas dos Cards de Dashboard**: 32px a 36px, peso `700`, cor `#ffffff`.
- **Legendas dos Cards**: 14px, peso `500`, cor `#ffffff` (com sutil transparência).
- **Cabeçalhos de Tabela (`th`)**: 13px, peso `600`, cor `#555555`, texto limpo.
- **Corpo da Tabela e Parágrafos**: 14px, peso `400`, cor `#333333`, line-height `1.5`.
- **Breadcrumb e Metadados**: 12px, peso `400`, cor `#777777`.

---

### 4. O Papel Fundamental da Sidebar na Navegação
A Sidebar é o **eixo dorsal** de toda a experiência do utilizador no sistema. Como o projeto não faz uso de rotas amigáveis, a Sidebar unifica e organiza o acesso direto a todos os 11 recursos e entidades do sistema de gestão acadêmica:

1. **Dashboard Geral** (`view/dashboard/index.php`) — Visão analítica central.
2. **Professores / Formadores** (`view/professor/index.php`) — Gestão de docentes e horas/salários.
3. **Turmas** (`view/turma/index.php`) — Gestão de turmas e atribuição de Diretores de Turma.
4. **Formandos** (`view/formando/index.php`) — Cadastro de estudantes e contatos.
5. **Matrículas** (`view/matricula/index.php`) — Atribuição formal de alunos a qualificações.
6. **Inscrições em Módulos** (`view/inscricao/index.php`) — Inscrições curriculares semestrais.
7. **Módulos Curriculares** (`view/modulo/index.php`) — Disciplinas e cargas horárias.
8. **Qualificações / Cursos** (`view/qualificacao/index.php`) — Cursos e coordenações pedagógicas.
9. **Níveis de Formação** (`view/nivel/index.php`) — Certificações vocacionais (CV3, CV4, etc.).
10. **Salas de Aula** (`view/sala/index.php`) — Gestão de espaços físicos e capacidade.
11. **Campos / Áreas de Estudo** (`view/campo/index.php`) — Categorização e áreas profissionais.
12. **Diário de Lições** (`view/licao/index.php`) — Registro diário de aulas lecionadas.

#### Anatomia da Sidebar:
- **Painel de Perfil**: Avatar circular centralizado, indicador de status online, saudação "Welcome! admin".
- **Divisor de Seção**: Rótulo em caixa alta "GENERAL" ou "GESTÃO ACADÊMICA" em tom acinzentado suave.
- **Item de Navegação**: Ícone estilizado em SVG/caractere alinhado à esquerda, rótulo claro e transição suave ao passar o cursor (`hover`).
- **Item Ativo**: Fundo escurecido `#1e2b38` acompanhado de borda lateral indicativa de 4px na cor `#00c0ef` (Ciano).


---

### 5. Padrão Rígido dos Componentes de Interface

Qualquer interface desenvolvida no sistema deve, obrigatoriamente, obedecer às classes e estilos padronizados abaixo:

#### 5.1. Topbar Superior
- Altura fixa: `50px`.
- Cor de fundo: `var(--navy-top) = #083e60`.
- Conteúdo: Marca do sistema à esquerda, título contextual no centro ("Librarian & Academic Control Panel"), e perfil/notificações à direita.

#### 5.2. Cards de Estatística (Dashboard Widgets)
- Cantos arredondados: `border-radius: 4px`.
- Sombra: `box-shadow: 0 1px 3px rgba(0,0,0,0.12)`.
- Estrutura com número destacado (`.stat-number`), título descritivo (`.stat-label`), ícone temático (`.stat-icon`) e barra inferior (`.stat-footer`).
- Modificadores de cor: `.card-cyan`, `.card-green`, `.card-orange`, `.card-red`.

#### 5.3. Tabelas de Dados (Data Tables)
- Envolvidas em um container com classe `.card` e `.table-responsive`.
- Cabeçalho com borda inferior `#dee2e6` e cor de fundo `#fcfcfc`.
- Linhas com listras alternadas suaves (`tbody tr:nth-of-type(odd)` com `#f9f9f9`).
- Botões de Ação na tabela:
  - Botão Editar: `.btn .btn-edit` (Azul, `#007bff`, cantos de `3px`, padding `4px 8px`, texto `12px`).
  - Botão Excluir: `.btn .btn-delete` (Vermelho, `#dc3545`, cantos de `3px`, padding `4px 8px`, texto `12px`).

#### 5.4. Padrão de Formulários e Botões
- Grupos de formulário com `.form-group`.
- Labels em negrito moderado (`font-weight: 600`), com asterisco vermelho para campos obrigatórios (`.required`).
- Inputs textuais com `.form-control`, borda `#ccc`, altura `36px` e padding de `6px 12px`.
- Barra de Ações com alinhamento à direita:
  - `.btn-secondary` (Cinza claro `#6c757d`) para Cancelar/Voltar.
  - `.btn-reset` (Azul claro `#17a2b8` ou cinza) para Limpar campos.
  - `.btn-success` (Verde `#00a65a`) ou `.btn-primary` (Azul marinho `#083e60`) para Salvar/Submeter.

#### 5.5. Janelas Modais Pop-up Nativas (HTML & CSS Puro)
- Máscara escura: `.modal-overlay` com `background: rgba(0, 0, 0, 0.55)` e transição suave.
- Caixa do Modal: `.modal-box` com fundo branco `#ffffff`, cantos arredondados de `4px`, largura máxima de `500px` a `700px`, e cabeçalho com botão fechar `&times;`.
- Acionamento via seletor `:target`: Não requer JavaScript para abrir ou fechar, garantindo compatibilidade total e carregamento instantâneo.


---

### 6. Cláusula de Conformidade e Proibições Estritas
Para preservar a consistência visual e a integridade da arquitetura em qualquer tela atual ou futura:

1. **Vedação a Frameworks e Libs**:
   - É terminantemente proibido incluir Bootstrap, Tailwind, Bulma, jQuery, FontAwesome ou qualquer CDN externa.
   - Toda e qualquer regra de apresentação deve estar contida em `assets/css/style.css` ou nas folhas de tela `assets/css/screens/{tela}.css`, derivando sempre das variáveis CSS `--navy-top`, `--navy-sidebar`, `--color-cyan`, etc.
2. **Proibição de Roteamento Dinâmico**:
   - É proibido criar sistemas de `router.php`, `.htaccess` de rotas limpas ou bibliotecas de navegação. A navegação entre todos os recursos é realizada exclusivamente através da Sidebar com links diretos e parâmetros explícitos (`?action=...`).
3. **Padrão Obrigatório de Janelas e Diálogos**:
   - É proibido o uso de `alert()`, `confirm()` ou scripts externos de pop-up.
   - Qualquer necessidade de janela flutuante, detalhamento, inserção rápida ou confirmação deve utilizar a estrutura de Modal Pop-up NATIVO com `.modal-overlay:target`.
4. **Fidelidade da Sidebar como Eixo Dorsal**:
   - A barra lateral (`view/partials/sidebar.php`) deve estar presente e acessível em todas as interfaces do sistema.
   - Cada tela deve declarar `$active_menu = '{recurso}'` antes de incluir os partials, garantindo o feedback visual instantâneo do item ativo com a borda de realce lateral ciano `#00c0ef`.
5. **Vedação Absoluta ao Estilo Inline**:
   - É terminantemente proibido o uso de atributos `style="..."`, blocos `<style>` ou manipulação de estilo por script em qualquer arquivo de `view/`.
   - Toda regra de apresentação deve residir em arquivo externo: universalidades em `assets/css/style.css` e especificidades de tela em `assets/css/screens/{tela}.css`.
   - As folhas de tela são carregadas exclusivamente pelos `@import` declarados no topo de `assets/css/style.css`, mantendo o `view/partials/header.php` com uma única requisição de folha de estilos.
   - Como os `@import` são resolvidos antes das regras globais, modificadores que sobrepõem componentes universais devem usar seletor composto (ex: `.modal-header.modal-header-danger`), sendo vedado o recurso a `!important`.
   - Cores, larguras, espaçamentos e sombras devem obrigatoriamente derivar das variáveis de `:root` (`--color-red`, `--border-radius`, `--text-secondary`, etc.), nunca de valores soltos duplicados.

