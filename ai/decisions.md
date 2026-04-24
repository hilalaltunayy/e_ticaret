# Decisions

This document records the key technical, architectural, and product decisions made during the project.

Goals:
- avoid repeating past problems
- document why certain architectural choices were made
- ensure these decisions are preserved consciously in future changes

Decisions listed here are not meant to be re-discussed by default.
Any change must be deliberate and justified.

---

# 1. Controllers Must Stay Thin, Services Must Hold Logic

Decision:
- business logic must not live inside controllers
- controllers act only as request/response orchestrators
- business rules live in the service layer

Reason:
- controller bloat caused serious maintenance issues
- readability and scalability decreased
- refactoring cost became very high

Result:
- service-heavy architecture is mandatory
- controllers must not grow again

---

# 2. DTO Layer Is Required

Decision:
- raw request data must not be passed directly to services
- DTOs must be used between controller and service

Reason:
- unstructured data flow increases error risk
- validation must be centralized
- service layer must receive controlled data

Result:
- DTO usage is preferred for all important use cases

---

# 3. RBAC and Permission System Must Stay Strict

Decision:
- role + permission system must not be relaxed
- deny-by-default must be preserved
- secretary must never behave like an admin

Reason:
- permission leaks are critical risks
- UI hiding is not security
- system-level enforcement is required

Result:
- route + filter + service guard must be used together

---

# 4. Additive Migration Strategy Must Be Used

Decision:
- existing migrations must not be modified backward
- schema changes must be additive

Reason:
- migration mistakes can cause data loss
- backward edits create inconsistency

Result:
- new requirement = new migration
- extend instead of destructive change

---

# 5. UTF-8 Without BOM Is Mandatory

Decision:
- all files must use UTF-8 without BOM

Reason:
- encoding issues caused major time loss
- BOM can break PHP execution

Result:
- encoding is not optional
- this rule must not be changed

---

# 6. Builder-Based Structure Is Preferred

Decision:
- page builder and dashboard builder are used
- static-only approach is not preferred

Reason:
- admin flexibility is required
- UI must be manageable
- content control must be dynamic

Trade-off:
- more complexity
- more flexibility

Result:
- builder system must be preserved

---

# 7. Digital Content Must Not Be Directly Downloadable

Decision:
- digital books must not be exposed as direct downloads
- access must go through a reader

Reason:
- direct download leads to uncontrolled distribution
- access control and traceability are lost

Result:
- token-based access is required
- watermark and logging are used
- goal is deterrence, not full DRM

---

# 8. AI Integration Is Deferred from V1

Decision:
- AI is not part of Version 1
- it is planned as a later phase

Reason:
- core system is not fully completed
- early AI integration increases complexity
- working system first, smart system later

Result:
- AI is planned for Phase 2

---

# 9. Iterative AI-Assisted Development Is Adopted

Decision:
- features are built in small sprints
- AI tools (ChatGPT / Codex) are actively used

Reason:
- faster iteration
- controlled development
- continuous validation

Result:
- no large one-shot development
- iterative approach continues

---

# 10. Working System First, Optimization Later

Decision:
- first make the system work
- then improve it

Reason:
- over-optimization slows development
- no value in optimizing non-working systems

Result:
- “working” is prioritized over “perfect”

---

# 11. Admin Panel Must Be Preserved

Decision:
- admin panel must not be rewritten unnecessarily

Reason:
- it has already gone through many iterations
- it is relatively stable

Result:
- user development must not break admin side

---

# 12. Service Layer Must Not Become Bloated

Decision:
- service layer is primary, but must stay controlled
- splitting is allowed when necessary

Reason:
- controller bloat must not shift into services

Result:
- ~700–800 lines is a soft limit
- repeated logic must be reused, not duplicated

---

# 13. System Must Remain Modular and Extendable

Decision:
- system must not be treated as a rigid monolith
- it must stay modular and extendable

Reason:
- future additions like AI and analytics are expected

Result:
- architecture remains simple but expandable
