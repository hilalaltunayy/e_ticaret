# 17 KB Update Skill Validation

## Purpose

Validate whether the KB update skill design is correct, complete, safe, and consistent with the KB update policy.

This is an audit-only document. It does not implement the skill and does not modify application code.

## Policy Alignment Check

| Policy Rule | Covered in Skill? | Status | Notes |
|-------------|-------------------|--------|-------|
| KB must reflect current repository state. | Yes | Pass | Skill requires repo-first, source-backed updates and prohibits invented facts. |
| Any code change affecting a domain must trigger KB review/update. | Yes | Pass | Skill maps `changed_paths` through `kb-manifest.yaml` to domains and KB files. |
| Skipped KB updates require explicit reason. | Yes | Pass | Manual override rules require `manual_override_reason` and report recording. |
| Route changes require route matrix and route baseline review. | Yes | Pass | Route handling lists `02_route_permission_matrix.md` and `06_route_baseline.md`. |
| Controller changes require domain and route/view review when behavior changes. | Partially | Partial | Covered through changed path matching and affected KB files, but controller-specific behavior categories are not deeply enumerated. |
| Service logic changes require domain KB review. | Partially | Partial | Covered by manifest matching and domain ownership, but service-specific examples are limited. |
| Model, migration, and seeder changes require schema/model matrix review. | Yes | Pass | Schema/model handling lists `10_schema_model_matrix.md` and domain index review. |
| Permission/RBAC changes require security audit and route matrix review. | Yes | Pass | Security/RBAC handling covers filters, auth services, route access, and claim registry. |
| New feature additions require domain ownership and KB mapping. | Yes | Pass | Skill reviews manifest and domain index when new ownership appears. |
| UI implying backend change must trigger review. | Partially | Partial | Manifest can match UI paths, and broad review exists, but the skill design should later define UI-only versus backend-flow classification more explicitly. |
| Security-related changes must not be skipped silently. | Yes | Pass | Skill states security-sensitive changes must not be skipped without explicit review. |
| Mapping chain must be changed path -> manifest -> domain -> KB files. | Yes | Pass | Update decision logic mirrors the policy chain. |
| Exact/glob/broad_review priority must be respected. | Yes | Pass | Domain matching rules define priority and default actions. |
| Needs review paths cannot auto-update without confirmation. | Yes | Pass | Skill explicitly blocks auto-update for `needs_review`. |
| New automation-relevant claims require stable claim IDs. | Yes | Pass | Claim handling rules require new claim IDs and stable IDs. |
| Existing claims must not be overwritten silently. | Yes | Pass | Skill prohibits silent deletion or overwrite of historical claims. |
| Confidence must change when evidence changes. | Yes | Pass | Claim handling rules cover confidence adjustment. |
| Route baseline grouped rows remain non-exact until extraction. | Yes | Pass | Route change handling preserves this rule. |
| Schema/model field-level uncertainty must be marked Needs Review. | Yes | Pass | Schema/model handling requires Needs Review when field-level verification is unavailable. |
| Update report must be produced. | Yes | Pass | Skill defines update report path and required sections. |
| Failure conditions must block unsafe completion. | Yes | Pass | Skill defines failure conditions for unmapped paths, missing files, route/schema/security gaps, and app-code modification need. |
| Status model must use policy values. | Yes | Pass | Skill uses `pending`, `in_progress`, `completed`, and `skipped`. |

## Logic Completeness

The skill design correctly defines the primary decision flow:

```text
changed_paths
-> kb-manifest.yaml
-> affected domains
-> affected KB files
-> update required / not required
```

The path-to-domain-to-KB mapping is consistent with the manifest and policy.

Covered edge cases:

- Unmapped paths.
- Multiple domain matches.
- `exact`, `globs`, and `broad_review` priority.
- `needs_review` paths.
- Route changes.
- Security/RBAC changes.
- Schema/model changes.
- Manual override and skipped updates.
- Missing required KB files.

Needs Review:

- The design does not define a detailed classification model for controller-only changes.
- The design does not define a detailed classification model for service-only changes.
- The design does not define how to compare before/after file contents because implementation is out of scope.
- The design does not define exact report index registration behavior after creating update reports.
- The design does not specify whether documentation-only changes under `ai/domain-kb` should recursively trigger a KB update report.

