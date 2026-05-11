<!-- gitnexus:start -->
# GitNexus â€” Code Intelligence

This project is indexed by GitNexus as **e_ticaret** (51058 symbols, 107883 relationships, 300 execution flows). Use the GitNexus MCP tools to understand code, assess impact, and navigate safely.

> If any GitNexus tool warns the index is stale, run the Docker-based GitNexus analyze command.

## Mandatory GitNexus MCP-First Rule

For any question about repository code, implementation details, function counts, symbols, dependencies, call chains, impact, routes, services, models, controllers, or architecture, the agent MUST use GitNexus MCP first.

Do not start with direct repository scanning using rg, grep, find, ls, manual file traversal, or broad file reads.

Allowed workflow:
1. Use GitNexus MCP `query`/`context`/`impact`/`detect_changes` first.
2. Use direct file inspection only after GitNexus identifies the relevant files or symbols.
3. If GitNexus MCP is unavailable, disconnected, stale, or errors, STOP and ask the user: "GitNexus MCP is not available. May I continue with direct repository inspection?"
4. Do not silently fall back to direct scanning.

If the user asks "how did you get this?", the answer must explicitly say whether GitNexus MCP was used or direct scanning was used with permission.

## Always Do

- **MUST run impact analysis before editing any symbol.** Before modifying a function, class, or method, run `gitnexus_impact({target: "symbolName", direction: "upstream"})` and report the blast radius (direct callers, affected processes, risk level) to the user.
- **MUST run `gitnexus_detect_changes()` before committing** to verify your changes only affect expected symbols and execution flows.
- **MUST warn the user** if impact analysis returns HIGH or CRITICAL risk before proceeding with edits.
- When exploring unfamiliar code, use `gitnexus_query({query: "concept"})` to find execution flows instead of grepping. It returns process-grouped results ranked by relevance.
- When you need full context on a specific symbol â€” callers, callees, which execution flows it participates in â€” use `gitnexus_context({name: "symbolName"})`.

## Never Do

- NEVER edit a function, class, or method without first running `gitnexus_impact` on it.
- NEVER ignore HIGH or CRITICAL risk warnings from impact analysis.
- NEVER rename symbols with find-and-replace â€” use `gitnexus_rename` which understands the call graph.
- NEVER commit changes without running `gitnexus_detect_changes()` to check affected scope.

## Resources

| Resource | Use for |
|----------|---------|
| `gitnexus://repo/e_ticaret/context` | Codebase overview, check index freshness |
| `gitnexus://repo/e_ticaret/clusters` | All functional areas |
| `gitnexus://repo/e_ticaret/processes` | All execution flows |
| `gitnexus://repo/e_ticaret/process/{name}` | Step-by-step execution trace |

## CLI

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |

Canonical Docker commands:
- Analyze: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 analyze --verbose"`
- Status: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 status"`
- List: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 list"`
- MCP: `docker exec -i gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 mcp"`

## Execution Order Policy (Plan -> Coder -> Test)

This repository enforces a hard-fail execution order for delivery work:

`PLANNED_APPROVED -> CODING_DONE -> TEST_VERIFIED -> FINAL_REPORT`

Hard rules:
- Development, refactor, bugfix, test, migration, integration, and cleanup work MUST start with a plan and MUST NOT begin coding without an approved `PLAN_PACKET v1`.
- Coding MUST be performed only by the Coder phase/agent and only within the approved scope.
- Testing MUST run after coding and MUST use a `TEST_INPUT_PACKET v1` created from coder output.
- Test phase MUST NOT return PASS without `CODER_REPORT v1`.
- On FAIL or BLOCKED, flow MUST route back to Coder via `CODER_FEEDBACK v1`.
- Investigation-only tasks may stop at planning/analysis without entering coding/testing.

## Handoff Contracts

The following packet contracts are mandatory and use Markdown with fixed headings/field names:

1. `PLAN_PACKET v1` (Plan -> Coder)
2. `TEST_INPUT_PACKET v1` (Coder -> Test)
3. `CODER_FEEDBACK v1` (Test -> Coder for FAIL/BLOCKED)
4. `FINAL_STATUS v1` (Test -> Final)

Each packet MUST include:
- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `next_action`

## Agent-Specific Exit Criteria

### Planning Exit Criteria
- Produces `PLAN_PACKET v1` with explicit scope and out-of-scope.
- Includes success criteria, risk/mitigation, and `TEST_EXPECTATIONS`.
- Includes GitNexus-first evidence for technical claims (`query/context/impact`) and calls out any targeted file reads if needed.
- Performs no implementation changes.

### Coder Exit Criteria
- Requires valid `PLAN_PACKET v1`; otherwise return `NO_PLAN_BLOCKER`.
- Applies only approved scope; any out-of-scope need must produce a scope deviation record and halt for approval.
- Produces `CODER_REPORT v1` with change groups, command log, expected vs actual outcomes, open risks.
- Produces `TEST_INPUT_PACKET v1` for Test phase.
- Preserves all existing GitNexus obligations, including impact-before-edit for symbols and detect_changes-before-commit.

### Test Exit Criteria
- Requires `CODER_REPORT v1`; otherwise return `BLOCKED: missing coder report`.
- Produces `TEST_REPORT v1` with evidence and one decision only: `PASS`, `FAIL`, or `BLOCKED`.
- If `FAIL` or `BLOCKED`, MUST produce `CODER_FEEDBACK v1` with repro and expected/actual behavior.

## Failure Routing (Test -> Coder)

Routing rules:
- `PASS` -> emit `FINAL_STATUS v1` and close execution cycle.
- `FAIL` -> send `CODER_FEEDBACK v1`, return to Coder, wait for updated `CODER_REPORT v1` and `TEST_INPUT_PACKET v1`.
- `BLOCKED` -> classify blocker type (`env`, `dependency`, `data`, `permission`, or `other`), provide concrete unblock actions, then route to Coder or owner based on blocker source.

## GitNexus-First Across All Roles

Plan, Coder, and Test phases all inherit GitNexus-first behavior:
- Start code intelligence tasks with GitNexus tools.
- Use direct file reads only when GitNexus output is insufficient for required detail.
- When direct reads are used after GitNexus, include a note: "GitNexus yetersiz kaldı, hedefli dosya okumasına geÃ§ildi."

## Planning Phase Enforcement Addendum (Mandatory)

This addendum strengthens workflow behavior for delivery-class requests and does not weaken any existing GitNexus-first rule.

- For any development, UI change, bugfix, refactor, migration, integration, cleanup, or test request, the first assistant output MUST be treated as Planning phase output.
- Planning phase output MUST be emitted as `PLAN_PACKET v1` format; free-text short plans are not allowed for delivery-class requests.
- `PLAN_PACKET v1` MUST include these fields/sections:
  - `TEST_EXPECTATIONS`
  - `GITNEXUS_FIRST_EVIDENCE`
  - `WAITING_FOR_USER_APPROVAL`
- The following user expressions MUST be treated as plan-only triggers:
  - "şimdilik dosya değiştirme"
  - "kod yazma"
  - "önce ne yapacağını söyle"
  - "planını sun"
  - "implementasyon başlatma"
- When a plan-only trigger is present, transition to Coder phase is forbidden until explicit user approval is received.
- Planning output `Next Action` MUST indicate waiting state by including `WAITING_FOR_USER_APPROVAL`.

<!-- gitnexus:end -->
