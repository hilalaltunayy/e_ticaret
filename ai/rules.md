# Rules

This file defines the mandatory working rules for AI tools, Codex, agents, and manual development in this project.

Goals:
- protect the existing working structure
- prevent unnecessary breakage
- reduce time-wasting issues such as bad refactors, encoding corruption, migration risks, and permission leaks
- keep changes controlled and traceable

---

# 1. General Working Principle

- No change should be implemented as an unnecessary large-scale intervention.
- Every task must be completed through the smallest possible controlled and traceable changes.
- Existing working areas must not be rewritten without a clear reason.
- Architectural changes must not be introduced just because they look cleaner or more elegant.
- No hidden or surprise changes are allowed outside the requested scope.
- Unless explicitly requested by the user, no file should be deleted, moved, renamed, no route should be removed, no table name should be changed, and no existing behavior should be broken.
- Only the minimum required files should be modified in each task.

---

# 2. Things That Must Not Be Done Without User Approval

The following actions must not be done unless the user explicitly requests or approves them:

- deleting files
- moving or renaming files
- removing existing routes or changing their names
- arbitrarily renaming existing controller / service / model classes
- deleting existing database columns
- modifying existing migrations in a backward-breaking way
- rewriting a working module from scratch
- large-scale refactoring
- changing the permission system
- breaking the publish/draft/version structure
- redesigning working admin screens
- unwanted UI redesign
- breaking the existing builder logic
- changing the session/auth flow without a strong reason

Default rule:
- prefer extension over deletion
- prefer safe addition over breaking change
- prefer task-focused fixes over refactoring

---

# 3. Refactor Rules

Uncontrolled refactoring is forbidden in this project.

- Refactoring should only be done when truly necessary.
- If refactoring is not required by the task, it must not be done.
- "I was already in the file, so I cleaned other parts too" is not acceptable.
- Large style-only rewrites of controller, service, model, or view files are not allowed.
- Any refactor must have a clear reason:
  - does it fix a bug?
  - does it safely reduce repeated code?
  - does it reduce controller bloat?
  - does it improve permission or workflow safety?
- Refactoring must not change external behavior unless that behavior change is explicitly intended and documented.

Special note:
- Controller bloat caused serious time loss in this project before.
- Refactoring must be controlled, incremental, and task-scoped.

---

# 4. Controller / Service / Model Architecture Rules

Core principle:
- controllers must stay thin
- business logic must live in services
- models must remain data-access oriented

Rules:
- Heavy business rules must not be written inside controllers.
- Controllers should only handle request input, validation flow, auth context usage, service calls, and response/view return.
- Business rules such as inventory reduction, order logic, permission validation, status transitions, digital access granting, and moderation logic must live in the service layer.
- Business workflow must not be embedded into models.
- Models should mainly focus on queries, persistence, and data access.
- A single controller must not accumulate too many responsibilities.
- When adding new logic, first decide whether it belongs in the controller or in a service.
- If no service exists yet, add a controlled service instead of bloating the controller further.
- However, this rule does not mean that a new service should be created for every small task.
- A new service should only be introduced when a genuinely separate responsibility area emerges.
- A single service file must not grow unnecessarily.
- As a general guideline, a service file should remain under roughly 700-800 lines whenever reasonably possible.
- If a service naturally starts exceeding this range, its responsibilities should be reviewed and splitting into meaningful sub-services should be considered.
- Splitting must be based on responsibility boundaries, not done randomly.
- Services must not be fragmented into meaningless pieces only to reduce line count.
- Repeated functions of the same type or with highly similar behavior must not be added in a way that bloats the service.
- Whenever possible, repeated logic should be reused through a shared helper/private method or by calling an existing appropriate method.
- Instead of multiplying similar functions that do the same job, prefer calling one reliable shared function.
- Copy-paste growth inside services is not acceptable.
- The goal is to keep both controllers and the service layer balanced, readable, and maintainable.
---

