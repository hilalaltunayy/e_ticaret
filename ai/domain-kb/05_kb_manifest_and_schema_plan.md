# 05 KB Manifest & Schema Plan

## Purpose

Define a consistent structure for Domain KB files and manifest usage so the KB can support automation for KB updates, KB drift checks, Oracle MCP repo guidance, and GitNexus task planning.

This plan does not change application behavior. It defines documentation standards that should be applied before building automation on top of the current KB.

## KB File Structure Standard

Every KB file should have a predictable structure. The exact sections may vary by file type, but the following sections are the baseline standard.

| Section | Required | Purpose |
|--------|----------|---------|
| Purpose | Yes | Explain why the file exists and what decisions it supports. |
| Scope | Yes | Define which domains, paths, routes, or behaviors are covered. |
| Source of Truth | Yes | List the repository files, config files, KB files, or generated baselines used as evidence. |
| Current State | Yes | Describe verified repository facts. |
| Mappings | Required for inventory, route, manifest, and domain files | Link features to routes, controllers, services, models, views, migrations, seeds, permissions, and KB files. |
| Claims | Required for automation-facing files | Record atomic statements with source, confidence, domain, and related files. |
| Assumptions | Yes | Mark uncertain statements explicitly with `Assumption`. |
| Risks | Yes | Separate confirmed risks from uncertain or incomplete areas. |
| Needs Review | Required when uncertainty exists | List paths, domains, or claims that require deeper review. |
| Automation Notes | Required for automation-facing files | Describe how the file should be updated or validated after source changes. |
| Summary | Yes | End with the useful takeaways and next documentation step. |

Minimum required fields for all KB files:

- Title
- Purpose
- Scope
- Source of Truth
- Current State or Findings
- Assumptions
- Risks or Needs Review
- Summary

Automation-facing KB files should also include:

- Stable domain IDs
- Source file paths
- Claim status
- Confidence level
- Related KB files
- Update triggers
- Known drift risks

## KB Claim Schema

A KB claim is an atomic statement about the repository or the KB itself. Claims should be small enough to verify independently.

Recommended claim format:

```text
- Claim:
  - ID:
  - Status:
  - Source:
  - Confidence:
  - Domain:
  - Related files:
  - Related routes:
  - Related permissions:
  - Evidence:
  - Assumption:
  - Last verified:
```

Field definitions:

| Field | Required | Allowed / Expected Values | Notes |
|------|----------|---------------------------|-------|
| Claim | Yes | Plain English statement | One fact or finding per claim. |
| ID | Yes for automation-facing files | Stable ID such as `route.auth.login.public` | Should not change when wording changes. |
| Status | Yes | `Verified`, `Partially Verified`, `Not Verified`, `Incorrect`, `Needs Review` | Use the same status vocabulary across KB files. |
| Source | Yes | Repository path, KB path, or generated baseline path | Prefer concrete files over broad folders. |
| Confidence | Yes | `High`, `Medium`, `Low` | High means source-backed and recently checked. |
| Domain | Yes | Stable domain ID and display name | Example: `auth` / `Auth`. |
| Related files | Yes when applicable | List of paths | Include controllers, models, services, views, migrations, seeds, filters, or config files. |
| Related routes | Required for route/security claims | Route URI, method, and route name when available | Use concrete route rows instead of wildcard-only references. |
| Related permissions | Required for RBAC claims | Permission names or `None` | Distinguish role-only and permission-based access. |
| Evidence | Yes | Short explanation of how the source supports the claim | Keep evidence repo-first. |
| Assumption | Required when uncertain | `None` or explicit `Assumption: ...` | Never hide uncertainty inside a confirmed claim. |
| Last verified | Recommended | Date in `YYYY-MM-DD` format | Helps drift automation and human review. |

Example:

```text
- Claim:
  - ID: route.admin.orders.permission.manage_orders
  - Status: Verified
  - Source: app/Config/Routes.php
  - Confidence: High
  - Domain: order / Order
  - Related files:
    - app/Controllers/Admin/Orders.php
    - app/Config/Filters.php
  - Related routes:
    - GET admin/orders
  - Related permissions:
    - manage_orders
  - Evidence: The admin orders route group is protected by `role:admin,secretary|perm:manage_orders`.
  - Assumption: None
  - Last verified: 2026-04-24
```

