# Database Management

This directory contains database initialization and migration scripts for the Camagru application.

## Structure

- `init.sql` - Initial database setup (charset and collation)
- `migrations/` - Individual table creation scripts (to be added)

## Database Details

- **Host**: camagru_db (Docker service)
- **Port**: 3306
- **Database/User/Password**: loaded from local `.env`

## Setup

The database is automatically created by Docker Compose when the containers start. The `init.sql` script is used to set up the initial database configuration.

To manually initialize or reset the database:

```bash
docker-compose exec db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < database/init.sql
```

To access the database directly:

```bash
docker-compose exec db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

Or with root access:

```bash
docker-compose exec db sh -lc 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```
