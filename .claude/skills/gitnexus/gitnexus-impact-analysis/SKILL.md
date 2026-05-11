---
name: gitnexus-impact-analysis
description: "Use when the user asks what breaks if a symbol changes, or needs safety analysis before edits."
---

# Impact Analysis with GitNexus

## Workflow

1. Run `gitnexus_impact({target: \"X\", direction: \"upstream\"})`.
2. Read `gitnexus://repo/{name}/processes` for affected flows.
3. Run `gitnexus_detect_changes()` for diff-based impact.
4. Report risk and blast radius before editing.

> If the index is stale, run the Docker-based GitNexus analyze command.

## Risk Guidance

- d=1 callers/importers: will break
- d=2: likely affected
- d=3: may need testing

Always warn users before proceeding when risk is HIGH or CRITICAL.
