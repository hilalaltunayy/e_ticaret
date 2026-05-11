---
name: gitnexus-guide
description: "Use when the user asks about GitNexus itself - available tools, how to query the knowledge graph, MCP resources, graph schema, or workflow reference."
---

# GitNexus Guide

Quick reference for GitNexus MCP tools, resources, and schema.

## Always Start Here

1. Read `gitnexus://repo/{name}/context` for overview and freshness.
2. Match the task to a GitNexus skill.
3. Follow that skill workflow.

> If step 1 warns the index is stale, run the Docker-based GitNexus analyze command.

## Workflow Bridge (Plan -> Coder -> Test)

GitNexus-first behavior remains mandatory in all roles. For delivery workflow use:

1. Planning phase with `.claude/skills/workflow/plan-skill/SKILL.md`
2. Coder phase with `.claude/skills/workflow/coder-skill/SKILL.md`
3. Test phase with `.claude/skills/workflow/test-skill/SKILL.md`

References and handoff contracts live in:
- `.claude/skills/workflow/references/planning-template.md`
- `.claude/skills/workflow/references/coder-report-template.md`
- `.claude/skills/workflow/references/test-verification-template.md`
- `.claude/skills/workflow/references/handoff-schema.md`

This bridge does not replace GitNexus exploration/impact rules; it adds execution sequencing and handoff discipline.

## Canonical Docker Commands

- Analyze: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 analyze --verbose"`
- Status: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 status"`
- List: `docker exec gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 list"`
- MCP: `docker exec -i gitnexus sh -lc "cd /workspace && npx -y gitnexus@1.6.3 mcp"`

## Tools Reference

| Tool | What it gives you |
| --- | --- |
| `query` | Execution flows related to a concept |
| `context` | Symbol callers, callees, and process participation |
| `impact` | Upstream/downstream blast radius |
| `detect_changes` | Git-diff impact of current changes |
| `rename` | Coordinated multi-file rename |
| `cypher` | Raw graph queries |
| `list_repos` | Indexed repos |

## Resources Reference

| Resource | Content |
| --- | --- |
| `gitnexus://repo/{name}/context` | Stats, staleness check |
| `gitnexus://repo/{name}/clusters` | Functional areas |
| `gitnexus://repo/{name}/cluster/{clusterName}` | Cluster members |
| `gitnexus://repo/{name}/processes` | Execution flows |
| `gitnexus://repo/{name}/process/{processName}` | Step-by-step flow |
| `gitnexus://repo/{name}/schema` | Graph schema |
