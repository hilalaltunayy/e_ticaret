---
name: gitnexus-refactoring
description: "Use when renaming, extracting, splitting, moving, or restructuring code safely."
---

# Refactoring with GitNexus

## Workflow

1. Run `gitnexus_impact({target: \"X\", direction: \"upstream\"})`.
2. Run `gitnexus_query({query: \"X\"})`.
3. Run `gitnexus_context({name: \"X\"})`.
4. Plan update order: interfaces, implementations, callers, tests.

> If the index is stale, run the Docker-based GitNexus analyze command.

## Checklist

- Preview renames with `gitnexus_rename(..., dry_run: true)`.
- Apply rename only after review.
- Run `gitnexus_detect_changes()` after refactoring.
- Validate affected flows with tests.
