# Camagru

A web application for photo manipulation and sharing.

## Project Structure

```
├── app/                 # Application logic
│   ├── controllers/    # Request handlers
│   ├── models/         # Data models
│   └── views/          # View templates
├── config/             # Configuration files
│   └── database.php    # Database configuration
├── database/           # Database files
│   ├── init.sql       # Database initialization
│   └── migrations/    # Table creation scripts
├── docker/             # Docker configuration
│   ├── nginx/         # Nginx web server config
│   └── php/           # PHP container config
├── public/             # Publicly accessible files
│   ├── index.php      # Application entry point
│   └── assets/        # Static assets (CSS, JS, images)
└── uploads/            # User uploaded files
```

## Setup and Installation

### Prerequisites

- Docker and Docker Compose
- Git

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd camagru
   ```

2. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

3. **Initialize the database**
   The database is automatically created by Docker Compose with the following credentials:
   - **Database**: camagru
   - **User**: camagru
   - **Password**: camagru

4. **Access the application**
   Open your browser and navigate to: `http://localhost:8080`

## Development

### Database Management

See [database/README.md](database/README.md) for database operations and migrations.

### Running Commands in Containers

```bash
# Access PHP container
docker-compose exec php bash

# Access database
docker-compose exec db mysql -u camagru -pcamagru camagru

# View logs
docker-compose logs -f [service-name]
```

### Stopping Containers

```bash
docker-compose down
```

## Architecture

- **Web Server**: Nginx 1.27 Alpine
- **Application**: PHP 8.2 FPM Alpine
- **Database**: MariaDB 11
- **Network**: Docker bridge network (camagru_net)
