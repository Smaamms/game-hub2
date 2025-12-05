# Game Hub - Sistema de Compra, Venda e Troca de Jogos

## 📋 Descrição

O **Game Hub** é uma plataforma completa para compra, venda e troca de jogos de Xbox e PlayStation. O sistema oferece um catálogo completo de jogos, carrinho de compras, checkout, área administrativa e sistema de trocas entre usuários.

## 🚀 Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3
- **Backend**: PHP 8.1
- **Banco de Dados**: MySQL 8.0
- **Servidor Web**: Apache 2.4

## 📁 Estrutura do Projeto

```
gamehub/
├── admin/                  # Área administrativa
│   ├── index.php          # Dashboard do admin
│   └── jogos.php          # Gerenciamento de jogos
├── css/                   # Arquivos de estilo
│   └── style.css          # Estilos principais
├── includes/              # Arquivos incluídos
│   ├── header.php         # Cabeçalho do site
│   └── footer.php         # Rodapé do site
├── images/                # Imagens do site
├── config.php             # Configurações e conexão com BD
├── database.sql           # Script de criação do banco
├── index.php              # Página inicial
├── catalogo.php           # Catálogo de jogos
├── jogo.php               # Detalhes do jogo
├── login.php              # Login de usuários
├── registro.php           # Registro de novos usuários
├── logout.php             # Logout
├── carrinho.php           # Carrinho de compras
├── adicionar-carrinho.php # Adicionar item ao carrinho
├── checkout.php           # Finalização da compra
├── minha-conta.php        # Área do cliente
├── trocas.php             # Sistema de trocas
├── propor-troca.php       # Propor nova troca
└── README.md              # Esta documentação
```

## 🗄️ Banco de Dados

### Tabelas Principais

1. **usuarios** - Armazena dados dos usuários (clientes e administradores)
2. **jogos** - Catálogo de jogos disponíveis
3. **categorias** - Categorias dos jogos (Ação, RPG, Esportes, etc.)
4. **pedidos** - Pedidos realizados pelos clientes
5. **itens_pedido** - Itens de cada pedido
6. **carrinho** - Itens no carrinho de cada usuário
7. **trocas** - Propostas de troca entre usuários
8. **avaliacoes** - Avaliações dos jogos pelos clientes

### Instalação do Banco de Dados

O banco de dados já foi criado automaticamente. Para recriar:

```bash
mysql -u root < database.sql
```

## 👤 Credenciais de Acesso

### Administrador Padrão
- **E-mail**: admin@gamehub.com
- **Senha**: admin123

### Criar Novo Usuário
Acesse a página de registro em: `/registro.php`

## 🎮 Funcionalidades

### Para Clientes

1. **Navegação e Busca**
   - Catálogo completo de jogos
   - Filtros por plataforma, categoria e condição
   - Busca por título
   - Visualização de detalhes do jogo

2. **Compras**
   - Adicionar jogos ao carrinho
   - Atualizar quantidades
   - Remover itens
   - Finalizar compra com múltiplas formas de pagamento
   - Acompanhar status dos pedidos

3. **Sistema de Trocas**
   - Propor trocas de jogos
   - Receber propostas de outros usuários
   - Aceitar ou recusar propostas
   - Acompanhar histórico de trocas

4. **Conta do Usuário**
   - Visualizar histórico de pedidos
   - Gerenciar dados pessoais
   - Acompanhar trocas

### Para Administradores

1. **Dashboard**
   - Estatísticas gerais (jogos, usuários, pedidos, vendas)
   - Pedidos recentes
   - Visão geral do sistema

2. **Gerenciamento de Jogos**
   - Adicionar novos jogos
   - Editar jogos existentes
   - Remover jogos
   - Controlar estoque
   - Definir jogos em destaque

3. **Gerenciamento de Pedidos**
   - Visualizar todos os pedidos
   - Atualizar status dos pedidos
   - Ver detalhes completos

4. **Gerenciamento de Trocas**
   - Monitorar propostas de troca
   - Moderar trocas entre usuários

## 🎨 Plataformas Suportadas

- Xbox One
- Xbox Series X/S
- PlayStation 4
- PlayStation 5

