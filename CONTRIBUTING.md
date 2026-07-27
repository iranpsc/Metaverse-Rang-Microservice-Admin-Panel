# Contributing to Metaverse Rang — Microservice Admin Panel

Thank you for your interest in contributing to the **Metaverse Rang Microservice Admin Panel**.

This document describes the development workflow, coding expectations, testing requirements, Pull Request process, and quality standards for contributing to the project.

The goal is to keep the codebase secure, maintainable, testable, and easy to collaborate on.

---

## Table of Contents

* [Code of Conduct](#code-of-conduct)
* [Before You Start](#before-you-start)
* [Ways to Contribute](#ways-to-contribute)
* [Development Environment](#development-environment)
* [Branching Strategy](#branching-strategy)
* [Creating an Issue](#creating-an-issue)
* [Feature Requests](#feature-requests)
* [Coding Standards](#coding-standards)
* [Laravel Guidelines](#laravel-guidelines)
* [Frontend Guidelines](#frontend-guidelines)
* [Testing Requirements](#testing-requirements)
* [Test Coverage](#test-coverage)
* [Database Changes](#database-changes)
* [Microservice and API Changes](#microservice-and-api-changes)
* [Commit Guidelines](#commit-guidelines)
* [Pull Request Process](#pull-request-process)
* [Pull Request Requirements](#pull-request-requirements)
* [CI/CD and Quality Gates](#cicd-and-quality-gates)
* [Code Review](#code-review)
* [Security](#security)
* [Documentation](#documentation)
* [Backward Compatibility](#backward-compatibility)
* [License](#license)

---

## Code of Conduct

All contributors are expected to communicate respectfully and professionally.

We encourage:

* Constructive feedback
* Technical discussion
* Respectful code review
* Collaborative problem solving
* Clear communication

Harassment, discrimination, personal attacks, or intentionally disruptive behavior are not acceptable.

Please also review the repository's [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md).

---

## Before You Start

Before starting work:

1. Check existing Issues.
2. Check open and closed Pull Requests.
3. Search for duplicate or related work.
4. Confirm that your proposed change is within the scope of the project.
5. For significant architectural changes, discuss the approach with maintainers before implementation.

For large changes, opening an Issue before development is strongly recommended.

---

## Ways to Contribute

There are many ways to contribute:

* Report reproducible bugs.
* Propose new features.
* Fix existing issues.
* Add automated tests.
* Improve test coverage.
* Improve application performance.
* Improve accessibility.
* Improve security.
* Improve documentation.
* Refactor code.
* Improve CI/CD workflows.
* Improve developer tooling.

Contributions are not limited to code.

---

## Development Environment

Follow the installation instructions in [`README.md`](README.md).

Before submitting a Pull Request, contributors should be able to:

* Install Composer dependencies.
* Install frontend dependencies.
* Configure the local environment.
* Run the Laravel application.
* Run frontend development tools.
* Run the test suite.
* Build frontend assets successfully.

Typical setup:

```bash
composer install
```

```bash
npm install
```

Configure `.env` and generate the application key:

```bash
php artisan key:generate
```

Run database migrations when required:

```bash
php artisan migrate
```

Start the Laravel application:

```bash
php artisan serve
```

Start Vite:

```bash
npm run dev
```

---

## Branching Strategy

Do not commit directly to protected or main branches.

Create a dedicated branch for each change.

Recommended branch names:

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
feature/user-management
fix/login-validation
test/authentication
refactor/service-integration
docs/update-installation
chore/update-dependencies
```

Keep branches focused on a single logical objective.

---

## Creating an Issue

Before opening an Issue:

1. Search existing Issues.
2. Confirm that the issue is reproducible.
3. Provide enough information for another developer to investigate it.

### Bug Reports

A useful bug report should contain:

* Clear title
* Problem description
* Steps to reproduce
* Expected behavior
* Actual behavior
* Environment details
* Relevant logs
* Screenshots when applicable

Example:

```text
## Description

The admin user list does not update after changing the selected page.

## Steps to Reproduce

1. Open the admin user list.
2. Navigate to another page.
3. Change the page again.
4. Observe the displayed results.

## Expected Behavior

The selected page should display the correct results.

## Actual Behavior

The previous results remain visible.

## Environment

- PHP:
- Laravel:
- Database:
- Browser:
- OS:
```

Never include passwords, API keys, access tokens, or other sensitive information.

---

## Feature Requests

Feature requests should clearly describe:

* The problem being solved.
* Why the feature is needed.
* Expected behavior.
* Proposed user experience.
* Technical considerations, if known.

For major features or architectural changes, discuss the proposal with maintainers before implementation.

---

## Coding Standards

Contributions should follow the conventions already established in the codebase.

General principles:

* Prefer readable code.
* Keep classes focused.
* Keep methods reasonably small.
* Avoid unnecessary duplication.
* Reuse existing components and services where appropriate.
* Validate input.
* Handle errors consistently.
* Avoid unnecessary dependencies.
* Avoid unrelated refactoring.
* Write tests for meaningful behavior changes.
* Keep security in mind.

---

## Laravel Guidelines

When working with Laravel code:

### Controllers

Controllers should remain focused on handling HTTP requests and coordinating application behavior.

Avoid putting large amounts of business logic directly inside controllers.

Prefer appropriate application services, actions, or domain-oriented classes when the existing architecture supports them.

### Models

Use Eloquent relationships and model features consistently with the existing project architecture.

Avoid unnecessary database queries.

Be careful about:

* N+1 queries
* Unnecessary eager loading
* Mass assignment
* Sensitive model attributes

### Validation

Validate incoming data using Laravel's validation mechanisms.

For complex validation rules, use dedicated Form Request classes when appropriate.

### Database

Database changes should be implemented through migrations.

Never manually modify the production database as part of a normal Pull Request workflow.

### Configuration

Configuration values should be managed through environment variables and Laravel configuration files.

Do not hard-code secrets or environment-specific credentials.

---

## Frontend Guidelines

The project uses Vite for frontend asset management.

When modifying frontend code:

* Follow existing project conventions.
* Keep JavaScript maintainable.
* Avoid unnecessary dependencies.
* Ensure assets build successfully.
* Preserve existing UI behavior unless the change intentionally modifies it.
* Consider responsive behavior.
* Consider accessibility.
* Handle loading and error states where applicable.

For significant UI changes, include screenshots or screen recordings in the Pull Request.

---

## Testing Requirements

Testing is a required part of the contribution process for changes that affect application behavior.

Run the project's test suite before opening a Pull Request:

```bash
php artisan test
```

Or:

```bash
vendor/bin/phpunit
```

Tests should be added or updated when changing:

* Authentication
* Authorization
* Validation
* Business logic
* Controllers
* Models
* Database behavior
* API integrations
* Error handling
* Critical administrative workflows

---

## Test Quality

Good tests should be:

* Deterministic
* Readable
* Isolated
* Maintainable
* Focused on behavior

Tests should cover:

* Successful scenarios
* Expected failure scenarios
* Validation errors
* Authorization failures
* Important edge cases
* Regression scenarios

Avoid writing tests that only verify implementation details when behavior-based testing is more appropriate.

---

## Test Coverage

Test coverage is an important quality metric.

However, high coverage alone does not guarantee good tests.

Priority should be given to meaningful coverage of:

* Critical business logic
* Authentication
* Authorization
* Administrative workflows
* API integrations
* Validation
* Error handling
* Important edge cases

New changes should not unnecessarily decrease existing coverage.

When a Pull Request decreases coverage, the reason should be documented in the Pull Request description.

---

## Database Changes

When modifying the database:

* Create a migration.
* Keep migrations focused.
* Use descriptive migration names.
* Consider existing production data.
* Consider rollback behavior.
* Update seeders when appropriate.
* Add or update tests.

Avoid destructive migrations unless the change has been reviewed and the migration strategy is clearly documented.

For potentially breaking database changes, consider a safe migration strategy that allows existing application versions to continue operating during deployment.

---

## Microservice and API Changes

The Admin Panel is part of a broader microservice ecosystem.

When changing API integrations:

* Verify the API contract.
* Validate request data.
* Handle API failures.
* Handle timeouts appropriately.
* Avoid exposing sensitive response data.
* Update tests.
* Document breaking changes.

If an API contract changes, the Pull Request should clearly describe:

```text
- Previous contract
- New contract
- Reason for change
- Affected services
- Required frontend changes
- Required deployment order
- Backward compatibility considerations
```

Breaking API changes should be coordinated with the maintainers of the affected services.

---

## Commit Guidelines

Use clear and descriptive commit messages.

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

Commit types:

| Type       | Description                 |
| ---------- | --------------------------- |
| `feat`     | New feature                 |
| `fix`      | Bug fix                     |
| `test`     | Add or update tests         |
| `refactor` | Code restructuring          |
| `docs`     | Documentation               |
| `style`    | Formatting or style changes |
| `perf`     | Performance improvement     |
| `chore`    | Maintenance                 |
| `ci`       | CI/CD changes               |
| `build`    | Build system changes        |

Keep commits focused and avoid combining unrelated changes.

---

## Pull Request Process

### Step 1 — Create a Branch

```bash
git checkout -b feature/my-change
```

### Step 2 — Implement the Change

Implement the smallest complete solution to the problem.

### Step 3 — Add or Update Tests

Add tests for new behavior and regression tests for bug fixes.

### Step 4 — Run Local Checks

Run the relevant checks:

```bash
php artisan test
```

```bash
npm run build
```

Run additional project-specific checks when applicable.

### Step 5 — Review Your Changes

Before pushing:

```bash
git status
```

Review the complete diff:

```bash
git diff
```

Make sure you have not accidentally included:

* `.env`
* credentials
* debug files
* temporary files
* generated files that should not be committed

### Step 6 — Push Your Branch

```bash
git push origin feature/my-change
```

### Step 7 — Open a Pull Request

Open a Pull Request against the appropriate target branch.

---

## Pull Request Requirements

Every Pull Request should include enough information for reviewers to understand the change.

Recommended structure:

```markdown
## Summary

Briefly explain what changed.

## Motivation

Explain why this change was necessary.

## Changes

- Change 1
- Change 2
- Change 3

## Testing

- [ ] Tests added or updated
- [ ] Tests passed
- [ ] Frontend build passed

## Database Changes

- [ ] No database changes
- [ ] Migration added
- [ ] Seeder updated

## API / Microservice Changes

- [ ] No API changes
- [ ] API contract changed
- [ ] Related services coordinated

## Screenshots

Add screenshots for relevant UI changes.

## Additional Notes

Mention configuration changes, deployment requirements, or known limitations.
```

---

## Pull Request Checklist

Before requesting review:

* [ ] The Pull Request has a clear title.
* [ ] The change solves a specific problem.
* [ ] The change is focused.
* [ ] No unrelated refactoring was included.
* [ ] Relevant tests were added or updated.
* [ ] Existing tests pass.
* [ ] Frontend assets build successfully.
* [ ] Database migrations are included when required.
* [ ] API changes are documented.
* [ ] No secrets were committed.
* [ ] Environment changes are documented.
* [ ] Documentation was updated when necessary.
* [ ] UI changes include screenshots when appropriate.
* [ ] CI checks are passing.

---

## CI/CD and Quality Gates

Pull Requests may be validated automatically through GitHub Actions or other CI systems.

Depending on the configured workflow, automated checks may include:

```text
Install Dependencies
        │
        ▼
Code Quality Checks
        │
        ▼
PHPUnit / Laravel Tests
        │
        ▼
Frontend Build
        │
        ▼
Security Checks
        │
        ▼
Pull Request Review
        │
        ▼
Merge
```

A Pull Request should not be merged while required CI checks are failing.

If a CI failure is unrelated to the submitted change, document the situation in the Pull Request and notify the maintainers.

---

## Code Review

All Pull Requests are subject to code review.

Reviewers may evaluate:

* Correctness
* Maintainability
* Laravel conventions
* Architecture
* Security
* Performance
* Database impact
* API compatibility
* Test quality
* Documentation
* CI status

Review feedback should be addressed before merging.

The goal of code review is to improve the quality and reliability of the project, not simply to approve or reject changes.

---

## Security

Never commit sensitive information.

This includes:

* Passwords
* API keys
* Access tokens
* Private keys
* Database credentials
* Production environment files
* SMTP credentials
* Internal secrets

Do not report security vulnerabilities through public GitHub Issues.

Follow the security reporting process described in [`SECURITY.md`](SECURITY.md).

If a secret is accidentally committed:

1. Do not assume deleting the file is sufficient.
2. Immediately notify the maintainers.
3. Rotate or revoke the exposed credential.
4. Remove the secret from the repository history when appropriate.
5. Review whether the credential was accessed.

---

## Documentation

Documentation contributions are welcome.

You can improve:

* README
* Installation instructions
* Environment configuration
* Laravel documentation
* API integration documentation
* Database documentation
* Testing documentation
* Deployment documentation
* Troubleshooting guides

Documentation should be updated when code changes make existing documentation inaccurate.

---

## Backward Compatibility

When changing:

* APIs
* Database schemas
* Environment variables
* Authentication
* Authorization
* Shared components
* Microservice contracts

consider backward compatibility.

For breaking changes:

1. Document the change.
2. Explain the migration path.
3. Identify affected services.
4. Update tests.
5. Update documentation.
6. Coordinate deployment requirements.

---

## What Makes a Good Contribution?

A strong contribution is:

```text
Focused
   +
Tested
   +
Secure
   +
Documented
   +
Maintainable
   +
Reviewable
```

The goal is not simply to add more code.

The goal is to improve the reliability, security, maintainability, and usability of the Metaverse Rang Admin Panel.

---

## License

By contributing to this repository, you agree that your contributions will be licensed under the same license that governs the project, as specified in the repository's [`LICENSE`](LICENSE) file.

Please review the project's license before submitting substantial contributions.

---

## Thank You

Thank you for contributing to the Metaverse Rang ecosystem.

Every contribution—whether code, tests, documentation, bug reports, security improvements, or constructive feedback—helps make the project better.

We appreciate your time and effort.
