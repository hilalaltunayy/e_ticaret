# handoff-schema.md

# Agent Handoff Schema (Markdown Contract)

Bu dosya Plan -> Coder -> Test -> Final akışındaki paketlerin sabit alan sözleşmesini tanımlar.

## Required Header

Her paket aşağıdaki başlıkla başlamalıdır:

- `## <PACKET_NAME> v1`

## Required Metadata Block

Her pakette `### Metadata` altında en az şu alanlar bulunmalıdır:

```yaml
id: <string>
source_agent: <string>
target_agent: <string>
status: <string>
```

## Required Core Sections

Aşağıdaki başlıklar paket tipinden bağımsız zorunludur:

- `### Scope`
- `### Artifacts`
- `### Acceptance Criteria`
- `### Risks`
- `### Next Action`

## Normalized Field Names

Makine/insan uyumluluğu için aşağıdaki alan adlarını kullan:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `next_action`

## Packet Types

1. `PLAN_PACKET v1`
- planning output
- must include `out_of_scope`, `test_expectations`, `gitnexus_evidence`

2. `CODER_REPORT v1`
- implementation output
- must include `plan_reference`, `impact_summaries`, `commands_executed`, `expected_results`, `actual_results`

3. `TEST_INPUT_PACKET v1`
- coder -> test handoff
- must include `changed_behaviors`, `affected_files`, `suggested_test_commands`, `critical_regression_points`

4. `TEST_REPORT v1`
- test verification output
- must include `tests_executed`, `result_summary`, `decision`, `confidence`, `known_risks`

5. `CODER_FEEDBACK v1`
- test -> coder fail/block feedback
- must include `issue_summary`, `reproduction_steps`, `expected_behavior`, `actual_behavior`, `priority`

6. `FINAL_STATUS v1`
- final closure packet
- must include final decision, unresolved risks, and release/readiness note

## Decision Values

`TEST_REPORT v1` kararları sadece:

- `PASS`
- `FAIL`
- `BLOCKED`

## Failure Routing

- `FAIL` or `BLOCKED` -> always produce `CODER_FEEDBACK v1`
- `PASS` -> produce `FINAL_STATUS v1`
