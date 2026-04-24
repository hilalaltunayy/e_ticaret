# Codex Output Format Skill

This skill defines how Codex must format its output after completing a task.

## Purpose

Ensure consistent, readable, and structured output for every task.

## Rules

Codex must always return the following sections:

1. Added files
2. Updated files
3. Deleted files
4. Per-file summary of changes
5. Commands to run
6. Manual test steps
7. Risks / Notes

## Additional Rules

- Do not modify unrelated files.
- Do not perform large refactors unless explicitly asked.
- Stay within task scope.
- If no files were changed in a category, state it clearly.
- If a migration or seeder is added, include the command to run it.
- If routes, filters, or permissions are changed, include test steps.
- If unsure about something, do not assume — mention it as a note.

## Expected Output Format

1. Added files

- ...

2. Updated files

- ...

- ...

3. Deleted files

- ...

4. Per-file summary

- path/to/file.php
  - ...

5. Commands to run

php spark migrate
php spark db:seed SeederName

6. Manual test steps

- ...

7. Risks / Notes

- ...
