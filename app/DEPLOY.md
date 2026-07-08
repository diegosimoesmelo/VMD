# Publicacao em producao

Este projeto roda em producao com Docker Compose, Caddy, Laravel e PostgreSQL.

## Enderecos

Neste projeto, a landing page fica na Vercel e o sistema administrativo fica na VPS:

```text
https://autoescolavmd.com.br      -> Landing page Next.js na Vercel
https://app.autoescolavmd.com.br  -> Sistema VMD Laravel na VPS
```

Na landing page, o botao de area administrativa deve apontar para:

```text
https://app.autoescolavmd.com.br
```

## Sem dominio

Para publicar primeiro pelo IP da VPS, use:

```env
APP_URL=http://161.97.120.221
APP_SITE_ADDRESS=:80
APP_DOMAIN=
SESSION_SECURE_COOKIE=false
```

Nesse modo o acesso fica:

```text
http://161.97.120.221
```

## Com dominio

Para o dominio `autoescolavmd.com.br`, use:

```env
APP_URL=https://app.autoescolavmd.com.br
APP_SITE_ADDRESS=app.autoescolavmd.com.br
APP_DOMAIN=autoescolavmd.com.br
SESSION_SECURE_COOKIE=true
```

## DNS

Como a landing esta na Vercel, o dominio principal deve seguir as instrucoes de DNS da Vercel. Em geral, a Vercel vai pedir registros para:

```text
autoescolavmd.com.br
www.autoescolavmd.com.br
```

Para o sistema administrativo na VPS, crie tambem:

```text
Tipo: A
Nome: app
Valor: 161.97.120.221
```

Aguarde a propagacao antes de subir o HTTPS do sistema.

## Preparar a VPS

```bash
apt update
apt upgrade -y
apt install -y ca-certificates curl git ufw
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" > /etc/apt/sources.list.d/docker.list
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 5050/tcp
ufw --force enable
```

## Publicar

```bash
mkdir -p /opt/vmd
cd /opt/vmd
git clone https://github.com/diegosimoesmelo/VMD.git .
cd app
cp .env.production.example .env.production
```

Edite `.env.production`:

```bash
nano .env.production
```

Preencha principalmente:

```env
APP_URL=https://app.autoescolavmd.com.br
APP_SITE_ADDRESS=app.autoescolavmd.com.br
APP_DOMAIN=autoescolavmd.com.br
DB_PASSWORD=senha_forte
POSTGRES_PASSWORD=senha_forte
SESSION_SECURE_COOKIE=true
PGADMIN_DEFAULT_EMAIL=admin@vmd.local
PGADMIN_DEFAULT_PASSWORD=senha_forte_do_pgadmin
MAIL_*
```

Gere a chave:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml run --rm --entrypoint php app artisan key:generate --show
```

Copie o valor gerado para `APP_KEY` no `.env.production`.

Suba o ambiente:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
```

Verifique:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml ps
docker compose --env-file .env.production -f docker-compose.prod.yml logs -f app
docker compose --env-file .env.production -f docker-compose.prod.yml logs -f caddy
```

## pgAdmin

O pgAdmin fica disponivel em:

```text
http://161.97.120.221:5050
```

Entre com:

```env
PGADMIN_DEFAULT_EMAIL=admin@vmd.local
PGADMIN_DEFAULT_PASSWORD=senha_forte_do_pgadmin
```

Para cadastrar o servidor PostgreSQL no pgAdmin:

```text
Host: db
Port: 5432
Database: vmd
Username: vmd_app
Password: mesma senha de DB_PASSWORD/POSTGRES_PASSWORD
```

O PostgreSQL continua privado dentro da rede Docker. Se a porta 5432 tiver sido liberada no firewall durante testes, remova:

```bash
ufw delete allow 5432/tcp
```

## Atualizar versao

```bash
cd /opt/vmd/app
git pull
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Backup

O servico `backup` gera um dump diario em:

```text
/opt/vmd/app/backups
```

Ele mantem os ultimos 14 dias de arquivos `.dump`.