## Source Anchoring Strategy

Every important KB statement should point back to the repository evidence that supports it.

Source anchors should use this hierarchy:

1. Exact file path when the claim depends on a file.
2. Exact route URI, HTTP method, route name, and controller method when the claim depends on routing.
3. Exact class and method when the claim depends on controller, filter, service, or model behavior.
4. Exact migration or seed file when the claim depends on tables, fields, permissions, or seeded data.
5. KB file path when the claim is about documentation quality or manifest coverage.

Recommended source reference format:

```text
Source:
  - path: app/Config/Routes.php
    anchor_type: route_group
    symbol: admin/orders
    reason: Defines the route, filter, and controller mapping.
```

For Markdown tables, use compact source anchors:

| Claim | Source Anchor | Status |
|------|---------------|--------|
| Admin orders are permission-protected. | `app/Config/Routes.php` -> `admin/orders` -> `role:admin,secretary|perm:manage_orders` | Verified |

Line numbers are useful for human review but should not be the only anchor because they drift easily. Prefer stable symbols such as route URI, class name, method name, table name, permission name, or view path.

## Route Baseline Strategy

The KB needs a route baseline that represents the currently known correct route structure.

The route baseline should capture one concrete route per row:

| Field | Purpose |
|------|---------|
| Route ID | Stable generated ID for automation. |
| URI | Concrete URI or route pattern. |
| HTTP Method | GET, POST, PUT, PATCH, DELETE, CLI, or ANY. |
| Route Name | Named route if defined. |
| Controller | Controller class. |
| Method | Controller method. |
| Filter / Guard | Applied route-level filter. |
| Expected Permission | Permission expected by route policy. |
| Role Scope | Public, User, Admin, Secretary, Admin + Secretary, or Mixed. |
| Domain | Owning domain ID. |
| View | Known rendered view when statically identifiable. |
| Status | Verified, Partially Verified, Needs Review, or Missing. |

Route changes should be detected by comparing the current extracted route baseline with the stored KB route baseline.

Route drift examples:

- A new route appears without a domain owner.
- A route changes controller or method.
- A route moves into or out of an auth/role/permission group.
- A route loses a filter.
- A route uses a new permission not listed in the domain index.
- A route references a controller method that no longer exists.
- A route is removed but still appears in KB mappings.

The current `02_route_permission_matrix.md` is valuable, but it groups several routes with wildcard notation. Before automation, it should either be expanded into concrete route rows or paired with a generated route baseline file.

## Manifest Standard

`kb-manifest.yaml` should be the primary automation entry point that maps repository paths to domains and KB files.

Recommended top-level structure:

```yaml
version: 2
language: en
kb_root: ai/domain-kb
source_inventory: ai/domain-cube/00_repo_inventory.md

global_kb_files:
  - path: ai/domain-kb/00_repo_inventory.md
    role: repository_inventory
  - path: ai/domain-kb/01_domain_index.md
    role: domain_index

domains:
  auth:
    name: Auth
    owner_type: domain
    kb_files:
      - ai/domain-kb/01_domain_index.md
      - ai/domain-kb/02_route_permission_matrix.md
      - ai/domain-kb/03_security_filter_audit.md
    exact_paths: []
    domain_globs: []
    broad_review_globs: []
    route_patterns: []
    permissions: []
    related_domains: []
    needs_review: []
```

Each domain should include:

- Stable domain ID
- Display name
- Domain purpose
- KB files that must be updated when the domain changes
- Exact paths for high-confidence ownership
- Domain globs for expected domain-owned folders
- Broad review globs for noisy or shared areas
- Route patterns
- Permissions
- Related domains
- Needs review entries
- Assumptions

Watched path standards:

| Path Type | Use Case | Automation Behavior |
|----------|----------|---------------------|
| `exact_paths` | Known files directly owned by the domain | Strong drift signal. KB update is usually required. |
| `domain_globs` | Domain-owned folders or filename patterns | Medium drift signal. KB update may be required. |
| `broad_review_globs` | Shared or noisy areas such as all admin views | Weak drift signal. Human review may be required. |

