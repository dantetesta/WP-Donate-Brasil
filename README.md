<p align="center">
  <img src="https://img.shields.io/badge/WordPress-6.0+-blue?style=for-the-badge&logo=wordpress" alt="WordPress">
  <img src="https://img.shields.io/badge/PHP-7.4+-purple?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/License-GPL%20v2-green?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Version-2.0.1-orange?style=for-the-badge" alt="Version">
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
│  📈 Dashboard de Doações                                │
├─────────────────────────────────────────────────────────┤
│  💰 Total Arrecadado    │  📦 Total de Doações         │
│  R$ 15.420,00           │  127 doações                  │
├─────────────────────────────────────────────────────────┤
│  📊 Gráfico de Evolução │  🏆 Top Doadores              │
│  [█████████░░] Jan-Dez  │  1. Roberto - R$ 1.500        │
│                         │  2. Maria - R$ 980            │
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
```

### 4️⃣ Métodos de Pagamento

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

### 4️⃣ Notificações por E-mail

```
📧 E-mails Automáticos
├── ✉️ Notificar Admin: Nova doação recebida
├── ✉️ Notificar Doador: Comprovante recebido
└── ✉️ Notificar Doador: Doação aprovada

📝 Macros disponíveis:
{nome}, {email}, {valor}, {metodo}, {data}, {mensagem}
```

---

## 🔗 Shortcodes

### Página de Doação Completa

```php
[wdb_donation_page]
```

Exibe a página completa de doação com todos os métodos configurados.

### Botão de Doação

```php
[wdb_donate_button text="Doe Agora" class="minha-classe"]
```

| Parâmetro | Descrição | Padrão |
|-----------|-----------|--------|
| `text` | Texto do botão | "Fazer Doação" |
| `class` | Classes CSS extras | "" |

### Galeria de Doadores

```php
[wdb_donors_gallery limit="12" columns="4"]
```

| Parâmetro | Descrição | Padrão |
|-----------|-----------|--------|
| `limit` | Quantidade de doadores | 10 |
| `columns` | Colunas no grid | 3 |

### Total Arrecadado

```php
[wdb_total_donations]
```

Exibe o valor total de doações aprovadas.

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

### Testado com:

- ✅ Elementor
- ✅ WooCommerce
- ✅ Yoast SEO
- ✅ Contact Form 7
- ✅ Cache plugins (WP Super Cache, W3 Total Cache)

---

## 📈 Changelog

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
