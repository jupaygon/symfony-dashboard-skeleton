# Symfony Skeleton — Hexagonal Architecture

Symfony 8.0 starter template with **hexagonal architecture** (Domain/Application/Infrastructure). Ready-to-use base for clean PHP projects.

**From zero to a clean, well-structured Symfony project in seconds.**

## Requirements

- PHP >= 8.4
- Composer

## Quick Start

```bash
git clone https://github.com/jupaygon/symfony-skeleton.git my-project
cd my-project
rm -rf .git && git init
composer install
```

Create your `.env.local` with your database connection:

```
DATABASE_URL="mysql://root:password@127.0.0.1:3306/my_project?serverVersion=8.0&charset=utf8mb4"
```

Create the database:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

## Architecture

```
src/
├── Application/
│   └── Service/              # Use cases, orchestration
├── Domain/
│   ├── Model/                # Entities, value objects
│   └── Port/                 # Repository interfaces (contracts)
├── Infrastructure/
│   ├── Command/              # Symfony console commands
│   ├── EventSubscriber/      # Event listeners
│   ├── Http/
│   │   ├── Api/              # API controllers (REST, webhooks)
│   │   └── Controller/       # Web controllers
│   ├── Persistence/
│   │   └── Doctrine/         # Repository implementations
│   ├── Security/             # Authentication, authorization
│   └── Service/              # Infrastructure services
└── Kernel.php
```

### Layer rules

- **Domain** has no dependencies on Infrastructure or Application. Pure business logic.
- **Application** orchestrates domain objects. Depends on Domain only (via Port interfaces).
- **Infrastructure** implements the Port interfaces and handles external concerns (HTTP, database, security).

### Example flow

The skeleton includes a `Demo` entity demonstrating the full pattern:

```
Controller → DemoService → DemoRepositoryInterface → DemoRepository (Doctrine)
   (Infra)      (App)            (Domain/Port)            (Infra/Persistence)
```

## Creating new entities

Use the `make` command with the full namespace path:

```bash
php bin/console make:entity '\App\Domain\Model\MyEntity'
```

> The repository will be created in the default Symfony directory. Move it manually to `Infrastructure/Persistence/Doctrine/` and create the corresponding interface in `Domain/Port/`.

## Tests

```bash
./vendor/bin/phpunit
```

## Stack

- **Symfony 8.0** — Latest stable
- **PHP 8.4** — Required minimum
- **Doctrine ORM 3** — Database abstraction
- **PHPUnit 11** — Testing

## License

MIT
