# 04. Business Rules

This document captures domain/business logic that the application must enforce. Any rule not listed here should be treated as undefined — flag it, do not assume it.

## 1. Investment Types (initial supported set)

- Stocks/Equities
- Unit Trust / Mutual Funds
- Cryptocurrency
- Property
- Business Equity / Private Investment
- Fixed Deposit / Cash Savings

Each type has: amount invested, current value (if available), date acquired, notes/attachments.

## 2. Portfolio Calculation Rules

- Total Portfolio Value = sum of current value across all active investment records for a user.
- If current value is not available for a record, fall back to amount invested (cost basis) and flag it as "estimated".
- Gain/Loss % = (current value - amount invested) / amount invested * 100, per record and in aggregate.
- Asset Allocation % = value of a given type / total portfolio value * 100.

## 3. Risk Profile Rules (draft)

- Risk profile is descriptive only (Conservative, Balanced, Aggressive) based on user's declared allocation preferences.
- Risk profile does not restrict what a user can record; it only affects how AI insights are framed.

## 4. Data Integrity Rules

- An investment record must always belong to exactly one user.
- Amount invested must be a positive number; zero or negative values are rejected.
- Deleting an investment record is a soft delete; it must remain in the audit trail.

## 5. AI Insight Rules

- The AI must only reference data belonging to the requesting user.
- Every AI-generated financial statement should indicate whether it is grounded in the user's data/documents or is general knowledge.
- The AI must never state or imply guaranteed returns.

## 6. Compliance Notes

- This platform does not provide licensed financial advice. All AI output must include this disclaimer where relevant.
- Personal data handling must follow applicable data protection law (e.g. Malaysia's PDPA) — see 09_SECURITY.md.

## 7. Open Questions (to be resolved with stakeholders)

- Do we support joint/shared portfolios (e.g. family accounts)?
- Do we need multi-currency conversion, and if so, which FX source?
- What is the exact threshold for "significant portfolio change" notifications?
