# Docker setup

Services:
- `php`: PHP-FPM 8.2 with `pdo_mysql`
- `nginx`: serves `public/` on port `8080`
- `db`: MariaDB 11 on port `3306`

## Start

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