# 5. Permission and Security Rules

Security is not an afterthought in this project. It is part of the core architecture.

Rules:
- the deny-by-default approach must be preserved
- route/filter protection must not be bypassed or removed
- service-level guards must be preserved for critical actions
- a secretary must never behave like an admin
- no URL-based permission bypass must be possible
- hiding a menu item is never enough; backend authorization must also exist
- user, secretary, and admin boundaries must remain explicit
- permission checks must be treated as a system-level concern, not only a UI concern
- every new route must be evaluated for auth/permission protection
- object-level authorization must be enforced where ownership matters

Especially in:
- admin panel
- secretary access
- order / shipment operations
- review moderation
- digital access
- page/dashboard publish flows

permission leaks are strictly unacceptable.

---

# 6. Database Rules

Database changes must be handled very carefully in this project.

- The existing working schema must not be broken unnecessarily.
- Migration strategy must be additive.
- Prefer adding new columns or new tables over risky destructive changes.
- Do not write risky migrations that remove working columns or conflict with existing data.
- Existing migration files must not be arbitrarily modified retroactively.
- Use unique timestamps for new migrations.
- Check table and field existence before adding columns where appropriate.
- Critical names such as database, table, and column names must not be changed without verification.
- Seed logic must not break the existing auth and permission structure.
- UUID-based identity logic must remain intact where used.
- Snapshot logic must preserve historical data where required.
- Data integrity is critical in orders, payments, shipments, permissions, and audit areas.

Additional safety:
- Any bulk SQL logic that may damage existing data must be treated with caution.
- Destructive data operations should not be suggested casually.

---

# 7. Migration and Seeder Rules

- Migration files must not be rewritten backward.
- New requirements must be handled with new migrations.
- Do not solve new needs by editing old migrations.
- Migrations must be additive and safe.
- Use tableExists / fieldExists checks when helpful.
- Seeders should be designed to be close to idempotent when possible.
- Roles, permissions, and initial records must not create uncontrolled duplicates.
- Auth and role/permission seed structure must remain stable.

---

# 8. Encoding and File Format Rules

Encoding corruption is a critical risk in this project.

Mandatory rules:
- All source files must be saved as UTF-8 without BOM.
- UTF-8 with BOM must not be used.
- Any operation that may change encoding must be handled carefully.
- Turkish characters must not be corrupted.
- BOM-related issues must especially be avoided in PHP, views, config, routes, and migrations.
- Encoding should be verified after saving when necessary.
- Hidden copy-paste character corruption must be avoided.
- Line ending format must not be changed unnecessarily.
- Existing file encoding integrity must not be broken.

Additional rule:
- Syntax should be checked after changes whenever possible.
- Avoid saves that may introduce PHP parse errors.

---

# 9. UI and View Change Rules

- No arbitrary visual redesign is allowed outside the requested scope.
- A small page adjustment must not cause a full view rewrite.
- Areas that the user already approves and likes must be preserved.
- Working and approved admin screens must not be changed unnecessarily.
- User-facing changes should be modern, clean, and controlled.
- Mobile layout must not be broken.
- Existing component structure must not be dismantled without reason.
- Turkish panel terminology and text choices must be preserved where already decided.
- Only the requested area should be changed; avoid design changes with chain side effects.

---

# 10. Admin Panel Protection Rules

- The admin panel is considered largely stabilized in this project.
- No redesign should be applied to admin screens unless explicitly requested.
- Existing admin screen behavior must not be changed without reason.
- Any change to the admin side must be clearly within the active task scope.
- User-facing development must not regress the admin panel.
- Existing page management, builder, dashboard, and permission flows on the admin side must be preserved.
- Previously approved admin UI decisions must not be broken.

---

# 11. Secretary Rules

