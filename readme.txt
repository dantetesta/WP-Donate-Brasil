=== WP Donate Brasil ===
Contributors: dantetesta
Donate link: https://dantetesta.com.br
Tags: doações, pix, pagamentos, brasil, donations
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema completo de doações para sites brasileiros com PIX, múltiplos métodos de pagamento e galeria de doadores.

== Description ==

O **WP Donate Brasil** é um plugin completo para gerenciar doações em sites WordPress brasileiros. 

= Recursos Principais =

* **Página de Doações Personalizável** - Crie uma página completa de doações com design moderno
* **Múltiplos Métodos de Pagamento** - PIX, Transferência Bancária, PayPal, Bitcoin e Link de Pagamento
* **QR Code PIX Automático** - Geração automática de QR Code para pagamentos PIX
* **Galeria de Doadores** - Exiba seus doadores em uma galeria pública (com opção de anonimato)
* **Dashboard Completo** - Relatórios e gráficos de doações por período
* **Top Doadores** - Ranking dos maiores doadores
* **Comprovantes** - Sistema de upload e gestão de comprovantes de doação
* **E-mails Automáticos** - Notificações por e-mail para doadores e administradores
* **Responsivo** - Design adaptado para desktop, tablet e mobile

= Métodos de Pagamento =

* **PIX** - Com geração automática de QR Code e código copia-e-cola
* **Transferência Bancária** - Exibe dados bancários para transferência
* **PayPal** - Integração com link do PayPal.me
* **Bitcoin** - Endereço de carteira para doações em criptomoeda
* **Link de Pagamento** - Link personalizado para qualquer gateway

= Shortcodes =

* `[wdb_donation_form]` - Formulário de doação
* `[wdb_donors_gallery]` - Galeria de doadores
* `[wdb_donors_page]` - Página completa de doadores

== Installation ==

1. Faça upload da pasta `wp-donate-brasil` para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Acesse 'Doações' no menu lateral para configurar
4. Configure os métodos de pagamento desejados
5. Crie uma página e adicione o shortcode `[wdb_donation_form]`

== Frequently Asked Questions ==

= O plugin funciona com qualquer tema? =

Sim, o plugin foi desenvolvido para funcionar com qualquer tema WordPress.

= Posso personalizar as cores? =

Sim, nas configurações do plugin você pode definir cores primária e secundária.

= O PIX é automático? =

O QR Code é gerado automaticamente, mas a confirmação do pagamento é manual através do upload de comprovante.

= Posso usar apenas alguns métodos de pagamento? =

Sim, você pode habilitar/desabilitar cada método individualmente.

= Os doadores podem ser anônimos? =

Sim, existe a opção de doação anônima que oculta o nome do doador na galeria.

== Screenshots ==

1. Dashboard com gráficos e estatísticas
2. Página de doações no frontend
3. Configuração de métodos de pagamento
4. Galeria de doadores
5. Gestão de comprovantes

== Changelog ==

= 1.0.0 =
* Lançamento inicial
* Sistema completo de doações
* Múltiplos métodos de pagamento (PIX, Transferência, PayPal, Bitcoin, Link)
* Dashboard com relatórios e gráficos
* Top Doadores com ranking
* Galeria de doadores
* Sistema de comprovantes
* E-mails automáticos
* Filtros por período (mês, ano, todo período)

== Upgrade Notice ==

= 1.0.0 =
Primeira versão estável do plugin.

== Privacy Policy ==

Este plugin armazena dados de doadores (nome, e-mail, valor doado) no banco de dados do WordPress. Os dados são utilizados exclusivamente para gestão de doações e não são compartilhados com terceiros.

Dados armazenados:
* Nome do doador
* E-mail do doador
* Valor da doação
* Data da doação
* Comprovante (se enviado)
* Mensagem (opcional)

Os doadores podem solicitar a remoção de seus dados através do administrador do site.
