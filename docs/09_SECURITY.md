# 09. Security

## 1. Authentication and Sessions

- Use Laravel's built-in authentication (Sanctum or Breeze/Fortify) for session/token management.
- Passwords hashed with bcrypt/argon2 via Laravel's default hasher; never store plaintext passwords.
- Support password reset via time-limited, single-use tokens sent by email.
- Enforce reasonable session expiry and support logout from all devices.

## 2. Authorization

- Use Laravel Policies/Gates so a user can only access their own investments, insights, and documents.
- The AI layer must apply the same user-scoping as the rest of the application; no cross-user data leakage.

## 3. Data Protection

- All traffic served over HTTPS/TLS.
- Sensitive fields (e.g. OAuth tokens for OneDrive) encrypted at rest using Laravel's encryption facilities.
- Database backups encrypted and access-restricted.

## 4. Secrets Management

- No secrets or API keys committed to the repository; use .env and a secrets manager in production.
- Rotate API keys and OAuth client secrets periodically.

## 5. Audit and Logging

- All financial data mutations logged to audit_logs (see 06_DATABASE_DESIGN.md) with user id, action, and diff.
- AI queries and responses logged to ai_insights for traceability.
- Avoid logging raw sensitive personal data in application logs.

## 6. Compliance

- Follow applicable data protection law (e.g. Malaysia's Personal Data Protection Act) for handling personal and financial data.
- Provide users a way to export or delete their data on request.
- Clearly disclose that the platform does not provide licensed financial advice.

## 7. Third-Party Integrations

- OneDrive OAuth scopes should be the minimum required (read-only on the selected folder).
- LLM provider calls should avoid sending unnecessary personally identifiable information beyond what is needed for the query.

## 8. Incident Response (draft)

- Define a process for revoking compromised credentials/tokens quickly.
- Define a notification process for users in case of a data breach, per applicable law.
