# Implement Task Prompt

Use this prompt when asking Codex to implement a selected task.

## Prompt Template

You are working on the local CodeIgniter 4 e-commerce project.

Read these files first:

- ai/README.md
- ai/project.md
- ai/architecture.md
- ai/rules.md
- ai/current_state.md
- ai/decisions.md

Then read the selected task file and the selected skill files.

Task file:

- ai/tasks/.../...md

Skill files:

- ai/skills/...

Instructions:

1. Stay inside the task scope.
2. Do not rewrite unrelated files.
3. Follow the existing project structure.
4. Use Controller-Service pattern.
5. Respect RBAC and permission rules.
6. Do not make destructive database changes.
7. If migration or seeder is needed, make it additive and safe.
8. Return a clear report using CODEX_OUTPUT_FORMAT.

Expected output:

1. Added files
2. Updated files
3. Deleted files
4. Per-file summary
5. Commands to run
6. Manual test steps
7. Risks / notes
