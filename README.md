# MultiInvestmentAPPS (AIOS)

AIOS (AI Investment Operating System) is a multi-investment, AI-assisted platform that gives users one consolidated view of their investments plus AI-generated insights grounded in their own data and documents.

## Status

Sprint 0, project foundation. No application code yet; this repository currently holds the project structure and planning documentation.

## Documentation

Full project documentation lives in the docs folder. Start with docs/00_MASTER_PROMPT.md, which indexes every other document: vision, PRD, SRS, business rules, architecture, database design, AI architecture, OneDrive knowledge engine, security, Laravel standards, GitHub workflow, deployment, and roadmap.

## Project Structure

The repository is organized into the following top-level folders. docs holds product, technical, and process documentation. prompts holds AI prompt templates. architecture holds architecture diagrams and supporting material. database holds database schema, migration references, and ERDs. ai holds AI and agent implementation code. deployment holds deployment scripts and configuration. uiux holds UI/UX assets and design references. workflows holds workflow and automation definitions. testing holds automated tests. scripts holds utility and maintenance scripts. storage holds local storage and artifacts, and is not for committed secrets.

## Tech Stack (planned)

The backend will be built in Laravel (PHP) with MySQL or PostgreSQL as the database. AI features will be powered by an external LLM provider plus a vector database, orchestrated by a dedicated AI service layer, with OneDrive integration as the knowledge source for grounded AI answers. See docs/05_SYSTEM_ARCHITECTURE.md for details.

## Getting Started

Application code has not been scaffolded yet. Once the Laravel application is added, standard setup will involve cloning the repository, copying .env.example to .env and filling in the required values, installing dependencies with Composer, running database migrations, and serving the application locally.

## Contributing

See CONTRIBUTING.md for branching, commit, and PR conventions, and docs/10_LARAVEL_STANDARD.md for coding standards.

## License

See LICENSE.

## Changelog

See CHANGELOG.md for a high-level history, and docs/14_CHANGELOG.md for the detailed project changelog.
