# 10. Laravel Coding Standard

## 1. General Style

- Follow PSR-12 coding style for all PHP code.
- Use type hints and return types on all methods where possible.
- Prefer explicit, descriptive names over abbreviations.

## 2. Project Structure Conventions

- Controllers: thin controllers, delegate business logic to Service classes under app/Services.
- Models: app/Models, one model per table, relationships declared explicitly with return types.
- Requests: use Form Request classes (app/Http/Requests) for validation, not inline validation in controllers.
- Policies: app/Policies for authorization logic, registered in AuthServiceProvider.
- Jobs/Queues: app/Jobs for background work (e.g. OneDrive sync, embedding generation).
- Services: app/Services for business logic (e.g. PortfolioService, AiInsightService).

## 3. Database and Migrations

- One migration per logical change; never edit an already-released migration, create a new one.
- Use descriptive migration names, e.g. create_investments_table, add_risk_profile_to_users_table.
- Always define foreign key constraints and indexes explicitly (see 06_DATABASE_DESIGN.md).

## 4. Testing

- Use Pest or PHPUnit (project default: Pest) under the testing/ folder structure.
- Every new Service class should have a corresponding feature or unit test.
- Critical business rules from 04_BUSINESS_RULES.md must have explicit test coverage.

## 5. API Conventions

- Use API Resources (app/Http/Resources) to shape JSON responses; never return raw Eloquent models.
- Consistent response envelope: data, message, and errors keys.
- Version the API under /api/v1 to allow future breaking changes.

## 6. Error Handling

- Use custom Exception classes for domain errors (e.g. InsufficientDataException) rather than generic exceptions.
- Log unexpected exceptions with context (user id, request id) but never log secrets or full request bodies containing sensitive data.

## 7. Commit Hygiene

- Run `php artisan test` and static analysis (e.g. PHPStan/Larastan if configured) before committing.
- Keep commits scoped to one logical change; see 11_GITHUB_WORKFLOW.md for commit message conventions.
