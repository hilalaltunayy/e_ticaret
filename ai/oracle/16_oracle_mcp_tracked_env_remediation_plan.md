# 16 Oracle MCP Tracked Env Remediation Plan

## Purpose

Plan how to safely stop tracking the root `.env` file without deleting it locally and without exposing secrets.

This document is planning-only. It does not run Git commands, does not read `.env`, does not modify `.env`, and does not create runtime files.

## 1. Current Finding

User-provided manual check results:

- `git check-ignore -v --no-index .env` confirms `.gitignore` matches `.env`.
- `git ls-files .env` returns `.env`.

Interpretation:

- `.gitignore` is configured to ignore `.env`.
- `.env` is still tracked by Git because it was added before or despite ignore protection.
- This is a blocker before Oracle MCP runtime creation.

## 2. Why Tracked `.env` Is a Blocker

A tracked `.env` may expose real secrets through Git history, staging, commits, diffs, or remote pushes.

Oracle MCP runtime planning requires local secrets to remain local-only. If `.env` is tracked, runtime implementation must not proceed because future Docker/MCP work may increase the chance of accidental secret exposure.

Runtime creation remains blocked until `.env` is no longer tracked and local secret handling is confirmed safe.

## 3. What `.gitignore` Does and Does Not Do

What `.gitignore` does:

- Prevents new untracked matching files from being added by default.
- Protects future local `.env` files from accidental tracking.
- Allows `.env.example` to remain trackable when `!.env.example` is present.

What `.gitignore` does not do:

- It does not automatically untrack files already tracked by Git.
- It does not delete secrets from Git history.
- It does not remove `.env` from the index by itself.
- It does not prove `.env` is safe if `.env` is already tracked.

## 4. Safe Remediation Concept

The safe remediation concept is:

- Use `git rm --cached .env` later only after explicit approval.
- This removes `.env` from Git tracking.
- It must not delete the local `.env` file.
- The local `.env` should remain present on disk.
- The next commit should include the `.gitignore` addition and the removal of `.env` from Git tracking.
- Before commit, staged files must be reviewed carefully.

Important:

- This plan does not run `git rm --cached .env`.
- This plan does not stage or commit anything.

## 5. What NOT To Do

- Do not print `.env`.
- Do not paste `.env`.
- Do not delete `.env`.
- Do not edit `.env`.
- Do not overwrite `.env`.
- Do not commit before reviewing staged files.
- Do not run `git reset`.
- Do not run `git checkout`.
- Do not run `git clean`.
- Do not discard changes.
- Do not stage unrelated files.
- Do not modify `app/`.

## 6. Required Manual Validation After Remediation

After an explicitly approved remediation command is run later, validate:

```text
Test-Path .env
```

Expected:

- `True`
- This confirms the local `.env` file still exists.

```text
git ls-files .env
```

Expected:

- Empty output
- This confirms `.env` is no longer tracked by Git.

```text
git check-ignore -v --no-index .env
```

Expected:

- Output showing `.gitignore` is the matching ignore source.

```text
git status --short .env
```

Expected:

- Status depends on the exact remediation step.
- If `git rm --cached .env` has been staged, Git may show `.env` as removed from tracking.
- The local `.env` file must still exist and must not be printed.

## 7. Commit Safety Notes

- Commit must include `.gitignore`.
- Commit may include removal of tracked `.env` from the Git index.
- Commit must not include `.env` contents.
- Commit must not include unrelated files.
- Commit must not include app code changes.
- Review staged files before commit.
- Review staged diff names without printing `.env` contents.
- If `.env` content appears in any diff output, stop immediately and do not continue until the secret exposure risk is handled.

## 8. Final Decision

Runtime creation allowed: NO

Blocking issues:

- `.env` is currently tracked by Git according to user-provided `git ls-files .env` output.
- Runtime creation must wait until `.env` is removed from tracking without deleting the local file.

Approved next action:

- Run an explicit user-approved remediation command only, such as `git rm --cached .env`, in a controlled step.
- Then run the required manual validation checks before any runtime implementation continues.
