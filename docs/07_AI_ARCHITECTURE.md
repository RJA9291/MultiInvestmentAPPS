# 07. AI Architecture

## 1. Purpose

Describes how the AI/agent layer is designed: what it can access, how it retrieves context, and how it must behave.

## 2. Design Principles

- Read-only over financial data: the AI never writes to investments, users, or any transactional table directly.
- Grounded answers: every response should be traceable to either the user's own data or an explicitly cited knowledge document.
- No guaranteed returns language: the AI must avoid promissory financial claims.
- User-scoped context: the AI only ever sees data belonging to the requesting user.

## 3. Components

- AI Orchestration Service: receives a user query, assembles context, calls the LLM provider, and returns a structured response.
- Retrieval Layer (RAG): given a query, fetches the most relevant chunks from the vector store (populated by 08_ONEDRIVE_KNOWLEDGE_ENGINE.md) plus a snapshot of the user's portfolio data.
- Prompt Templates: versioned prompt templates per use case (portfolio summary, single-asset analysis, general Q&A).
- LLM Provider: external API (provider to be selected); should be swappable behind an interface.
- Response Post-Processor: checks output for disclaimers, strips any unsupported financial guarantees, and attaches source citations.

## 4. Data Flow

1. User submits a question through the AI Insight Module.
2. Orchestration Service pulls the user's current portfolio snapshot from the database.
3. Retrieval Layer fetches relevant knowledge chunks from the vector store.
4. Both are inserted into the appropriate prompt template and sent to the LLM.
5. The LLM response is post-processed (disclaimers, citations) and returned to the user.
6. The full interaction (query, response, sources) is logged to ai_insights for auditability.

## 5. Guardrails

- Reject or flag queries that ask the AI to execute a transaction or move funds.
- Reject queries asking for advice that would require a licensed financial advisor, and surface the standard disclaimer instead.
- Rate-limit AI queries per user to control cost and abuse.

## 6. Open Items

- Select LLM provider and vector database.
- Define prompt versioning and evaluation process.
- Define fallback behavior when the LLM provider is unavailable.