- A secretary is not an admin.
- Secretary access must always be treated as permission-driven.
- A secretary must only access explicitly granted areas.
- Menu visibility must not be confused with real backend authorization.
- Secretary-specific route protection must be considered where needed.
- Secretary actions in review moderation, order handling, or shipment flows must remain audit-friendly.
- No shortcut should allow accidental access to admin-only modules.

---

# 12. User Frontend Rules

- The user-facing side must be modern but not excessive.
- User experience should be strong, but maintenance cost must remain controlled.
- Product list, product detail, cart, and checkout areas must be developed incrementally.
- Frontend work must not be treated as pure visual work; data flow, pricing, inventory, and access rules must also be considered.
- Controlled flexibility should be preferred for product card and product detail layouts.
- Admin-manageable block structure must be preserved.
- Publish/draft logic must not be broken on builder-related screens.

---

# 13. Builder and Versioning Rules

- Draft / published / archived logic must be preserved.
- Live and draft structures must not be mixed.
- Publish flow must remain explicit and controlled.
- Builder configurations must not be trusted without validation.
- Validation logic for schema-based configuration must remain intact.
- Block rendering must rely on a controlled partial/view system.
- Even if the admin enters a bad configuration, the system should fail in a controlled way rather than collapse.
- Page builder and dashboard builder must not be given unnecessary unrestricted freedom.
- Controlled slot-based structure is preferred.

---

# 14. Order, Inventory, and Business Rule Rules

- Cart logic must not be underestimated.
- Repricing during checkout must not be forgotten.
- Order item snapshot logic must be preserved.
- Printed and digital product flows must not be mixed carelessly.
- Digital access granting must be handled as a separate concern.
- Inventory reduction logic must not be implemented carelessly.
- Stock race conditions must be considered.
- Shipment statuses must not change randomly.
- Payment callback/webhook scenarios must be treated as idempotent.
- Order and payment records must not be mutated in ways that break historical truth.

---

# 15. Audit Log Rules

Audit thinking must be preserved in the following areas:
- permission changes
- price changes
- inventory changes
- review moderation actions
- order status changes
- shipment status changes
- page publish
- dashboard publish
- digital access revoke

Rule:
- For every critical action, ask whether it should be written to audit logs.
- Secretary actions must be especially traceable.

---

# 16. Testing and Verification Rules

Whenever possible, every task output should include:
- changed file list
- short change summary
- manual test steps
- risks / notes

Additional rules:
- The affected screens must be checked manually after changes.
- Visual correctness and behavior correctness must be treated separately.
- Route behavior, button flow, and permission protection must all be checked.
- Mobile behavior must be considered when relevant.
- Writing code alone does not mean the task is complete.

---

# 17. AI / Codex Working Rules

- AI must operate only within the requested scope.
- No unnecessary file touch is allowed.
- Existing structure must be read before changing anything.
- Understand first, then change.
- Prefer solutions that fit the current file structure and architecture.
- No surprise refactors.
- Do not impose generic best-practice solutions that conflict with the actual project structure.
- Every output must clearly state which files were changed.
- Risks must be stated openly.
- Uncertain assumptions must not be hidden.

---

# 18. Decision Principle

Decision priority in this project is:

1. preserve the working structure
2. preserve security
3. preserve permission boundaries
4. preserve data integrity
5. improve architecture without bloating it
6. improve visual quality
7. avoid unnecessary cleanup

Default principle:
- first, do no harm
- then improve

---

# 19. Priority Order in Case of Conflict

If rules conflict, apply this priority order:

1. prevent data loss
2. prevent security / permission leaks
3. avoid breaking working modules
4. preserve encoding and syntax integrity
5. avoid architectural bloat
6. visual improvement
7. style cleanup

---

# 20. Summary Rule

The main rule of this project is:

- no silent deletion
- no uncontrolled refactor
- no unnecessary rewrites
- no permission leaks
- no encoding corruption
- no bloated controllers
- no fragile migrations
- no surprise design changes
- yes to small, safe, traceable progress
