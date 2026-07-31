# 13. Project Roadmap

This roadmap tracks major phases. Dates are placeholders until scheduled with stakeholders.

## Sprint 0 - Project Foundation (current)

- Repository setup, branch strategy, folder structure.
- Core documentation (this docs/ folder).
- No application code yet.

## Phase 1 - Core Platform MVP

- Authentication (register, login, password reset).
- Investment CRUD and consolidated dashboard.
- Basic notifications.
- Initial database schema per 06_DATABASE_DESIGN.md.

## Phase 2 - AI Insight Layer

- AI Orchestration Service and prompt templates.
- Basic AI Q&A over the user's own portfolio data (no OneDrive yet).
- Guardrails and disclaimers per 07_AI_ARCHITECTURE.md.

## Phase 3 - OneDrive Knowledge Engine

- OneDrive OAuth connection and folder selection.
- Document parsing, chunking, and embedding pipeline.
- AI answers grounded in synced documents per 08_ONEDRIVE_KNOWLEDGE_ENGINE.md.

## Phase 4 - Hardening and Launch Readiness

- Security review against 09_SECURITY.md.
- Performance and load testing against NFRs in 03_SRS.md.
- Deployment pipeline finalized per 12_DEPLOYMENT.md.

## Phase 5 - Post-Launch Enhancements (candidate ideas)

- Multi-currency support.
- Shared/joint portfolios.
- Deeper risk profiling and personalized recommendations.

## Tracking

Each phase should be broken down into sprints with tasks tracked in the project management tool of choice; update this file when phase scope or order changes, and log notable milestones in 14_CHANGELOG.md.
