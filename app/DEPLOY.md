# Publicacao em producao

Este projeto roda em producao com Docker Compose, Caddy, Laravel e PostgreSQL.

## O que preciso da VPS

- IP publico da VPS Contabo.
- Usuario SSH e forma de acesso: senha ou chave SSH.
- Dominio que sera usado no sistema, se ja existir.
- Acesso ao painel DNS do dominio, se ja existir.
- Confirmacao se o banco comeca vazio ou se existe dados para importar.

## Sem dominio

Para publicar primeiro pelo IP da VPS, use:

```env
APP_URL=http://161.97.120.221
APP_SITE_ADDRESS=:80
APP_DOMAIN=
```

Nesse modo o acesso fica:

```text
http://161.97.120.221
```

## Com dominio

Quando tiver um dominio, altere:

```env
APP_URL=https://seudominio.com.br
APP_SITE_ADDRESS=seudominio.com.br
APP_DOMAIN=seudominio.com.br
```

## DNS

No painel do dominio, crie:

```text
Tipo: A
Nome: @
Valor: IP_DA_VPS

Tipo: CNAME
Nome: www
Valor: seudominio.com.br
```

Aguarde a propagacao antes de subir o HTTPS.

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
APP_URL=http://161.97.120.221
APP_SITE_ADDRESS=:80
APP_DOMAIN=
DB_PASSWORD=senha_forte
POSTGRES_PASSWORD=senha_forte
MAIL_*
```

Gere a chave:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml run --rm app php artisan key:generate --show
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
