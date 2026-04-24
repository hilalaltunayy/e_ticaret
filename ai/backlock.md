# Backlog

This document defines the upcoming work, priority order, release logic, and future phases of the project.

Goals:
- clarify what should be built first
- protect the v1 scope
- separate post-v1 improvements into later phases
- keep architectural discipline while accelerating development
- introduce AI at the right time

This is not just an idea list. It is a controlled product roadmap.

---

# 1. Backlog Structure

The backlog should be understood in layers:

- Now: items that should be actively built now
- Next: work that should come immediately after
- Later: near-future work after v1
- Future / Phase 2: more advanced future-version work
- Research Needed: areas that require research or prototyping before implementation

This prevents all ideas from being mixed into a single flat list.

---

# 2. Main Product Goal

The main goal is to:
- build a working core system for all three roles
- complete the core storefront flows on the user side
- bring the admin and secretary sides to a solid operational level
- reach a strong, usable, nearly complete v1 state
- move into an AI-supported second phase afterward

This backlog prioritizes completing the core product first, then adding intelligent enhancements.

---

# 3. Current Main Priority: V1 Core Completion

The highest priority right now is completing the Version 1 core flows.

The goal of v1 is to:
- make the essential user flows operational
- complete the core experience for all three roles
- stabilize the sales, viewing, ordering, and management loop
- then move into more advanced areas

For that reason, the first priority in this backlog is the user side and the remaining critical operational screens.

---

# 4. NOW – Items That Must Be Actively Built

## 4.1 User Frontend Core Flows

This is the most critical active area.

User-side areas that need completion:
- user registration / login / account flows
- strengthening the product list side
- completing the product detail side
- favorites behavior and favorites screen
- cart flow
- checkout flow
- payment screen and payment initiation logic
- my account screen
- my orders screen
- my reviews area
- product questions / seller questions areas
- digital books area
- rating / stars for purchased products

Goal:
Complete the core shopping and account experience for the user role.

---

## 4.2 Storefront Quality and Consistency

The user side should not only work, it should also feel consistent.

Needed work:
- visual and behavioral consistency across storefront screens
- mobile compatibility checks
- clarifying the full user experience chain across product list / product detail / cart / checkout
- controlled behavior of recommendation surfaces
- improving same-category recommendation areas
- thinking through empty states / loading / fallback states where needed

---

## 4.3 Core User Domain Completion

Alongside the frontend, the domain backbone must also be completed.

Core areas that need completion:
- favorites
- reviews
- rating
- orders
- cart consistency
- ownership rules
- digital purchase access
- account-linked user actions

Goal:
Complete not only the screens, but also the business rules behind them.

---

# 5. NEXT – Work That Will Make V1 Strong

## 5.1 Remaining Critical Admin Management Screens

After the user side becomes stable enough, the remaining critical admin screens should be completed.

Priority admin screens:
- customer review management
- customer questions / product questions management
- traffic analysis screen
- payments and refunds management screen
- log records screen
- missing notification / operational monitoring screens
- remaining reporting screens where needed

Goal:
Bring the admin panel to a sufficient operational level for v1.

---

## 5.2 Secretary Operational Completion

The secretary side should also be completed as a permission-controlled management surface.

Priorities:
- verifying admin-opened management areas in secretary view
- clarifying review moderation flows
- completing controlled secretary access in areas such as orders / shipments
- reviewing critical secretary actions from an audit perspective

---

## 5.3 Payment and Refund Baseline

The payment side should at least reach a safe and simple baseline flow for v1.

Needed work:
- clarify the payment screen flow
- define a mock / basic provider integration approach
- establish the payment attempt structure
- design callback / webhook logic
- define the basic screen and data model for refund flows

Note:
Payment integration is also a research-heavy area at the moment, so architectural and technical decisions are required before implementation.

---

## 5.4 Digital Reading Baseline

For digital books in v1, the goal is not full DRM but a secure baseline access approach.

Needed work:
- digital books screen
- ownership checks
- token-based access
- reader route
- basic canvas/render logic
- initial watermark approach
- access log backbone
- initial revoke / expire thinking

Goal:
For the first version, target controlled access, deterrence, and traceability rather than absolute protection.

---

# 6. LATER – Near-Future Improvements After V1

