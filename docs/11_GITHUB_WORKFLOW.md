# 11. GitHub Workflow

## 1. Branching Strategy

- main: always deployable, protected branch.
- feature/<name>: one branch per feature or sprint task, branched from main.
- fix/<name>: bug fix branches.
- release/<version>: optional, for release stabilization if needed.

## 2. Commit Message Convention

Use short, descriptive commit messages in imperative mood, e.g. "Add portfolio dashboard endpoint", "Fix investment total calculation". Prefer Conventional Commits style where practical: feat:, fix:, docs:, refactor:, test:, chore:.

## 3. Pull Request Process

1. Open a PR from your feature/fix branch into main.
2. PR description should reference the related task/sprint item and summarize the change.
3. At least one review required before merge (or self-review checklist if solo).
4. All tests must pass before merge.
5. Squash or rebase merge preferred to keep history clean.

## 4. Sprint Workflow (as used in this project)

- Each Sprint 0 style task creates or updates a feature branch (e.g. feature/project-foundation).
- Tasks within a sprint are committed incrementally with clear messages per file/change.
- Once a sprint's tasks are complete, open a PR to merge into main.

## 5. Code Review Checklist

- Does the change follow 10_LARAVEL_STANDARD.md?
- Are new business rules reflected in 04_BUSINESS_RULES.md?
- Are security-sensitive changes reviewed against 09_SECURITY.md?
- Is 14_CHANGELOG.md updated for notable changes?

## 6. Release Tagging

- Tag releases using semantic versioning (vMAJOR.MINOR.PATCH) once the project reaches a stable release cadence.
