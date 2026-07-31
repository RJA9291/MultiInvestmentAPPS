# 12. Deployment

## 1. Environments

- local: developer machines, using Laravel Sail/Docker or Valet.
- staging: pre-production environment mirroring production configuration, used for QA.
- production: live environment serving real users.

## 2. Deployment Pipeline (target)

1. Developer opens a PR (see 11_GITHUB_WORKFLOW.md).
2. CI runs automated tests and static analysis on the PR.
3. On merge to main, CI runs the full test suite again.
4. A deployment step pushes the build to staging automatically.
5. Production deployment is triggered manually (or automatically after staging sign-off, TBD).

## 3. Hosting (to be finalized)

- Candidate options: traditional VPS, managed PaaS (e.g. Laravel Forge + a cloud provider), or containerized deployment (Docker + a container platform).
- Database hosted separately from the application server, with automated backups.
- Queue workers run as a separate long-running process from the web server.

## 4. Configuration and Secrets

- Environment-specific configuration via .env files, never committed to the repository.
- Secrets (API keys, OAuth credentials, database passwords) managed via the hosting provider's secret manager or environment variables.

## 5. Migrations and Releases

- Database migrations run automatically as part of the deployment step, before the new application code goes live.
- Use maintenance mode for any migration that requires downtime.

## 6. Monitoring and Rollback

- Application errors and performance monitored via a logging/monitoring service (provider TBD).
- Keep the previous release deployable so a rollback can happen quickly if a deployment introduces a critical issue.

## 7. Open Items

- Select hosting provider and CI/CD tooling.
- Define staging-to-production promotion criteria.
- Define monitoring/alerting thresholds.
