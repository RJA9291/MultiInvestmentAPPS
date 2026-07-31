# 06. Database Design

## 1. Overview

This document describes the core data model. Field lists are indicative for Sprint 0 and will be refined as migrations are written (see 10_LARAVEL_STANDARD.md for migration conventions).

## 2. Core Entities

### users
id, name, email, password_hash, risk_profile, created_at, updated_at, deleted_at.

### investments
id, user_id (FK to users), type (enum: stock, unit_trust, crypto, property, business_equity, fixed_deposit), name, amount_invested, current_value, currency, acquired_at, notes, created_at, updated_at, deleted_at.

### investment_valuations
id, investment_id (FK to investments), value, recorded_at, source (manual, api, ai_estimate). Tracks value history over time for charts.

### ai_insights
id, user_id (FK to users), query_text, response_text, grounded_sources (json), created_at.

### knowledge_documents
id, user_id (FK to users), source (onedrive), external_id, title, content_hash, indexed_at, created_at, updated_at.

### knowledge_embeddings
id, knowledge_document_id (FK to knowledge_documents), chunk_index, embedding_vector, created_at.

### notifications
id, user_id (FK to users), type, payload (json), read_at, created_at.

### audit_logs
id, user_id (FK to users), action, subject_type, subject_id, changes (json), created_at.

## 3. Relationships

- A user has many investments.
- An investment has many investment_valuations.
- A user has many ai_insights.
- A user has many knowledge_documents.
- A knowledge_document has many knowledge_embeddings.
- A user has many notifications and audit_logs.

## 4. Indexing Notes

- Index investments on (user_id, type).
- Index investment_valuations on (investment_id, recorded_at).
- Index audit_logs on (user_id, created_at).

## 5. Data Retention

- Soft deletes on investments and users to preserve audit history.
- audit_logs and ai_insights should be retained per the policy defined in 09_SECURITY.md.
