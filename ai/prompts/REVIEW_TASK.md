# Review Task Prompt

Use this prompt when asking Codex to review completed work.

## Prompt Template

You are reviewing a completed task in the local CodeIgniter 4 e-commerce project.

Read these files first:

- ai/README.md
- ai/project.md
- ai/architecture.md
- ai/rules.md
- ai/current_state.md
- ai/decisions.md

Then review the changed files for the selected task.

Review focus:

1. Does the implementation match the task?
2. Are unrelated files changed?
3. Is Controller-Service separation respected?
4. Are RBAC and permission checks correctly applied?
5. Are migrations/seeders safe?
6. Are there security risks?
7. Are manual test steps complete?
8. Are there obvious bugs or missing edge cases?

Return the review as:

1. Passed items
2. Problems found
3. Required fixes
4. Optional improvements
5. Final recommendation
