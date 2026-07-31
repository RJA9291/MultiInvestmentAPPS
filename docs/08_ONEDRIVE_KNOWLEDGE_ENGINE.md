# 08. OneDrive Knowledge Engine

## 1. Purpose

Describes how a user's OneDrive documents become part of the AI's grounded knowledge base, so AI insights can reference the user's own notes, statements, and research.

## 2. High-Level Flow

1. User connects their OneDrive account via OAuth and selects a folder to sync.
2. The sync job lists files in that folder and pulls new or changed files (based on file id and modified timestamp).
3. Supported file types (PDF, DOCX, XLSX, TXT, MD) are parsed into plain text.
4. Text is split into chunks and embedded, then stored in the vector store, linked back to a knowledge_documents record.
5. The AI Orchestration Service (see 07_AI_ARCHITECTURE.md) queries this vector store at answer time.

## 3. Sync Behavior

- Sync runs periodically (e.g. every N hours) and can be triggered manually by the user.
- Deleted files in OneDrive should mark the corresponding knowledge_documents record as inactive rather than hard-deleting immediately.
- Sync is one-way: OneDrive to AIOS. The application never writes back to a user's OneDrive.

## 4. Security Considerations

- OAuth tokens are stored encrypted; see 09_SECURITY.md.
- Only the connecting user's own AI queries may retrieve embeddings derived from their documents.
- Users can disconnect OneDrive at any time, which should stop future syncs and optionally purge existing embeddings.

## 5. Failure Handling

- If a file fails to parse, log the failure and skip it rather than failing the whole sync job.
- If OneDrive access is revoked or the token expires, notify the user to reconnect.

## 6. Open Items

- Choose the vector database/embedding provider.
- Define maximum file size and supported file types for v1.
- Define retention policy for embeddings after a user disconnects OneDrive.