These gaps do not block implementation planning, but they should be handled in implementation requirements.

## Safety Check

- Code modification risk:
  - Status: Pass.
  - Notes: The skill design explicitly says never to modify application code and never to modify files under `app/`.

- Hallucination risk:
  - Status: Pass.
  - Notes: The skill requires repo evidence, source anchors, and `Needs Review` for uncertainty.

- Claim overwrite risk:
  - Status: Pass.
  - Notes: The skill prohibits silent claim deletion, ID reuse, and silent overwrites.

- Unsafe completion risk:
  - Status: Pass.
  - Notes: The skill must not mark updates completed unless affected KB files were checked.

- Manual override risk:
  - Status: Pass.
  - Notes: Overrides require explicit reasons and update report recording.

- Broad-review automation risk:
  - Status: Pass.
  - Notes: `broad_review` requires manual review before completion.

## Domain Coverage Check

| Domain Area | Covered? | Status | Notes |
|-------------|----------|--------|-------|
| Route | Yes | Pass | Routes trigger updates to route matrix, route baseline, and claims when needed. |
| Security | Yes | Pass | Filters, auth, RBAC, and security-sensitive changes trigger security audit review. |
| Schema | Yes | Pass | Models, migrations, and seeders trigger schema/model matrix review. |
| Domain index | Yes | Pass | Domain ownership changes trigger domain index review. |
| Claim registry | Yes | Pass | New automation-relevant claims and changed claim status/confidence trigger registry updates. |
| Manifest | Yes | Pass | New ownership, unmapped paths, and new KB files trigger manifest review. |
| UI-only flows | Partially | Partial | UI paths are covered through manifest matching, but UI-only versus backend-flow classification should be expanded later. |
| GitNexus metadata | Partially | Partial | Inputs and outputs align with metadata baseline, but the skill does not implement GitNexus integration by design. |

## Missing Logic

- Needs Review: Controller-only change classification should be expanded in a future implementation checklist.
- Needs Review: Service-only behavior change classification should be expanded in a future implementation checklist.
- Needs Review: UI-only versus backend-flow handling should reference the existing cart, checkout, favorites, review, account, and payment gaps.
- Needs Review: Update reports should define whether they must be added to the manifest immediately or only by a periodic KB housekeeping task.
- Needs Review: The skill should later define how to handle documentation-only changes under `ai/domain-kb`.
- Needs Review: The skill should later define how to detect stale `kb_files` references when a KB file is renamed.

## Automation Readiness

- Can this design be implemented?
  - Status: Yes.
  - Notes: The design provides inputs, outputs, path matching, domain mapping, update rules, safety rules, report format, and failure conditions.

- Is any required concept missing before implementation?
  - Status: No hard blocker.
  - Notes: Implementation will still need detailed mechanics for file diffing, path matching, report creation, and validation, but those are implementation details.

- Is the design safe enough for documentation-only implementation?
  - Status: Yes.
  - Notes: It has explicit no-application-code rules and uncertainty handling.

- Is the design ready for Oracle MCP, GitNexus, or Orchestrator integration?
  - Status: Not yet.
  - Notes: The user explicitly scoped those integrations out. The design is intentionally pre-integration.

## Critical Risks

- Broad or shared manifest paths can cause noisy domain matches.
- Service/controller behavior changes may be hard to classify without implementation-level diff analysis.
- UI-only changes can imply backend expectations that are not route-backed.
- Manual overrides could hide drift if reasons are weak or not reviewed.
- Claim registry can drift if new claims are added in KB prose but not registered.
- Route baseline remains partly manual until route extraction is automated.
- Schema/model matrix remains baseline-level until field-level extraction is automated.

## Final Verdict

PARTIAL PASS

- Can we safely proceed to implementation phase?
  - Yes, for a documentation-only KB Update Skill implementation.
  - The implementation phase must preserve all safety rules and treat Missing Logic items as implementation requirements or explicit Needs Review items.

The skill design is aligned with the KB update policy and is safe enough to proceed, but it should not be treated as complete automation design for Oracle MCP, GitNexus, Orchestrator, route extraction, or schema extraction.
