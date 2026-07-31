# 05. System Architecture

## 1. High-Level Overview

MultiInvestmentAPPS (AIOS) follows a layered architecture:

1. Client Layer - web frontend (Blade/Livewire or SPA, TBD) consuming the backend API.
2. Application Layer - Laravel backend: controllers, services, jobs, policies.
3. AI Layer - orchestration service that talks to an LLM provider, retrieves context via RAG, and returns grounded answers (see 07_AI_ARCHITECTURE.md).
4. Knowledge Layer - OneDrive sync plus document indexing/embeddings store (see 08_ONEDRIVE_KNOWLEDGE_ENGINE.md).
5. Data Layer - relational database (MySQL/PostgreSQL) for transactional data; vector store for embeddings.
6. Infrastructure Layer - hosting, queues, cache, storage (see 12_DEPLOYMENT.md).

## 2. Request Flow (summary)

Client sends a request to the Laravel API. The application layer validates and processes it against the database. For AI insight requests, the application layer forwards the query to the AI Orchestration Service, which retrieves relevant context from the vector store (populated by the OneDrive Knowledge Engine) and calls the LLM provider, then returns a grounded response back through the API to the client. All financial data mutations are also written to an audit/log store.

## 3. Core Modules

- Auth Module: registration, login, sessions/tokens.
- Portfolio Module: CRUD for investment records, aggregation logic.
- Dashboard Module: read-optimized views/aggregates for the UI.
- AI Insight Module: request/response handling, prompt templates, grounding.
- Knowledge Sync Module: OneDrive connector, document parsing, embedding generation.
- Notification Module: alerts on portfolio thresholds.

## 4. Tech Stack (initial)

- Backend: Laravel (PHP)
- Database: MySQL or PostgreSQL
- Queue: Laravel Queues (Redis/Database driver)
- AI: External LLM API (provider TBD) plus vector database (TBD)
- Frontend: Blade/Livewire or a JS framework (TBD)
- Hosting: TBD, see 12_DEPLOYMENT.md

## 5. Key Architectural Decisions

- The AI layer is strictly read-only against financial data; all writes go through the normal application layer with validation.
- OneDrive sync is a one-way ingestion pipeline into the knowledge store; the app never writes back to a user's OneDrive.
- All architectural decisions that change this document should be logged in 14_CHANGELOG.md.
