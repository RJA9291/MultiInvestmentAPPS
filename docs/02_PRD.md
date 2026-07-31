# 02. Product Requirements Document (PRD)

## Overview

MultiInvestmentAPPS (AIOS) is a web platform that lets a user register multiple investment accounts/asset classes, view a consolidated portfolio, and receive AI-generated insights based on their data and a curated knowledge base.

## Goals

- Let users add and track multiple investment types in one account.
- Provide a consolidated portfolio dashboard.
- Provide AI-generated summaries and insights per portfolio and per asset.
- Ground AI answers in user documents synced via OneDrive (see 08_ONEDRIVE_KNOWLEDGE_ENGINE.md).

## User Personas

- Persona A - Diversified Investor: holds stocks, unit trust, and crypto; wants one dashboard.
- Persona B - Busy Professional: wants quick AI summaries instead of manual analysis.
- Persona C - Small Business Owner: tracks personal and business investments separately but wants combined visibility.

## MVP Feature Scope

1. User authentication (register, login, password reset).
2. Portfolio management: create/edit/delete investment entries (type, amount, date, notes).
3. Dashboard: consolidated view across asset classes with basic charts.
4. AI insight panel: ask questions about your portfolio, get grounded answers.
5. Document sync: connect OneDrive folder as a knowledge source.
6. Basic notification/alerts (e.g. large portfolio change).

## User Stories (sample)

- As a user, I want to add an investment record so that it appears in my dashboard.
- As a user, I want to ask "how is my portfolio doing this month" and get an AI-generated summary.
- As a user, I want to connect my OneDrive folder so the AI can reference my personal notes and statements.
- As a user, I want to see my asset allocation broken down by type.

## Out of Scope (MVP)

- Automated trade execution.
- Real-time market data feeds (may be a later phase).
- Multi-currency support beyond basic display.

## Success Criteria

- A user can go from signup to seeing a populated dashboard in under 10 minutes.
- AI insight responses cite the specific data/document they are grounded in.
