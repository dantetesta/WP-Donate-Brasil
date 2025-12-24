<p align="center">
  <img src="https://img.shields.io/badge/WordPress-6.0+-blue?style=for-the-badge&logo=wordpress" alt="WordPress">
  <img src="https://img.shields.io/badge/PHP-7.4+-purple?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/License-GPL%20v2-green?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Version-2.0.2-orange?style=for-the-badge" alt="Version">
</p>

<h1 align="center">🙏 WP Donate Brasil</h1>

<p align="center">
  <strong>Sistema completo de doações para sites WordPress brasileiros</strong>
</p>

<p align="center">
  <a href="#-recursos">Recursos</a> •
  <a href="#-instalação">Instalação</a> •
  <a href="#-configuração">Configuração</a> •
  <a href="#-métodos-de-pagamento">Pagamentos</a> •
  <a href="#-shortcodes">Shortcodes</a> •
  <a href="#-autor">Autor</a>
</p>

---

## 📋 Sobre

O **WP Donate Brasil** é um plugin WordPress desenvolvido especialmente para o mercado brasileiro, oferecendo uma solução completa para receber doações com **PIX**, transferência bancária, Bitcoin e muito mais.

### ✨ Destaques

- 🎨 **Interface moderna** com Tailwind CSS
- 📱 **100% responsivo** (mobile-first)
- 🔒 **Seguro** com validações e sanitizações
- ⚡ **Rápido** e otimizado para performance
- 🇧🇷 **Feito para o Brasil** com PIX nativo

---

## 🚀 Recursos

### 💳 Métodos de Pagamento

| Método | Descrição |
|--------|-----------|
| **PIX** | QR Code dinâmico com copia e cola |
| **Transferência** | Dados bancários completos |
| **Bitcoin** | Endereço com copia e cola |
| **PayPal** | Link direto para doação |
| **Link de Pagamento** | Qualquer gateway externo |

### 📊 Dashboard Administrativo

```
┌─────────────────────────────────────────────────────────┐
│  📈 Dashboard - Relatórios e estatísticas de doações    │
├─────────────────────────────────────────────────────────┤
│  💰 Total Arrecadado  │  ✅ Aprovadas  │  ⏳ Pendentes  │
│  R$ 2.555,08          │  21            │  3             │
├─────────────────────────────────────────────────────────┤
│  🎫 Ticket Médio: R$ 121,67                             │
├─────────────────────────────────────────────────────────┤
│  📈 Gráfico de Doações (Valor R$ x Quantidade)          │
│  Filtros: Mês/Ano                                       │
├─────────────────────────────────────────────────────────┤
│  🍩 Métodos de Pagamento    │  📊 Status dos Comprov.   │
│  Bitcoin, PIX, Transf...    │  Aprovados vs Pendentes   │
├─────────────────────────────────────────────────────────┤
│  🏆 Top Doadores (ranking com valor e quantidade)       │
│  1. Anônimo 31x - R$ 1.798  │  2. Dante 5x - R$ 1.533   │
└─────────────────────────────────────────────────────────┘
```

### 💳 Página de Métodos de Doação

```
┌─────────────────────────────────────────────────────────┐
│  💳 Métodos de Doação - Configure os métodos disponíveis│
├─────────────────────────────────────────────────────────┤
│  ◉ PIX                                          [Ativo] │
│    Chave, Nome do Titular, Cidade, Banco                │
│    QR Code gerado automaticamente                       │
├─────────────────────────────────────────────────────────┤
│  ◉ Transferência Bancária                       [Ativo] │
│    Banco, Agência, Conta, Titular, CPF/CNPJ             │
├─────────────────────────────────────────────────────────┤
│  ◉ PayPal                                       [Ativo] │
│    E-mail do PayPal, Instruções                         │
├─────────────────────────────────────────────────────────┤
│  ◉ Bitcoin                                      [Ativo] │
│    Endereço Bitcoin, Rede (BTC)                         │
├─────────────────────────────────────────────────────────┤
│  ◉ Link de Pagamento                            [Ativo] │
│    Nome do Gateway, URL, Logo (opcional)                │
└─────────────────────────────────────────────────────────┘
```

### 🎯 Funcionalidades Completas

- ✅ **Página de doação** em tela cheia (fullpage)
- ✅ **Galeria de doadores** com carrossel animado
- ✅ **Upload de comprovantes** com validação
- ✅ **Aprovação/Rejeição** de doações
- ✅ **Notificações por e-mail** personalizáveis
- ✅ **Relatórios e gráficos** por período
- ✅ **Top doadores** com ranking
- ✅ **Doações anônimas** opcionais
- ✅ **Shortcodes** flexíveis
- ✅ **Cores personalizáveis** no admin
- ✅ **SEO otimizado** com meta tags
- ✅ **Acessibilidade** WCAG 2.1

