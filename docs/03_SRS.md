# 03. Software Requirements Specification (SRS)

## 1. Purpose

This document specifies functional and non-functional requirements for MultiInvestmentAPPS (AIOS), derived from 02_PRD.md.

## 2. Functional Requirements

- FR-1: System shall allow user registration, login, logout, and password reset.
- FR-2: System shall allow a user to create, read, update, and delete investment records.
- FR-3: System shall support multiple investment types (e.g. stocks, unit trust, crypto, property, business equity) per user.
- FR-4: System shall render a consolidated dashboard summarizing all investments by type and total value.
- FR-5: System shall provide an AI chat/insight interface scoped to the logged-in user's data.
- FR-6: System shall allow connecting a OneDrive folder as a knowledge source for AI grounding.
- FR-7: System shall log all AI insight requests and responses for auditability.
- FR-8: System shall notify users of significant portfolio changes (configurable threshold).

## 3. Non-Functional Requirements

- NFR-1 (Performance): Dashboard should load within 2 seconds for a portfolio of up to 500 records.
- NFR-2 (Availability): Target 99.5% uptime for the core web application.
- NFR-3 (Security): All data in transit encrypted via TLS; sensitive fields encrypted at rest. See 09_SECURITY.md.
- NFR-4 (Scalability): Backend should support horizontal scaling of the Laravel application layer.
- NFR-5 (Auditability): All financial data mutations must be logged with user id, timestamp, and change diff.
- NFR-6 (Usability): Core flows (add investment, view dashboard, ask AI) must be completable without external documentation.

## 4. System Constraints

- Backend framework: Laravel (see 10_LARAVEL_STANDARD.md).
- AI layer must not have write access to financial records; it is read-only over user data (see 07_AI_ARCHITECTURE.md).
- No direct trade execution or fund movement (informational platform only).

## 5. Assumptions

- Users have at least one OneDrive account if they wish to use the knowledge engine feature.
- Initial launch targets a single currency display, with multi-currency as a future enhancement.

## 6. Traceability

Each functional requirement above should map to at least one user story in 02_PRD.md and one or more test cases in the testing/ folder.