These can be addressed shortly after v1:

- more advanced checkout experience
- stronger shipment tracking screens
- stock alerts and operational dashboard improvements
- product search quality improvements
- more advanced filtering and sorting
- richer user panel surfaces
- more advanced campaign / coupon behavior
- stronger moderation tools
- improved digital reading experience
- stronger analytics surfaces

---

# 7. FUTURE / PHASE 2 – AI Integration

AI integration is the targeted second major phase of the project.

Important principle:
- AI should not become central before the v1 core is stable
- the working system should be completed first
- then AI should be added as an intelligence layer on top of that system

The AI phase can initially include the following areas:

## 7.1 Admin AI Assistant

An AI-supported recommendation and insight system for the admin panel.

Possible use cases:
- sales interpretation on dashboards
- category-based increase / decline comments
- weekly / monthly summaries
- insights such as "this category is trending upward"
- homepage / banner / campaign suggestions
- pricing or visibility suggestion notes
- decision-support cards on specific management screens

Goal:
Allow the admin not only to see data, but also to receive meaningful action suggestions from it.

---

## 7.2 AI-Supported Content and Merchandising Suggestions

Possible areas:
- homepage content suggestions
- campaign text suggestions
- banner suggestions
- product featuring suggestions
- category-based merchandising suggestions

---

## 7.3 User-Facing AI Discovery Features

Possible future AI areas on the user side:

- keyword / theme / style-based book discovery
- content-based search beyond only book title
- surfaces such as "key concepts highlighted in this book"
- semantic search
- AI-supported recommendation layer based on user query
- smarter search backed later by semantic indexing or search_index-like structures

Note:
This is a strong area, but it requires research. It should not become the primary priority before v1 is completed.

---

# 8. RESEARCH NEEDED

Some areas should not go directly into implementation before research or prototyping.

## 8.1 Payment Integration Research

Things to research:
- which payment provider should be used
- how mock-to-real provider transition should work
- how callback / webhook flow should be designed
- how refund operations should be modeled
- how idempotency should be guaranteed

---

## 8.2 Digital Reader Research

Things to research:
- PDF.js / EPUB approach
- limits of canvas-based rendering
- token refresh / short-lived token approach
- watermark strategies
- device / session limits
- logging and revoke behavior
- the balance between security and usability

---

## 8.3 Large Catalog / Content Ingestion Research

Since larger datasets such as 200-300 books are expected in the future, the following should be researched:
- bulk book ingestion
- media management
- cover, description, and metadata import
- search indexing structure
- performance and pagination
- data quality control

---

## 8.4 AI Search / Semantic Discovery Research

Things to research:
- content-based and keyword-based search
- semantic search infrastructure
- indexing strategy
- extracting key concepts from book content
- matching user query with book metadata
- AI cost / token efficiency / caching strategies

---

# 9. Release Logic

## Version 1 (Primary Goal)

V1 should be considered complete when:
- core shopping and account flows on the user side are working
- critical admin management screens reach a sufficient level
- the secretary side works under permission control
- payment and digital access have at least a baseline working approach
- the project overall reaches a usable first-version state

## Version 1.1 / 1.x

Post-v1 improvement releases:
- remaining operational screens
- better analytics
- UX improvements
- digital reading experience improvements
- search and filtering quality improvements

## Version 2

Primary focus of Version 2:
- AI integration
- smart recommendations
- semantic discovery
- stronger admin decision support
- more advanced automations

---

# 10. Priority Summary

Short priority order:

1. Complete the user storefront core
2. Finish cart / checkout / auth / account / favorites / digital books flows
3. Complete product detail and related user interactions
4. Build a baseline working solution for payment and digital access
5. Complete the remaining critical admin management screens
6. Validate the secretary view and permission-controlled modules
7. Consolidate and stabilize v1
8. Then move into the AI-supported second phase

---

# 11. Backlog Management Principle

This backlog is not static.
But not every new idea should immediately become an active priority.

Rule:
- do not allow scattered expansion to break v1 focus
- do not force research-heavy areas into immediate implementation
- do not add AI features too early and risk the core product flows
- finish the working core product first
- then add intelligent and advanced features

This backlog should be updated regularly, but priority discipline must be preserved.