---

## 📦 Instalação

### Via Upload (Recomendado)

1. Baixe o arquivo `.zip` do plugin
2. Acesse **WordPress Admin → Plugins → Adicionar Novo**
3. Clique em **Enviar Plugin** e selecione o arquivo
4. Clique em **Instalar Agora** e depois **Ativar**

### Via FTP

1. Extraia o arquivo `.zip`
2. Faça upload da pasta `wp-donate-brasil` para `/wp-content/plugins/`
3. Ative o plugin no painel WordPress

### Via Composer

```bash
composer require dantetesta/wp-donate-brasil
```

---

## ⚙️ Configuração

Após ativar o plugin, acesse **WP Donate Brasil** no menu lateral.

### 1️⃣ Configurações da Página

```
📝 Configurações da Página
├── Frase de Destaque: "Ajude a Aldeia a Sobreviver"
├── Título da Página: "Faça uma Doação"
├── Subtítulo: "Sua contribuição faz a diferença!"
└── Descrição: "Escolha uma das formas..."
```

### 2️⃣ Design Visual

```
🎨 Design Visual
├── Cor Primária: Botões e destaques
└── Cor Secundária: Gradientes
```

### 3️⃣ Galeria de Doadores

```
🖼️ Galeria de Doadores
├── Exibir galeria: ON/OFF
├── Título da Galeria: "Nossos Doadores"
├── Itens no Carrossel: 10
└── Página de Lista Completa (slug): doadores

📋 Campos visíveis na Lista:
├── Foto, Nome, E-mail, Telefone
├── Valor, Qtd. Doações, Data
└── Método, Mensagem

🔍 Filtros na Lista de Doadores:
├── Busca, Método
└── Mês/Ano, Ordenação

⚙️ Outras opções:
└── Exibir créditos do desenvolvedor
```

### 4️⃣ Mensagem de Agradecimento

```
🎉 Mensagem de Agradecimento
├── Título: "Muito Obrigado! 🙏"
├── Subtítulo: "Sua doação faz a diferença!"
└── Mensagem Completa: (personalizável)

✨ Exibida após enviar comprovante
   com animação de confetes!
```

### 5️⃣ Métodos de Pagamento

Configure cada método individualmente:

**PIX:**
```
Chave PIX: seu@email.com
Tipo: E-mail / CPF / CNPJ / Telefone / Aleatória
Nome do Beneficiário: Seu Nome
Cidade: Sua Cidade
```

**Transferência Bancária:**
```
Banco: Banco do Brasil
Agência: 1234-5
Conta: 12345-6
Tipo: Corrente / Poupança
Titular: Seu Nome
CPF/CNPJ: 123.456.789-00
```

**PayPal:**
```
E-mail do PayPal: seu-email@gmail.com
Instruções: Envie sua doação via PayPal.
```

**Bitcoin:**
```
Endereço Bitcoin: bc1qxy2kgdygjrsqtzq2n0yrf...
Rede: Bitcoin (BTC)
Instruções: Envie sua doação para o endereço abaixo.
```

**Link de Pagamento:**
```
Nome do Gateway: Mercado Pago
URL do Link: https://link.mercadopago.com.br/...
Logo (opcional): URL da imagem do gateway
Instruções: Clique no botão para doar via gateway.
```

### 6️⃣ Notificações por E-mail

```
📧 Configurações Gerais
├── Notificar Administrador: ON/OFF
├── Notificar Doador: ON/OFF
├── Nome do Remetente: "Canal Doadores"
└── E-mail do Administrador: admin@site.com

📝 Macros disponíveis:
{nome}, {email}, {valor}, {metodo}, {data}, {mensagem}

✉️ Templates de E-mail:
├── 🔔 Nova Doação (para Admin)
│   └── "Nova doação recebida de {nome}"
├── 📩 Comprovante Recebido (para Doador)
│   └── "Recebemos sua doação, {nome}!"
└── ✅ Doação Aprovada (para Doador)
    └── "Sua doação foi confirmada, {nome}!"
```

### 7️⃣ Ferramentas de Manutenção

```
🛠️ Ferramentas de Manutenção
├── 🧹 Limpar Cache do Plugin
├── 🗑️ Limpar Transientes
└── ⚠️ Deletar Todas as Doações (Zona de Perigo)
```

---

## 🔗 Shortcodes

