# 📦 Guia de Instalação - Game Hub

## Requisitos do Sistema

### Software Necessário
- **Sistema Operacional**: Linux (Ubuntu 22.04 ou superior recomendado) ou Windows com XAMPP/WAMP
- **Servidor Web**: Apache 2.4+
- **PHP**: 8.1 ou superior
- **Banco de Dados**: MySQL 8.0 ou superior

### Extensões PHP Necessárias
- PDO
- PDO_MySQL
- mbstring
- session

## 🔧 Instalação no Linux (Ubuntu/Debian)

### Passo 1: Instalar Dependências

```bash
# Atualizar repositórios
sudo apt-get update

# Instalar Apache, PHP e MySQL
sudo apt-get install -y apache2 php libapache2-mod-php php-mysql mysql-server

# Verificar instalação
php -v
mysql --version
apache2 -v
```

### Passo 2: Configurar MySQL

```bash
# Acessar MySQL
sudo mysql

# Criar banco de dados (executar dentro do MySQL)
CREATE DATABASE gamehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Passo 3: Importar Banco de Dados

```bash
# Navegar até a pasta do projeto
cd /caminho/para/gamehub

# Importar estrutura e dados
sudo mysql gamehub < database.sql
```

### Passo 4: Configurar Apache

```bash
# Copiar projeto para o diretório do Apache
sudo cp -r /caminho/para/gamehub /var/www/html/

# Ou criar um link simbólico
sudo ln -s /caminho/para/gamehub /var/www/html/gamehub

# Configurar permissões
sudo chown -R www-data:www-data /var/www/html/gamehub
sudo chmod -R 755 /var/www/html/gamehub
```

### Passo 5: Iniciar Serviços

```bash
# Iniciar Apache
sudo service apache2 start

# Iniciar MySQL
sudo service mysql start

# Configurar para iniciar automaticamente
sudo systemctl enable apache2
sudo systemctl enable mysql
```

### Passo 6: Acessar o Site

Abra o navegador e acesse:
```
http://localhost/gamehub/
```

## 🪟 Instalação no Windows (XAMPP)

### Passo 1: Instalar XAMPP

1. Baixe o XAMPP em: https://www.apachefriends.org/
2. Execute o instalador
3. Instale com Apache, MySQL e PHP

### Passo 2: Copiar Arquivos

1. Copie a pasta `gamehub` para `C:\xampp\htdocs\`
2. O caminho final deve ser: `C:\xampp\htdocs\gamehub\`

### Passo 3: Iniciar Serviços

1. Abra o XAMPP Control Panel
2. Clique em "Start" para Apache
3. Clique em "Start" para MySQL

### Passo 4: Criar Banco de Dados

1. Acesse: http://localhost/phpmyadmin/
2. Clique em "Novo" para criar um banco
3. Nome: `gamehub`
4. Collation: `utf8mb4_unicode_ci`
5. Clique em "Criar"

### Passo 5: Importar Dados

1. Selecione o banco `gamehub`
2. Clique na aba "Importar"
3. Escolha o arquivo `database.sql`
4. Clique em "Executar"

### Passo 6: Acessar o Site

Abra o navegador e acesse:
```
http://localhost/gamehub/
```

## ⚙️ Configuração

### Editar Configurações do Banco

Abra o arquivo `config.php` e ajuste se necessário:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Senha do MySQL (vazio no XAMPP por padrão)
define('DB_NAME', 'gamehub');
```

### Configurar URL do Site

Se o site estiver em uma pasta diferente, ajuste:

```php
define('SITE_URL', 'http://localhost/gamehub');
```

## 🔐 Primeiro Acesso

### Login Administrativo

Use estas credenciais para acessar como administrador:

- **E-mail**: admin@gamehub.com
- **Senha**: admin123

**⚠️ IMPORTANTE**: Altere a senha do administrador após o primeiro acesso!

### Criar Novo Usuário Cliente

1. Acesse: http://localhost/gamehub/registro.php
2. Preencha o formulário de cadastro
3. Faça login com suas credenciais

## 🧪 Testar Funcionalidades

### Teste 1: Navegação
- Acesse a página inicial
- Navegue pelo catálogo
- Use os filtros de busca

