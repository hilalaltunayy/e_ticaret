---
name: gitnexus-exploring
description: "Use when the user asks how code works, wants architecture understanding, or needs execution-flow exploration."
---

# Exploring Codebases with GitNexus

## Workflow

1. Read `gitnexus://repos` to discover indexed repos.
2. Read `gitnexus://repo/{name}/context` for overview and staleness.
3. Use `gitnexus_query({query: \"...\"})` to find related execution flows.
4. Use `gitnexus_context({name: \"...\"})` for symbol-level context.
5. Read `gitnexus://repo/{name}/process/{name}` for full flow traces.

> If step 2 says the index is stale, run the Docker-based GitNexus analyze command.

## Checklist

- Read repo context first.
- Query by concept.
- Review returned processes.
- Deep-dive on key symbols with `context`.
- Read source files for implementation details.
