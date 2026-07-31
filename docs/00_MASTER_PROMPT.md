# 00. Master Prompt - MultiInvestmentAPPS (AIOS)

This document is the master context primer for any AI assistant (Claude, ChatGPT, Copilot, etc.) or developer working on this repository. Read this file first before touching any code.

## Project Identity

- Name: MultiInvestmentAPPS
- Codename: AIOS (AI Investment Operating System)
- Type: Multi-investment, AI-assisted platform
- Stack: Laravel (backend), MySQL/PostgreSQL (database), AI/LLM layer, OneDrive-based knowledge engine

## How To Use This Docs Folder

Read documents in numeric order when onboarding:

1. 01_PRODUCT_VISION.md - why this product exists
2. 02_PRD.md - what we are building
3. 03_SRS.md - detailed functional/non-functional requirements
4. 04_BUSINESS_RULES.md - domain logic and investment rules
5. 05_SYSTEM_ARCHITECTURE.md - how the system is structured
6. 06_DATABASE_DESIGN.md - data model
7. 07_AI_ARCHITECTURE.md - AI/agent layer design
8. 08_ONEDRIVE_KNOWLEDGE_ENGINE.md - knowledge base integration
9. 09_SECURITY.md - security and compliance rules
10. 10_LARAVEL_STANDARD.md - coding standards
11. 11_GITHUB_WORKFLOW.md - git/branching conventions
12. 12_DEPLOYMENT.md - environments and release process
13. 13_PROJECT_ROADMAP.md - milestones and timeline
14. 14_CHANGELOG.md - version history

## Ground Rules For AI Assistants

- Always follow 10_LARAVEL_STANDARD.md for code style and structure.
- Always follow 11_GITHUB_WORKFLOW.md for branching and commit messages.
- Never invent business rules — check 04_BUSINESS_RULES.md, and if a rule is missing, flag it instead of guessing.
- Treat 09_SECURITY.md as non-negotiable (auth, secrets, PII handling).
- Update 14_CHANGELOG.md whenever a notable change ships.
- Keep documents in this folder in sync with the actual codebase; docs are the source of truth for intent, code is the source of truth for behavior.

## Current Status

Sprint 0 - Project foundation. This file and the rest of docs/ are scaffolds to be filled in as the project matures.
