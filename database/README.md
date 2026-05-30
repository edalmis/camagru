# Database Management

This directory contains database initialization and migration scripts for the Camagru application.

## Structure

- `init.sql` - Initial database setup (charset and collation)
- `migrations/` - Individual table creation scripts

## Tables

- `users` - Registered accounts and password hashes
- `images` - Uploaded image metadata and ownership
- `image_likes` - Per-user likes for each image
- `image_comments` - Per-user comments for each image
- `auth_tokens` - Optional persistent auth/remember-me tokens

## Database Details

- **Host**: camagru_db (Docker service)
- **Port**: 3306
- **Database/User/Password**: loaded from local `.env`

The application still uses PHP sessions for login state; `auth_tokens` is reserved for future remember-me/session-token support.

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