| Shortcode | Descrição |
|-----------|-----------|
| `[wp_donate_brasil_page]` | Página completa de doações |
| `[wp_donate_brasil_gallery]` | Apenas galeria de doadores |
| `[wdb_donors_list]` | Lista completa de doadores (com paginação) |

---

## 🏗️ Estrutura do Plugin

```
wp-donate-brasil/
├── 📁 admin/
│   ├── class-wdb-admin.php      # Painel administrativo
│   ├── css/                      # Estilos do admin
│   └── js/                       # Scripts do admin
├── 📁 assets/
│   ├── css/
│   │   ├── tailwind.min.css     # Tailwind compilado
│   │   └── fontawesome.min.css  # Font Awesome
│   ├── js/
│   │   ├── chart.min.js         # Gráficos
│   │   ├── swiper.min.js        # Carrossel
│   │   └── aos.min.js           # Animações
│   └── webfonts/                 # Fontes
├── 📁 includes/
│   ├── class-wdb-main.php       # Classe principal
│   ├── class-wdb-donation-page.php  # Página de doação
│   ├── class-wdb-pix-qrcode.php # Gerador PIX
│   └── class-wdb-emails.php     # Sistema de e-mails
├── 📁 public/
│   ├── class-wdb-frontend.php   # Frontend
│   └── templates/
│       └── fullpage-donation.php # Template fullpage
├── 📁 languages/                 # Traduções
├── wp-donate-brasil.php          # Arquivo principal
├── readme.txt                    # Readme WordPress.org
└── uninstall.php                 # Limpeza na desinstalação
```

---

## 🔐 Segurança

O plugin segue as melhores práticas de segurança do WordPress:

- ✅ **Nonces** em todas as requisições AJAX
- ✅ **Capability checks** para ações administrativas
- ✅ **Sanitização** de todos os inputs
- ✅ **Escape** de todos os outputs
- ✅ **Prepared statements** para queries SQL
- ✅ **Validação** de uploads (tipo, tamanho)

---

## 🌐 Compatibilidade

| Requisito | Versão Mínima |
|-----------|---------------|
| WordPress | 6.0+ |
| PHP | 7.4+ |
| MySQL | 5.7+ |

---

## 📈 Changelog

### 2.0.2 (23/12/2025)
- 🆕 Sistema de Tabs nas Configurações (Página, Galeria, E-mails, Ferramentas)
- 🆕 Grid de cards para Métodos de Pagamento
- 🆕 Offcanvas para configurar cada método (slide da direita)
- 🆕 Switch de ativar/desativar nos cards de métodos
- 🆕 Botão copiar nos shortcodes
- 🔧 Notificação apenas toast flutuante (removida interna)
- 🔧 Melhorias de UX e responsividade

### 2.0.1 (23/12/2025)
- 🔧 Bitcoin exibe ícone ao invés de QR Code
- 🔧 Correção na coluna attachment_id do banco
- 🔧 Top Doadores inclui anônimos no admin

### 2.0.0 (23/12/2025)
- 🆕 Dashboard com gráficos interativos
- 🆕 Top Doadores com ranking
- 🆕 Filtros por período (mês/ano)
- 🆕 Sistema de e-mails personalizáveis
- 🆕 Suporte a Bitcoin
- 🆕 Galeria de doadores com carrossel
- 🔧 Assets locais (sem CDNs externos)
- 🔧 Melhorias de performance

### 1.0.0 (20/12/2025)
- 🎉 Lançamento inicial
- ✅ PIX com QR Code
- ✅ Transferência bancária
- ✅ Upload de comprovantes
- ✅ Painel administrativo

---

## 🤝 Contribuindo

Contribuições são bem-vindas! 

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFeature`)
3. Commit suas mudanças (`git commit -m 'Add: Nova feature'`)
4. Push para a branch (`git push origin feature/NovaFeature`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está licenciado sob a **GPL v2 or later** - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 👨‍💻 Autor

<p align="center">
  <img src="https://avatars.githubusercontent.com/dantetesta" width="100" height="100" style="border-radius: 50%;">
</p>

<p align="center">
  <strong>Dante Testa</strong><br>
  Desenvolvedor WordPress Full Stack
</p>

<p align="center">
  <a href="https://dantetesta.com.br">🌐 Website</a> •
  <a href="https://github.com/dantetesta">💻 GitHub</a>
</p>

---

<p align="center">
  Feito com ❤️ no Brasil 🇧🇷
</p>

<p align="center">
  <sub>© 2025 Dante Testa. Todos os direitos reservados.</sub>
</p>