### Teste 2: Compra
- Faça login como cliente
- Adicione jogos ao carrinho
- Finalize uma compra

### Teste 3: Sistema de Trocas
- Acesse a página de trocas
- Proponha uma troca
- Verifique as propostas

### Teste 4: Área Admin
- Faça login como admin
- Acesse o painel administrativo
- Adicione um novo jogo
- Gerencie pedidos

## 🐛 Solução de Problemas Comuns

### Erro: "Connection refused"

**Causa**: MySQL não está rodando

**Solução**:
```bash
# Linux
sudo service mysql start

# Windows (XAMPP)
Inicie o MySQL pelo XAMPP Control Panel
```

### Erro: "Access denied for user"

**Causa**: Credenciais incorretas do banco

**Solução**: Verifique usuário e senha no `config.php`

### Erro: "Table doesn't exist"

**Causa**: Banco de dados não foi importado

**Solução**: Execute novamente:
```bash
mysql -u root gamehub < database.sql
```

### Erro 404: Página não encontrada

**Causa**: Apache não está rodando ou caminho incorreto

**Solução**:
```bash
# Verificar se Apache está rodando
sudo service apache2 status

# Verificar caminho do projeto
ls -la /var/www/html/gamehub
```

### Erro: "Permission denied"

**Causa**: Permissões incorretas dos arquivos

**Solução**:
```bash
sudo chown -R www-data:www-data /var/www/html/gamehub
sudo chmod -R 755 /var/www/html/gamehub
```

### Página em branco (sem erros)

**Causa**: Erros do PHP não estão sendo exibidos

**Solução**: Ative a exibição de erros no `config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 🔄 Atualização

Para atualizar o sistema:

1. Faça backup do banco de dados:
```bash
mysqldump -u root gamehub > backup_gamehub.sql
```

2. Faça backup dos arquivos:
```bash
cp -r /var/www/html/gamehub /backup/gamehub_backup
```

3. Substitua os arquivos novos
4. Execute scripts de atualização do banco (se houver)

## 📊 Monitoramento

### Verificar Logs do Apache

```bash
# Linux
sudo tail -f /var/log/apache2/error.log

# Windows (XAMPP)
C:\xampp\apache\logs\error.log
```

### Verificar Logs do MySQL

```bash
# Linux
sudo tail -f /var/log/mysql/error.log

# Windows (XAMPP)
C:\xampp\mysql\data\mysql_error.log
```

## 🚀 Otimização

### Melhorar Performance

1. **Ativar cache do PHP**:
```bash
sudo apt-get install php-apcu
```

2. **Otimizar MySQL**:
```sql
OPTIMIZE TABLE jogos, pedidos, usuarios;
```

3. **Ativar compressão no Apache**:
```bash
sudo a2enmod deflate
sudo service apache2 restart
```

## 🌐 Publicar na Internet

### Usando um Servidor VPS

1. Contrate um VPS (DigitalOcean, AWS, etc.)
2. Configure domínio apontando para o IP do servidor
3. Instale certificado SSL (Let's Encrypt):
```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d seudominio.com
```

4. Configure firewall:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Configurações de Segurança

1. **Alterar senha do admin**
2. **Configurar senha forte no MySQL**
3. **Desabilitar exibição de erros em produção**
4. **Configurar backups automáticos**

## 📞 Suporte

Se encontrar problemas durante a instalação:

1. Verifique os logs de erro
2. Consulte a documentação do PHP/MySQL/Apache
3. Entre em contato: contato@gamehub.com

## ✅ Checklist de Instalação

- [ ] Apache instalado e rodando
- [ ] MySQL instalado e rodando
- [ ] PHP 8.1+ instalado
- [ ] Banco de dados criado
- [ ] Estrutura importada (database.sql)
- [ ] Arquivos copiados para /var/www/html/
- [ ] Permissões configuradas
- [ ] config.php configurado
- [ ] Site acessível no navegador
- [ ] Login admin funcionando
- [ ] Cadastro de cliente funcionando
- [ ] Compra funcionando
- [ ] Sistema de trocas funcionando

---

**Instalação concluída com sucesso! 🎉**

Agora você está pronto para usar o Game Hub!
