# Metaverse Rang — Microservice Admin Panel

Administrative panel for the **Metaverse Rang Microservice ecosystem**, built with Laravel and Vite.

This application provides a centralized administration interface for managing administrative operations and interacting with the services that form the Metaverse Rang platform.

---

## Table of Contents

* [Overview](#overview)
* [Features](#features)
* [Technology Stack](#technology-stack)
* [Requirements](#requirements)
* [Installation](#installation)
* [Environment Configuration](#environment-configuration)
* [Database Setup](#database-setup)
* [Running the Application](#running-the-application)
* [Frontend Assets](#frontend-assets)
* [Testing](#testing)
* [Code Quality](#code-quality)
* [Project Structure](#project-structure)
* [Development Workflow](#development-workflow)
* [Contributing](#contributing)
* [Security](#security)
* [License](#license)

---

## Overview

**Metaverse Rang Microservice Admin Panel** is an administrative application developed as part of the Metaverse Rang platform.

The project is designed to provide an administration layer for the platform and its microservice-based ecosystem.

The Admin Panel is responsible for providing an administrative interface where authorized users can perform management and operational tasks through a centralized web application.

The application is built with **Laravel** and uses **Vite** for frontend asset development and bundling.

---

## Features

The Admin Panel is designed to support administrative workflows across the Metaverse Rang ecosystem.

Depending on the enabled modules and connected services, the application may provide functionality such as:

* Administrative dashboard
* Authentication and access control
* User and account management
* Resource management
* Data management
* CRUD operations
* Form validation
* Search and filtering
* Pagination
* Administrative workflows
* Integration with backend services
* Database-driven functionality
* Localization support
* Responsive administrative interface
* Automated testing
* Production-ready asset building

> The exact functionality available in the application is determined by the current implementation and enabled modules.

---

## Technology Stack

### Backend

* PHP
* Laravel
* Laravel Routing
* Laravel Middleware
* Laravel Controllers
* Laravel Models
* Laravel Migrations
* Laravel Validation
* Laravel Configuration

### Frontend

* Blade templates
* Vite
* JavaScript
* CSS
* Frontend assets managed through the Vite build system

### Database

The application uses Laravel's database layer and migration system.

The exact database engine is configured through the application's environment variables.

### Testing

* PHPUnit
* Laravel testing utilities

### Development Tools

* Composer
* Node.js
* npm
* Vite
* Git
* GitHub

---

## Requirements

Before installing the project, make sure the following tools are available on your development machine.

* PHP
* Composer
* Node.js
* npm
* A supported database
* Git

The exact PHP version and package requirements are defined by the project's `composer.json`.

The frontend Node.js requirements and available scripts are defined by `package.json`.

You can check the installed versions with:

```bash
php -v
```

```bash
composer -V
```

```bash
node -v
```

```bash
npm -v
```

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/iranpsc/Metaverse-Rang-Microservice-Admin-Panel.git
```

Move into the project directory:

```bash
cd Metaverse-Rang-Microservice-Admin-Panel
```

---

### 2. Install PHP Dependencies

Install Laravel and PHP dependencies using Composer:

```bash
composer install
```

For production environments, use:

```bash
composer install --no-dev --optimize-autoloader
```

---

### 3. Install Frontend Dependencies

Install Node.js dependencies:

```bash
npm install
```

---

## Environment Configuration

Create your local environment configuration from the example environment file if one is provided by the repository:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Configure the required environment variables in `.env`.

Typical Laravel configuration includes:

```env
APP_NAME="Metaverse Rang Admin Panel"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
```

Database configuration:

```env
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Additional environment variables may be required depending on the services and integrations used by the application.

> **Never commit your `.env` file to the repository.**

Do not commit:

* Passwords
* API keys
* Access tokens
* Private keys
* Database credentials
* Production credentials
* SMTP credentials
* Other sensitive configuration

---

## Application Key

Generate the Laravel application key:

```bash
php artisan key:generate
```

This command updates the `APP_KEY` value in your `.env` file.

---

## Database Setup

Configure your database connection in `.env`.

After configuring the database, run Laravel migrations:

```bash
php artisan migrate
```

If the project provides seeders and you need to populate the database with development data:

```bash
php artisan db:seed
```

Or:

```bash
php artisan migrate --seed
```

> Only run database reset commands such as `migrate:fresh` in development or test environments where data loss is acceptable.

---

## Storage Configuration

If the application uses Laravel's public storage disk, create the required symbolic link:

```bash
php artisan storage:link
```

---

## Running the Application

### Laravel Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at the local address displayed by Laravel.

---

## Frontend Assets

The project uses Vite for frontend asset development and bundling.

Start the Vite development server:

```bash
npm run dev
```

For production assets:

```bash
npm run build
```

During development, you may need to run both Laravel and Vite:

```text
Laravel Application
        │
        ▼
php artisan serve

        +

Vite Development Server
        │
        ▼
npm run dev
```

For production deployments, build the frontend assets before starting the application according to the project's deployment process.

---

## Common Commands

### Laravel

Run the development server:

```bash
php artisan serve
```

Clear application cache:

```bash
php artisan cache:clear
```

Clear configuration cache:

```bash
php artisan config:clear
```

Clear route cache:

```bash
php artisan route:clear
```

Clear compiled application files:

```bash
php artisan clear-compiled
```

Run migrations:

```bash
php artisan migrate
```

Create a migration:

```bash
php artisan make:migration create_example_table
```

Create a model:

```bash
php artisan make:model Example
```

Create a controller:

```bash
php artisan make:controller ExampleController
```

---

### Frontend

Install dependencies:

```bash
npm install
```

Start development mode:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

The exact scripts available in the project are defined in `package.json`.

---

## Testing

Testing is an important part of the project's development and quality assurance process.

The project uses PHPUnit and Laravel's testing capabilities.

Run the test suite with:

```bash
php artisan test
```

Or directly with PHPUnit:

```bash
vendor/bin/phpunit
```

Run a specific test file:

```bash
php artisan test tests/Feature/ExampleTest.php
```

Run a specific test by name:

```bash
php artisan test --filter=ExampleTest
```

Before opening a Pull Request, contributors should ensure that:

* Existing tests pass.
* New functionality has appropriate tests.
* Bug fixes include regression tests where applicable.
* Tests are deterministic.
* Tests do not depend on local production data.

---

## Testing Principles

Tests should focus on application behavior rather than implementation details.

Important areas to test include:

* Authentication
* Authorization
* Validation
* Business logic
* Controllers
* Database interactions
* API integrations
* Error handling
* Edge cases
* Regression scenarios

When changing existing functionality, update affected tests accordingly.

When adding new functionality, include tests that demonstrate both expected behavior and important failure scenarios.

---

## Code Quality

All contributions should maintain the quality and consistency of the existing codebase.

Contributors should:

* Follow Laravel conventions.
* Keep controllers focused.
* Avoid unnecessary business logic inside controllers.
* Reuse existing services and utilities where appropriate.
* Validate user input.
* Handle errors consistently.
* Avoid duplicated logic.
* Keep database queries efficient.
* Avoid unnecessary dependencies.
* Write maintainable and readable code.
* Add tests for meaningful behavior changes.

---

## Project Structure

The project follows a Laravel application structure.

The main directories include:

```text
.
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── lang/
│
├── public/
│
├── resources/
│
├── routes/
│
├── storage/
│
├── tests/
│
├── .github/
│
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── CONTRIBUTING.md
├── LICENSE
├── SECURITY.md
└── README.md
```

The exact structure may evolve as the project grows.

---

## Microservice Integration

The Admin Panel is part of the broader Metaverse Rang microservice ecosystem.

Depending on the implemented modules, the application may communicate with backend services through configured APIs or service integrations.

When modifying service integrations, contributors should consider:

* API contracts
* Authentication
* Authorization
* Request validation
* Response handling
* Error handling
* Timeouts
* Retry behavior
* Backward compatibility
* Logging
* Security

Changes that affect communication with backend microservices should be documented in the Pull Request.

Breaking changes to service contracts should be coordinated with the maintainers of the affected services.

---

## Development Workflow

The recommended workflow is:

```text
Issue / Task
     │
     ▼
Create Branch
     │
     ▼
Implement Change
     │
     ▼
Add / Update Tests
     │
     ▼
Run Quality Checks
     │
     ▼
Commit Changes
     │
     ▼
Push Branch
     │
     ▼
Open Pull Request
     │
     ▼
CI Validation
     │
     ▼
Code Review
     │
     ▼
Approval
     │
     ▼
Merge
```

All contributors should read [`CONTRIBUTING.md`](CONTRIBUTING.md) before submitting changes.

---

## Branch Naming

Use descriptive branch names.

Recommended naming conventions:

```text
feature/<description>
fix/<description>
refactor/<description>
test/<description>
docs/<description>
chore/<description>
hotfix/<description>
```

Examples:

```text
feature/admin-user-management
fix/authentication-error
test/user-management
refactor/service-layer
docs/update-readme
chore/update-dependencies
```

Keep branches focused on one logical change.

---

## Commit Messages

Use clear and meaningful commit messages.

Recommended format:

```text
type(scope): short description
```

Examples:

```text
feat(users): add user management
fix(auth): handle expired session
test(users): add user management tests
refactor(api): improve service integration
docs(readme): update installation guide
chore(ci): update test workflow
```

Recommended commit types:

* `feat` — New functionality
* `fix` — Bug fix
* `test` — Tests
* `refactor` — Code restructuring
* `docs` — Documentation
* `style` — Formatting or style-only changes
* `perf` — Performance improvement
* `chore` — Maintenance
* `ci` — CI/CD changes
* `build` — Build system changes

---

## Pull Requests

All code changes should be submitted through Pull Requests.

A Pull Request should:

* Have a clear title.
* Explain what was changed.
* Explain why the change was necessary.
* Reference the related Issue or task when applicable.
* Include appropriate tests.
* Pass required CI checks.
* Avoid unrelated changes.
* Include screenshots for significant UI changes.
* Document configuration changes.
* Document API or microservice contract changes.

### Pull Request Checklist

Before requesting review:

* [ ] The change follows project conventions.
* [ ] Relevant tests were added or updated.
* [ ] Existing tests pass.
* [ ] The application runs successfully.
* [ ] Frontend assets build successfully.
* [ ] No secrets were committed.
* [ ] Environment changes are documented.
* [ ] Documentation was updated when necessary.
* [ ] The Pull Request is focused on one logical change.
* [ ] CI checks are passing.

---

## Security

Security is a critical part of the project.

Never commit sensitive information to the repository.

This includes:

* Passwords
* API keys
* Access tokens
* Private keys
* Database credentials
* Production environment files
* SMTP credentials

Security vulnerabilities should not be reported through public GitHub Issues.

Please follow the security reporting process described in [`SECURITY.md`](SECURITY.md).

---

## Contributing

Contributions are welcome.

Before contributing, please read:

👉 [`CONTRIBUTING.md`](CONTRIBUTING.md)

All contributions are subject to code review and automated quality checks.

---

## License

This project is distributed under the license specified in [`LICENSE`](LICENSE).

Please review the license file before using, modifying, or redistributing the project.

---

## Maintainers

Maintained as part of the **Metaverse Rang** project ecosystem.

Repository:

https://github.com/iranpsc/Metaverse-Rang-Microservice-Admin-Panel

---

## Acknowledgements

Thank you to everyone who contributes to the Metaverse Rang ecosystem through code, testing, documentation, issue reports, and technical feedback.
