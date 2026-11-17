# 🌙 Moonspace

Rede social simples e noturna, feita para conversar. O Moonspace reúne o essencial de uma rede social (perfis, feed, mensagens, grupos, fóruns, filmes e jogos) e tem um ritual próprio, a **Pergunta da Noite**, que dá motivo para voltar todo dia.

- **Site:** https://moonspace.rf.gd
- **Autor:** Jair Roger
- **Versão do núcleo:** 3.4 (`SYS_VER` em `bootstrap.php`)

---

## Sumário

1. [Funcionalidades](#funcionalidades)
2. [Pergunta da Noite](#-pergunta-da-noite)
3. [Tecnologias](#tecnologias)
4. [Estrutura de pastas](#estrutura-de-pastas)
5. [Instalação e publicação](#instalação-e-publicação)
6. [Configuração no painel admin](#configuração-no-painel-admin)
7. [Como o código funciona](#como-o-código-funciona)
8. [O que foi removido na limpeza](#o-que-foi-removido-na-limpeza)
9. [Segurança](#segurança)
10. [Ideias para o futuro](#ideias-para-o-futuro)

---

## Funcionalidades

As funcionalidades são ligadas e desligadas em **Admin › Settings**. Quase todos os ajustes ficam no banco de dados, e não no código.

| Área | O que tem | Arquivos principais |
|---|---|---|
| **Conta** | Cadastro, login, ativação por e-mail, recuperação de senha, autenticação em duas etapas, primeiros passos | `sign.php`, `activation.php`, `started.php`, `includes/ajax/core/` |
| **Feed** | Posts com texto, fotos, vídeos, enquetes, artigos, reações, comentários, compartilhamentos, posts salvos, memórias | `index.php`, `post.php`, `photo.php`, `includes/ajax/posts/` |
| **Stories** | Stories de 24h | `includes/ajax/posts/story.php` |
| **Perfis e pessoas** | Perfil, capa, amigos, seguidores, bloqueio, sugestões, busca | `profile.php`, `people.php`, `search.php` |
| **Mensagens** | Mensagens privadas e chat lateral | `messages.php`, `includes/ajax/chat/`, `includes/ajax/messages/` |
| **Comunidades** | Páginas, grupos e eventos | `page(s).php`, `group(s).php`, `event(s).php` |
| **Fóruns e blogs** | Fóruns com tópicos e respostas, blog com artigos | `forums.php`, `blogs.php` |
| **Filmes e jogos** | Catálogo de filmes e jogos em HTML5 | `movies.php`, `games.php` |
| **Pergunta da Noite** | Ritual diário da comunidade ([detalhes abaixo](#-pergunta-da-noite)) | `night.php`, `includes/class-night.php` |
| **Carteira e Pro** | Saldo interno, transferências, pacotes Pro (pagamento por carteira, transferência bancária ou CoinPayments) | `wallet.php`, `packages.php`, `includes/ajax/payments/` |
| **Notificações** | Notificações no site e push via OneSignal (opcional) | `notifications.php`, `OneSignalSDK*.js` |
| **Moderação** | Painel admin, painel de moderador, denúncias, banimentos, lista negra | `admin.php`, `moderator.php` |
| **Idiomas** | 13 idiomas: inglês (padrão) e mais 12 em `content/languages/locale/` | arquivos `.po` |

---

## 🌙 Pergunta da Noite

O recurso que dá identidade ao Moonspace.

### Como funciona para quem usa

1. **Toda noite às 20h (horário de Brasília)** aparece uma pergunta nova, a mesma para todo mundo. Antes das 20h ainda vale a pergunta da noite anterior.
2. Cada pessoa **responde uma vez por noite**, com até 500 caracteres.
3. **As respostas dos outros só aparecem depois que você responde.** Antes disso, você vê apenas quantas pessoas já responderam.
4. Na aba **Minhas noites** fica o arquivo pessoal: todas as suas respostas, com a pergunta de cada noite.

Endereços:

| Endereço | Conteúdo |
|---|---|
| `/noite` | Pergunta desta noite, formulário e respostas |
| `/noite/minhas` | Arquivo pessoal ("Minhas noites") |
| `/noite/perguntas` | Banco de perguntas (somente admin) |

O link **Pergunta da Noite** aparece no menu lateral, logo abaixo do News Feed, para quem está logado.

### Para admins e moderadores

- **Admin** cadastra e apaga perguntas na aba **Perguntas**. Uma pergunta que já foi usada não pode ser apagada, para não criar buracos no arquivo das pessoas.
- **Admin e moderador** veem as respostas sem precisar responder (com o aviso "visível para você como moderador") e podem apagar respostas.
- Usuários banidos não aparecem. Quem bloqueou alguém, ou foi bloqueado, não vê as respostas dessa pessoa.

### Como a pergunta é escolhida

A hospedagem gratuita não roda tarefas agendadas (cron), por isso a escolha é feita **na primeira visita de cada noite**. O site sorteia uma pergunta **entre as menos usadas** e grava a escolha na tabela `night_days`. Quando todas as perguntas já foram usadas, o ciclo recomeça. Um `INSERT IGNORE` garante que duas visitas ao mesmo tempo não gerem duas perguntas.

### Banco de dados

O script [`sql/pergunta_da_noite.sql`](sql/pergunta_da_noite.sql) cria três tabelas e cadastra 30 perguntas iniciais:

| Tabela | Para que serve |
|---|---|
| `night_questions` | Banco de perguntas |
| `night_days` | Qual pergunta foi usada em cada noite (`night_date` é a chave) |
| `night_answers` | Respostas: uma por pessoa por noite (chave única `night_date + user_id`) |

### Arquivos

| Arquivo | Papel |
|---|---|
| `night.php` | Página (abas *Esta noite*, *Minhas noites*, *Perguntas*) |
| `includes/class-night.php` | Toda a regra: noite atual, sorteio, respostas, arquivo, administração |
| `includes/ajax/night/night.php` | Ações: `answer`, `add_question`, `delete_question`, `delete_answer` |
| `content/themes/default/templates/night.tpl` | Visual da página |
| `content/themes/default/templates/_sidebar.tpl` | Link no menu |
| `.htaccess` | Rotas `/noite` e `/noite/<aba>` |

### Ajustes rápidos

As constantes ficam no início de `includes/class-night.php`:

```php
const START_HOUR = 20;                     // hora em que a noite começa
const TIMEZONE = 'America/Sao_Paulo';      // fuso usado para a virada
const MAX_ANSWER_LENGTH = 500;             // tamanho máximo da resposta
const MAX_QUESTION_LENGTH = 255;           // tamanho máximo da pergunta
const ANSWERS_LIMIT = 200;                 // respostas exibidas por noite
const ARCHIVE_LIMIT = 365;                 // noites exibidas em "Minhas noites"
```

> Os textos da Pergunta da Noite estão direto em português e não passam pelo sistema de tradução.

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Servidor | PHP com a extensão **MySQLi**, Apache com `mod_rewrite` (`.htaccess`) |
| Banco | MySQL / MariaDB, `utf8mb4`, InnoDB |
| Templates | **Smarty 3.1.40** |
| Interface | Bootstrap 4, jQuery e jQuery UI, Font Awesome 5, Mustache, Moment.js, TinyMCE, Zuck.js (stories) |
| Traduções | Gettext (`.po`) via `includes/libs/GettextTranslator` |
| E-mail | PHPMailer |
| Outras bibliotecas | HTMLPurifier (limpeza de HTML), SimpleImage (imagens), Embed (prévia de links), MobileDetect, GoogleAuthenticator (2FA), ReCaptcha, Faker (gerador de dados de teste no admin), FTP/Spaces (armazenamento externo opcional) |
| Push | OneSignal (opcional) |
| Hospedagem atual | InfinityFree (`sql108.infinityfree.com`) |

---

## Estrutura de pastas

```
Moonspace/
├── *.php                     Páginas públicas (index, profile, messages, night, ...)
├── bootstrap.php             Inicialização: config, banco, sistema, idioma, Smarty, usuário
├── bootloader.php            bootstrap + dados de página (notificações, chat, anúncios)
├── .htaccess                 URLs amigáveis, cache e compressão
├── sql/                      Scripts SQL (bloqueada para acesso público)
├── includes/
│   ├── config.example.php    Modelo de configuração (copiar para config.php, que não vai para o git)
│   ├── functions.php         Funções globais (e-mail, SMS, upload, páginas, erros)
│   ├── class-user.php        Quase toda a regra da rede social
│   ├── class-night.php       Pergunta da Noite
│   ├── class-image.php       Tratamento de imagens
│   ├── class-pager.php       Paginação
│   ├── ajax/                 Endpoints AJAX, por área (posts, users, chat, admin, night...)
│   ├── assets/               JS/CSS/sons do núcleo e plugins JS
│   └── libs/                 Bibliotecas PHP de terceiros
└── content/
    ├── themes/default/
    │   ├── templates/        Templates Smarty (.tpl)
    │   ├── templates_compiled/  Cache do Smarty (gerado sozinho; pode ser apagado)
    │   ├── css/  images/
    ├── languages/locale/     Traduções (.po)
    └── uploads/              Arquivos enviados pelos usuários (NÃO apagar)
```

---

## Instalação e publicação

O site já está instalado. Estes passos servem para publicar mudanças ou montar outra cópia.

### 1. `includes/config.php`

O arquivo com as credenciais **não fica no repositório**. Em cada servidor:

1. Copie `includes/config.example.php` para `includes/config.php`.
2. Preencha `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`, `DB_PORT` e `SYS_URL` (sem barra no final).

Se o `config.php` não existir, o site mostra uma mensagem explicando isso.

> O `includes/config.php` está no `.gitignore`. Nunca o adicione ao git.

> O instalador original (`install.php`) foi removido. Ele não é necessário para um site já instalado e era um risco de segurança. Para uma instalação do zero em outro servidor, ele está em `Moonspace_removidos/`.

### 2. Banco de dados

O banco do site já existe. Para recursos novos, rode os scripts da pasta `sql/` no **phpMyAdmin › aba SQL**:

- `sql/pergunta_da_noite.sql`: obrigatório para a Pergunta da Noite (rodar uma vez só).

### 3. Envio dos arquivos (FTP)

- Envie os arquivos para a pasta pública (`htdocs` no InfinityFree).
- A pasta `sql/` **não** precisa ir para o servidor.
- **O FTP não apaga arquivos no servidor.** Quando algo for removido do projeto, apague também lá.
- Não sobrescreva `content/uploads/` no servidor: as fotos dos usuários estão lá.
- Depois de mexer em templates, se a mudança não aparecer, apague o conteúdo de `content/themes/default/templates_compiled/` no servidor.

### 4. Depuração

Para ver erros PHP durante testes, mude `DEBUGGING` para `true` em `config.php`. **Volte para `false` depois.**

---

## Configuração no painel admin

Ajustes que dependem do painel (ficam no banco, não no código):

- **Funções removidas do código.** Mantenha **desligados**: PayPal, Stripe, 2Checkout, Paystack, SMS via Twilio, chamadas de áudio/vídeo, login social (Google/Facebook/etc.), Live, Market, Offers, Jobs, Ads, Funding, Directory e Developers. Se algum for ligado, o link aparece mas leva a um erro 404 ou à mensagem *"This feature is not available"*.
- **Prefixo dos uploads.** Em *Settings › Uploads*, troque o prefixo para `moonspace`. Fotos antigas continuam com `sngine_` no nome do arquivo, porque o banco aponta para esses nomes.
- **Idiomas.** Em *Languages*. Só ative idiomas cuja pasta exista em `content/languages/locale/`.
- **Títulos e textos.** O nome do site, as páginas estáticas e os modelos de e-mail ficam no banco e são editados pelo painel.

---

## Como o código funciona

**Fluxo de uma página** (ex.: `games.php`):

```
games.php
 └─ require 'bootloader.php'          → bootstrap.php: config, banco ($db), $system, idioma, Smarty, $user
 ├─ checa se o recurso está ligado    → if (!$system['games_enabled']) _error(404);
 ├─ page_header('Título')
 ├─ $smarty->assign(...)              → dados para o template
 └─ page_footer('games')              → renderiza content/themes/default/templates/games.tpl
```

**Fluxo de uma ação AJAX:**

- Formulários com `class="js_ajax-forms" data-url="pasta/arquivo.php?do=acao"` são enviados pelo `core.js` para `includes/ajax/pasta/arquivo.php`.
- O endpoint responde com `return_json([...])`:
  - `['callback' => 'window.location.reload();']` executa um JavaScript;
  - `['error' => true, 'message' => '...']` mostra o erro no `.alert-danger` do formulário;
  - `['success' => true, 'message' => '...']` mostra o sucesso no `.alert-success`.

**Convenções importantes:**

- Use sempre `secure($valor, 'int'|'datetime'|...)` antes de colocar dados em SQL. A função também converte HTML em entidades, por isso os templates exibem o texto direto, sem escapar de novo.
- Datas são gravadas em **UTC** (`gmdate('Y-m-d H:i:s')`). Nos templates, `<span class="js_moment" data-time="...">` converte para "há 5 minutos" no fuso de quem vê.
- Para criar uma página nova: arquivo `.php` na raiz, template `.tpl` com o mesmo nome do `page_footer()`, regra no `.htaccess` **antes** do bloco `# Profile` (que captura qualquer `/nome` como perfil) e, se quiser, um link no `_sidebar.tpl`.

---

## O que foi removido na limpeza

Para deixar o projeto mais leve (de 92 MB para cerca de 70 MB), foram retirados recursos sem uso. Tudo foi **movido** para a pasta irmã `Moonspace_removidos/`, na mesma estrutura, e pode ser restaurado.

| Removido | Motivo |
|---|---|
| `install.php`, `changelog.txt`, `functions.php.bak`, `config-example.php`, cache de templates | Sem uso depois da instalação |
| PayPal, Stripe, 2Checkout, Paystack (libs e endpoints) | Pagamentos externos não usados |
| Twilio, Agora, `live.php` | SMS, chamadas e vídeo ao vivo não usados |
| HybridAuth, `connect.php` | Login social não usado |
| Market, Offers, Jobs, Ads, Funding, Directory, Developers, `api.php` | Módulos não usados |
| Arquivos `.mo` de tradução | Desatualizados; o site lê os `.po` |

Os pontos do código que ainda chamavam Twilio, Agora e PayPal (`includes/functions.php` e `includes/class-user.php`) foram protegidos: mostram *"This feature is not available"* em vez de travar o site.

---

## Segurança

- **Credenciais fora do git:** `includes/config.php` está no `.gitignore`, e o repositório guarda apenas o modelo `includes/config.example.php`. Commits antigos ainda contêm credenciais da hospedagem anterior (InfinityFree). Elas não devem ser reaproveitadas no novo provedor.
- Mantenha `DEBUGGING` como `false` em produção.
- Não deixe `install.php` no servidor.
- A pasta `sql/` tem um `.htaccess` que bloqueia o acesso público.

---

## Ideias para o futuro

Ideias **simples de implementar**, que funcionam na hospedagem atual (sem cron e sem tempo real) e reforçam a identidade noturna do Moonspace.

### 1. Sequência de noites 🔥 *(recomendada como próxima)*

Mostrar há quantas noites seguidas a pessoa responde a Pergunta da Noite (ex.: "🌙 12 noites seguidas") ao lado do nome, nas respostas.

- **Por que:** reforça o hábito diário sem criar competição pesada.
- **Como:** um método `get_streak($user_id)` em `class-night.php` que percorre as datas de `night_answers` em ordem decrescente e conta enquanto forem consecutivas. Não precisa de tabela nova.
- **Esforço:** pequeno, cerca de 1 método e 2 linhas no `night.tpl`.

### 2. Noite de Lua Cheia 🌕

Na noite de lua cheia, a Pergunta da Noite ganha um visual especial e usa uma pergunta "de lua cheia", mais profunda ou divertida, separada das comuns.

- **Por que:** cria um evento mensal que combina com o nome da rede.
- **Como:** a fase da lua sai de uma conta, sem API e sem cron. Ciclo lunar = 29,530588853 dias, a partir de uma lua nova de referência (6/jan/2000, 18:14 UTC); a lua cheia fica por volta do 14,77º dia do ciclo. Uma coluna `is_full_moon` em `night_questions` separa as perguntas especiais, e o `night.tpl` mostra um banner quando for lua cheia.
- **Esforço:** pequeno a médio.

### 3. Lembrete no menu 🔔

Um pontinho ao lado de "Pergunta da Noite" no menu quando a pessoa ainda não respondeu a noite atual.

- **Por que:** a maioria das pessoas esquece. O lembrete aumenta a participação sem enviar e-mail nem push.
- **Como:** no `bootloader.php`, para quem está logado, uma consulta simples (`SELECT 1 FROM night_answers WHERE night_date = ? AND user_id = ?`) e um `{if}` no `_sidebar.tpl`.
- **Esforço:** muito pequeno (cerca de 10 linhas). Acrescenta uma consulta leve por página.

### 4. Melhor resposta da noite ⭐

Cada pessoa pode marcar **uma** resposta favorita por noite. No dia seguinte, a mais marcada aparece em destaque como "A resposta de ontem".

- **Por que:** valoriza boas respostas e dá motivo para ler as dos outros.
- **Como:** tabela `night_votes (night_date, user_id, answer_id)` com chave única `(night_date, user_id)`, um botão de estrela por resposta e uma consulta `GROUP BY answer_id` para a noite anterior.
- **Esforço:** médio.

---

<sub>Moonspace © Jair Roger. As bibliotecas de terceiros em `includes/libs/` e `includes/assets/js/plugins/` mantêm as licenças originais.</sub>
