# Docker setup

Services:
- `php`: PHP-FPM 8.2 with `pdo_mysql`
- `nginx`: serves `public/` on port `8080`
- `db`: MariaDB 11 on port `3306`

## Start

1. Create a local env file:

```bash
cp .env.example .env
```

2. Set your local credentials and secrets in `.env`.

3. Start containers:

```bash
docker compose up -d --build
```

## Stop

```bash
docker compose down
```

## Remove with DB volume

```bash
docker compose down -v
```
