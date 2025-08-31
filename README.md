# Deploy de aplicação PHP em EC2 com banco RDS PostgreSQL

Este guia mostra como preparar a instância EC2, clonar o repositório, conectar ao RDS PostgreSQL, criar as tabelas e colocar a aplicação PHP no ar.

## Vídeo explicando Deploy

https://drive.google.com/file/d/1Z9iYFhxsrx_k-7C_RyMBCxwyyL6FaVcx/view?usp=sharing

## Pré-condições

* Uma instância EC2 foi criada com acesso via SSH e HTTP.
* Um RDS PostgreSQL está rodando (endpoint, usuário com permissões e senha).
* O Security Group da EC2 permite tráfego HTTP (porta 80) e o da RDS permite tráfego na porta 5432 vindo da EC2.

---

## Estrutura do repositório

O repositório está organizado conforme a seguinte estrutura:

```
projeto/
├── html/
│   ├── Membros.php          # cadastro e listagem de MEMBROS
│   └── SamplePage.php       # página de exemplo do tutorial AWS
├── inc/
│   └── dbinfo.inc.template  # template para credenciais do banco
├── .gitignore               # ignora credenciais e arquivos temporários
└── README.md                # você está aqui
```

---

## Descrição dos arquivos

* **html/Membros.php** — página que cria/verifica a tabela `MEMBROS` (ID, NOME, NOTA, PRESENCA) e mostra formulário + listagem.
* **html/SamplePage.php** — página de exemplo do tutorial AWS para criação e listagem da tabela EMPLOYEES.
* **inc/dbinfo.inc.template** — modelo para criar `dbinfo.inc` com `DB_SERVER`, `DB_USERNAME`, `DB_PASSWORD` e `DB_DATABASE`.
* **.gitignore** — deve incluir `inc/dbinfo.inc` para proteger suas credenciais, além de arquivos temporários e logs.
* **README.md** — instruções para deploy e uso da aplicação.

---

## Passo a passo na EC2

Substitua `<...>` pelos valores corretos da sua instância ou RDS.

### 1) Conectar à EC2

```bash
ssh -i </caminho/para/sua-key.pem> ec2-user@<EC2_PUBLIC_IP>
```

### 2) Atualizar e instalar Apache + PHP + cliente PostgreSQL

Amazon Linux 2:

```bash
sudo yum update -y
sudo amazon-linux-extras enable php8.0
sudo yum clean metadata
sudo yum install -y httpd php php-pgsql git
sudo systemctl start httpd
sudo systemctl enable httpd
```

Ubuntu:

```bash
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-pgsql git
sudo systemctl start apache2
sudo systemctl enable apache2
```

Verifique no navegador: `http://<EC2_PUBLIC_DNS>` — deve aparecer a página padrão do Apache.

### 3) Ajustar permissões do DocumentRoot

```bash
sudo usermod -a -G apache ec2-user      # Amazon Linux
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
sudo find /var/www -type d -exec sudo chmod 2775 {} \;
sudo find /var/www -type f -exec sudo chmod 0664 {} \;
```

Saia e reconecte-se via SSH para aplicar o grupo.

### 4) Clonar o repositório

```bash
cd /var/www/html
git clone <URL_DO_REPOSITORIO> projeto-rds
cd projeto-rds
```

### 5) Criar `dbinfo.inc` com as credenciais do PostgreSQL

```bash
mkdir -p /var/www/inc
cat > /var/www/inc/dbinfo.inc <<'PHP'
<?php
define('DB_SERVER', '<SEU_RDS_ENDPOINT>');
define('DB_USERNAME', '<SEU_USUARIO>');
define('DB_PASSWORD', '<SUA_SENHA>');
define('DB_DATABASE', '<SEU_DATABASE>');
?>
PHP

sudo chown ec2-user:apache /var/www/inc/dbinfo.inc
sudo chmod 640 /var/www/inc/dbinfo.inc
```

> O arquivo real `dbinfo.inc` **não deve ser versionado**, use `.gitignore` para protegê-lo.

### 6) Configurar Security Groups

* RDS: permite tráfego na porta 5432 da EC2.
* EC2: permite inbound HTTP (80) e SSH (22) apenas do seu IP.

### 7) Testar aplicação

Abra no navegador:

* `http://<EC2_PUBLIC_DNS>/projeto-rds/html/SamplePage.php`
* `http://<EC2_PUBLIC_DNS>/projeto-rds/html/Membros.php`

A página `Membros.php` permite cadastrar novos membros e listar os existentes na tabela `MEMBROS`.

---

## Referência

Baseado nos tutoriais oficiais AWS:

* [Install a web server on your EC2 instance](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/EC2_GetStarted.html)
* [Connect your Apache web server to your DB instance](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_CreatePostgreSQLInstance.html)
