---
name: gitnexus-debugging
description: "Use when debugging bugs, tracing errors, or understanding why behavior fails."
---

# Debugging with GitNexus

## Workflow

1. Run `gitnexus_query({query: \"<error or symptom>\"})`.
2. Run `gitnexus_context({name: \"<suspect symbol>\"})`.
3. Read `gitnexus://repo/{name}/process/{name}`.
4. Use `gitnexus_cypher(...)` for custom traces if needed.

> If the index is stale, run the Docker-based GitNexus analyze command.

## Checklist

- Capture the symptom clearly.
- Query by error text or behavior.
- Inspect callers/callees with `context`.
- Trace related processes.
- Confirm root cause in source files.