## 📦 Categorias de Jogos

- Ação
- RPG
- Esportes
- Tiro
- Estratégia
- Luta
- Corrida
- Aventura

## 💳 Formas de Pagamento

- Cartão de Crédito
- Cartão de Débito
- PIX
- Boleto Bancário

## 🔒 Segurança

- Senhas criptografadas com `password_hash()` do PHP
- Proteção contra SQL Injection usando PDO com prepared statements
- Sanitização de entradas do usuário
- Validação de dados no servidor
- Controle de acesso baseado em sessões
- Área administrativa protegida

## 🌐 Acesso ao Site

### URL Local
```
http://localhost/gamehub/
```

### URL Pública (Temporária)
```
https://80-ig71c0hagzge2skj1t4tp-83fbdfa2.manusvm.computer/gamehub/
```

## 📝 Configuração

### Configurações do Banco de Dados

Edite o arquivo `config.php` para alterar as configurações:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamehub');
```

### Configurações do Site

```php
define('SITE_NAME', 'Game Hub');
define('SITE_URL', 'http://localhost/gamehub');
```

## 🚀 Como Usar

### 1. Acessar o Site
Abra o navegador e acesse a URL do site.

### 2. Criar uma Conta
- Clique em "Cadastrar"
- Preencha o formulário de registro
- Faça login com suas credenciais

### 3. Navegar pelo Catálogo
- Explore os jogos disponíveis
- Use os filtros para encontrar jogos específicos
- Clique em "Ver Detalhes" para mais informações

### 4. Fazer uma Compra
- Adicione jogos ao carrinho
- Acesse o carrinho
- Finalize a compra preenchendo os dados de entrega
- Escolha a forma de pagamento

### 5. Propor uma Troca
- Acesse "Trocas" no menu
- Clique em "Propor Nova Troca"
- Selecione o jogo que você oferece
- Opcionalmente, selecione o jogo que deseja
- Envie a proposta

### 6. Área Administrativa (Admin)
- Faça login com credenciais de administrador
- Acesse "Admin" no menu
- Gerencie jogos, pedidos e usuários

## 🎯 Recursos Principais

### Design Responsivo
O site é totalmente responsivo e se adapta a diferentes tamanhos de tela (desktop, tablet, mobile).

### Interface Intuitiva
Design moderno com gradientes coloridos, cards bem estruturados e navegação fácil.

### Sistema Completo
Todas as funcionalidades de um e-commerce real:
- Catálogo de produtos
- Carrinho de compras
- Checkout
- Gerenciamento de pedidos
- Sistema de trocas
- Área administrativa

### Performance
- Consultas otimizadas ao banco de dados
- Índices nas tabelas principais
- Cache de sessão

## 🐛 Solução de Problemas

### Erro de Conexão com o Banco
Verifique se o MySQL está rodando:
```bash
sudo service mysql status
sudo service mysql start
```

### Erro 404 - Página não encontrada
Verifique se o Apache está rodando:
```bash
sudo service apache2 status
sudo service apache2 start
```

### Permissões de Arquivo
Se houver problemas de permissão:
```bash
sudo chmod -R 755 /home/ubuntu/gamehub
sudo chown -R www-data:www-data /home/ubuntu/gamehub
```

## 📊 Dados de Exemplo

O banco de dados já vem com:
- 1 usuário administrador
- 8 categorias de jogos
- 12 jogos de exemplo (Xbox e PlayStation)

## 🔄 Atualizações Futuras

Possíveis melhorias para versões futuras:
- Upload de imagens reais dos jogos
- Sistema de avaliações com comentários
- Chat entre usuários para negociar trocas
- Integração com APIs de pagamento reais
- Sistema de notificações por e-mail
- Histórico de preços
- Wishlist de jogos
- Comparação de jogos
- Sistema de cupons de desconto

## 📞 Suporte

Para dúvidas ou problemas, entre em contato:
- E-mail: contato@gamehub.com
- Telefone: (11) 9999-9999

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e demonstrativos.

---

**Desenvolvido com ❤️ para a comunidade gamer!**

🎮 Game Hub - Sua plataforma de jogos favorita!