Wildcard limits:

- Prefer exact paths for controllers, filters, models, services, migrations, and important views.
- Use single-domain globs only when a directory is clearly owned by one domain.
- Avoid broad globs such as `app/Controllers/Admin/**` as a primary signal.
- If a broad glob is necessary, place it under `broad_review_globs`, not `exact_paths`.
- Do not use future-looking globs as proof that a feature exists.
- For missing domains such as Favorites/Wishlist or Review, mark globs as discovery paths, not implemented paths.

Manifest consistency rules:

- All KB files under `ai/domain-kb` must appear in manifest metadata.
- All manifest content must be English.
- All domains in `01_domain_index.md` must have matching manifest domain IDs.
- Any route matrix or security audit file must be listed under domains affected by routes, filters, auth, RBAC, or permissions.
- Any `needs_review` path must include a reason, related domain, and expected next analysis.

## Automation Requirements

### KB Update Skill

- Required: Stable domain IDs, exact source-to-domain mappings, KB file ownership, claim schema, and update triggers.
- Currently missing: Complete manifest coverage, English-only manifest content, claim IDs, source anchors, and clear rules for broad globs.

### KB Drift Check

- Required: Route baseline, manifest path mapping, claim status vocabulary, source anchors, and file-to-KB dependency rules.
- Currently missing: Concrete route baseline, manifest sync with all KB files, exact route/controller/view mappings, and schema/model matrix.

### Oracle MCP

- Required: Repo-first domain summaries, source-backed claims, confidence levels, security-sensitive mappings, and clear assumptions.
- Currently missing: Machine-readable confidence fields, stable claim IDs, stronger source anchors, and normalized domain ownership for Campaign/Coupon, Payment, Cart/Checkout, Favorites/Wishlist, and Review.

### GitNexus

- Required: Domain IDs that map cleanly to tasks, impacted KB files, risk labels, dependency metadata, and commit-time documentation expectations.
- Currently missing: GitNexus task labels, domain ownership metadata, task-to-KB linkage fields, and commit validation rules.

## Required Fixes Before Automation

- Manifest fixes:
  - Convert `kb-manifest.yaml` fully to English.
  - Remove mojibake text.
  - Add all current KB files to manifest metadata.
  - Add `02_route_permission_matrix.md`, `03_security_filter_audit.md`, `04_kb_quality_audit.md`, and `05_kb_manifest_and_schema_plan.md` to relevant `kb_files`.
  - Split watched paths into `exact_paths`, `domain_globs`, and `broad_review_globs`.

- Missing connections:
  - Link each route-sensitive domain to `02_route_permission_matrix.md`.
  - Link auth, RBAC, admin, secretary, order, user account, builder, and public route domains to `03_security_filter_audit.md`.
  - Add explicit mapping for Campaign/Coupon as either a first-class domain or documented subdomain.
  - Add explicit mapping for Payment as either a first-class domain, Order subdomain, or missing/needs-review domain.
  - Distinguish UI-only fragments from backend runtime flows for Favorites/Wishlist, Review, Cart, Checkout, and Payment.

- Standardization needs:
  - Add stable claim IDs for automation-facing findings.
  - Add source anchors for critical claims.
  - Use one shared status vocabulary across KB files.
  - Use one shared confidence vocabulary across KB files.
  - Add route baseline fields for route drift detection.
  - Add schema/model matrix fields for migration and model drift detection.
  - Add manifest validation rules before commit-time automation.

## Final Summary

- What is ready: The KB has a useful file layout, a clear domain index, route/security audit coverage, and a quality audit that identifies the main automation blockers.
- What is not ready: The manifest is stale, not fully English, and not complete enough to drive reliable automation. Claims do not yet have stable IDs, machine-readable confidence, or consistent source anchors.
- Blocking issues: Missing manifest coverage for newer KB files, broad watched paths, no concrete route baseline, no schema/model matrix, unclear Campaign/Coupon and Payment ownership, and UI-only versus backend-flow ambiguity for several storefront features.
- Next step: Standardize `kb-manifest.yaml` to version 2 using this plan, then create a concrete route baseline file for route drift automation.
