# 10 Oracle MCP Beginner Setup Guide

## Purpose

This guide explains how to prepare the local environment before Oracle MCP implementation starts.

It is written for beginners and focuses on safe preparation only. It does not create runtime files, Docker files, MCP server code, scripts, automation, secrets, or application changes.

## What This Guide Does NOT Do

- It does not create `ai/oracle/runtime/`.
- It does not create a Dockerfile.
- It does not create Docker Compose files.
- It does not create MCP server code.
- It does not create scripts or automation.
- It does not create or store secrets.
- It does not modify `app/`.
- It does not run Docker containers.
- It does not connect to any AI provider.

## Prerequisites Checklist

Before implementation starts, confirm:

- Docker Desktop is installed.
- Docker Desktop can run on the local machine.
- The PHP / CodeIgniter project already exists locally.
- The repository path is known.
- Oracle access information will stay local only.
- No real secrets will be committed.
- No credentials will be pasted into prompts, KB files, Oracle docs, GitNexus metadata, or commit messages.

## Folder Safety Explanation

- `ai/oracle/` contains Oracle planning, validation, and guide documents.
- `ai/oracle/` is safe for documentation work.
- `ai/oracle/runtime/` will be the future runtime area.
- `ai/oracle/runtime/` is not created yet and should not be created until implementation starts.
- `app/` contains application code and must stay untouched during planning.
- `ai/domain-kb/` remains the Domain KB and contract source.
- Future Oracle runtime output should be limited to approved Oracle output folders.

## Local Secret Handling Rules

- Use a local `.env` later when implementation starts.
- Never commit credentials.
- Never paste real secrets into prompts.
- Never store API keys, tokens, private keys, passwords, or certificates in repository files.
- `.env.example` may contain placeholders only.
- Real `.env` must stay local.
- Logs and tool outputs must redact secrets.
- If a secret-like value appears in output, stop and review before continuing.

## Windows Path Notes

- This project may use Windows-style local paths such as `C:/code/e_ticaret`.
- Docker examples may later need container paths such as `/workspace/repo`.
- Avoid hardcoded user-specific paths in implementation.
- Prefer configurable paths through environment variables.
- Be careful with path separators when mapping Windows paths into Docker.
- Do not assume every developer has the same drive letter or username.

## Docker Preparation Checklist

Before any Docker implementation starts, confirm:

- Docker Desktop is installed.
- Docker Desktop is running.
- A basic Docker version check works locally.
- Docker can access the project folder when explicitly configured.
- No Oracle container has been created yet.
- No Oracle image has been built yet.
- No Dockerfile has been created yet.
- No Docker Compose file has been created yet.

This guide does not include executable Docker commands. Commands should be added later in an implementation-specific setup guide.

## MCP Preparation Checklist

Before MCP implementation starts, confirm:

- Tools start read-only first.
- `safety_boundary_check` is implemented first.
- `repo_lookup` comes before write-capable tools.
- `domain_lookup` comes before write-capable tools.
- `kb_impact_check` comes before task/plan draft tools.
- Write-capable tools must be limited to approved Oracle output directories.
- No tool may write to `app/`.
- No tool may read real `.env` without explicit approval.
- Every tool output must include source evidence.
- Every unclear result must return `Needs Review`.

## Pre-Implementation Confirmation Checklist

Before creating runtime files, confirm:

- `08_oracle_mcp_implementation_plan.md` has been reviewed.
- `09_oracle_mcp_implementation_plan_validation.md` has passed.
- Docker Desktop is installed and running.
- The intended repository path is known.
- The intended output path is known.
- Secret handling rules are understood.
- `app/` will remain read-only.
- The first implementation target is `safety_boundary_check`.
- The first lookup target after safety is `repo_lookup`.
- The first KB-aware lookup target is `domain_lookup`.
- No production deployment is planned.
- No CI/CD integration is planned.
- No GitNexus implementation is included in the Oracle beginner setup.
- No Orchestrator implementation is included in the Oracle beginner setup.

## Stop Conditions

Stop before implementation if:

- Docker Desktop is missing.
- Docker Desktop cannot start.
- Oracle access details are missing.
- Any request asks to commit secrets.
- Any request asks to paste real secrets into prompts or documentation.
- Any request asks to modify `app/` before explicit approval.
- Any request asks to create Docker/MCP runtime files before the implementation phase starts.
- Any request asks to bypass `safety_boundary_check`.
- Any request asks to mount the repository writable by default.
- Any request asks to log or display real secret values.

## Final Note

This guide prepares the local environment conceptually. It is not an implementation step.

The next step should be a validation report for this beginner setup guide before any runtime folder, Dockerfile, MCP server code, script, automation, or secret handling file is created.
