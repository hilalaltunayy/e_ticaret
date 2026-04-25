# 07 Oracle MCP Tool Schema Validation

## Purpose

Validate whether the Oracle MCP tool schemas are complete, safe, and consistent.

## Validation Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| All tools defined | All 10 planned tools should be present. | `repo_lookup`, `domain_lookup`, `route_lookup`, `permission_lookup`, `kb_impact_check`, `task_draft_create`, `plan_draft_create`, `validation_check`, `kb_update_required_check`, and `safety_boundary_check` are defined. | Pass | Complete tool list. |
| Inputs are clear | Each tool should define input fields, types, required flags, descriptions, and examples. | Each tool has an Inputs table with field, type, required, description, and example columns. | Pass | Suitable for later schema conversion. |
| Outputs are clear | Each tool should define output fields, types, descriptions, and examples. | Each tool has an Outputs table with field, type, description, and example columns. | Pass | Suitable for later schema conversion. |
| Access levels correct | Tools should use declared access levels and avoid app writes. | Tools use `read_only_repo`, `kb_read`, `oracle_report_write`, `task_draft_write`, and `no_app_write`. | Pass | Write-capable tools are scoped to reports/drafts. |
| No tool can write `app/` | Tool schemas must prohibit application writes. | Safety requirements and tool notes prohibit app writes. | Pass | Consistent with runtime boundary. |
| Secret handling safe | Tools must not expose secrets. | Design includes no secret output, secret redaction, and secret access blocking through `safety_boundary_check`. | Pass | Future implementation must enforce redaction. |
| Source citation required | Tools must cite KB files or repo paths. | Tool principles and required behavior require source files or KB files. | Pass | Supports evidence-based guidance. |
| Confidence system exists | Tool outputs need confidence levels. | Confidence values are defined as `high`, `medium`, and `low`. | Pass | Clear enough for first implementation planning. |
| Needs review logic exists | Ambiguous evidence should trigger review. | Tools return `Needs Review` for unclear evidence; status values include `needs_review`; manifest `broad_review` and `needs_review` require manual review. | Pass | Strong uncertainty handling. |
| Failure conditions defined | Each tool should define failure behavior. | Each tool includes Failure Conditions. | Pass | Good coverage. |
| No hallucination rule enforced | Tools must not invent repository facts. | Tool principles say tools must not invent repository facts and source evidence is required. | Pass | Implementation should preserve this rule. |

## Tool Coverage Check

- Repo lookup covered:
  - Status: Pass
  - `repo_lookup` covers repository file, symbol, class, and feature path discovery.
- Domain mapping covered:
  - Status: Pass
  - `domain_lookup` covers domain ownership and KB evidence.
- Route mapping covered:
  - Status: Pass
  - `route_lookup` covers routes, controllers, filters, and route KB references.
- Permission mapping covered:
  - Status: Pass
  - `permission_lookup` covers permissions, roles, filters, RBAC, and secretary/admin evidence.
- KB impact covered:
  - Status: Pass
  - `kb_impact_check` maps changed paths to domains, impact levels, and KB files.
- Task creation covered:
  - Status: Pass
  - `task_draft_create` drafts GitNexus-compatible task metadata.
- Plan creation covered:
  - Status: Pass
  - `plan_draft_create` drafts plan steps, KB impact, and review gates.
- Validation covered:
  - Status: Pass
  - `validation_check` validates task metadata, changed paths, KB impact, safety boundaries, and generated plans.
- Safety check covered:
  - Status: Pass
  - `safety_boundary_check` validates mode, filesystem access, secret access, and write intent.

## Missing Tools

No blocking missing tools were found.

Potential future tools, not required before implementation planning:

- `schema_lookup` for field-level model/migration inspection.
- `view_flow_lookup` for frontend view to route/form/action mapping.
- `claim_lookup` for claim registry search.
- `gitnexus_metadata_check` for dedicated GitNexus metadata validation.

## Risks

- Future implementation may weaken boundaries if access levels are not enforced in code.
- JSON schema details are not defined yet.
- Tool error envelope is not fully standardized beyond required output fields.
- Redaction test cases are not defined yet.
- Some write-capable draft/report tools need explicit allowed output path validation.
- `task_draft_create` and `plan_draft_create` should remain draft-only until GitNexus storage exists.
- Route lookup can remain partially manual until exact route extraction exists.

## Final Verdict

PASS: Ready for Oracle MCP implementation planning.
