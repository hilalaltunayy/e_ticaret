# 26 Oracle MCP Controlled Docker Creation Plan

## Purpose

Define the controlled plan for creating the first local-only Dockerfile, Compose file, and runtime output placeholder for the future Oracle MCP runtime.

This document is planning-only. It does not create Docker runtime files, MCP server code, scripts, automation, secrets, or application changes.

## Current Context

- `ai/oracle/25_oracle_mcp_docker_seccomp_acceptance.md` accepted the Docker seccomp warning for local-only development.
- The warning still requires review before any production or hardened deployment use.
- `ai/oracle/runtime/` exists.
- `ai/oracle/runtime/` currently contains only `README.md`.
- No Dockerfile exists yet.
- No Compose file exists yet.
- `app/` must remain read-only and untouched.

## 1. Exact Files/Folders To Create In Next Task

The next explicit task may create only these files:

- `ai/oracle/runtime/Dockerfile`
- `ai/oracle/runtime/compose.yaml`
- `ai/oracle/runtime/output/.gitkeep`

No other runtime, MCP, script, automation, secret, credential, wallet, or environment files are approved by this plan.

## 2. Minimal Dockerfile Principles

The future Dockerfile must follow these principles:

- Use a lightweight base image appropriate for the minimal local MCP runtime.
- Create and run as a non-root user.
- Use a working directory inside `/workspace`.
- Do not copy secrets into the image.
- Do not copy `.env`, `.env.local`, Oracle wallets, private keys, tokens, certificates, or credentials.
- Do not include Oracle credentials or wallet files.
- Do not perform automatic application modification.
- Do not write to `app/`.
- Do not run repository mutation commands during image build.
- Keep the image minimal until MCP server implementation is explicitly approved.

## 3. Minimal Compose Principles

The future Compose file must follow these principles:

- Mount the project repository read-only by default.
- Allow a writable mount only for `ai/oracle/runtime/output/`.
- Do not mount the host root directory.
- Do not use privileged mode.
- Set `restart: "no"`.
- Use an explicit local container name.
- Keep networking minimal.
- Do not expose ports unless a later MCP transport design explicitly requires it.
- Do not mount real `.env` files yet.
- Do not reference Oracle credentials yet.
- Do not start any service that modifies application files.

Recommended local container name:

- `oracle-mcp-local`

Recommended future mount intent:

- Repository mount: read-only
- Oracle output mount: writable only for `ai/oracle/runtime/output/`

## 4. What Must NOT Happen

The next task must not:

- Expose ports unless required by an approved MCP transport design.
- Mount `.env`.
- Create `.env`, `.env.local`, `.env.example`, wallet, credential, key, token, certificate, or secret files.
- Add Oracle credentials.
- Add Oracle wallet files.
- Create MCP server code.
- Create scripts.
- Create automation.
- Grant write access to `app/`.
- Start containers.
- Build images.
- Modify Docker Desktop settings.
- Modify `app/`.
- Stage, commit, reset, checkout, clean, or discard files.

## 5. Validation Checklist After Creation

After the Dockerfile, Compose file, and output placeholder are created in the next explicit task, validation must confirm:

| Check | Expected Result |
|------|-----------------|
| Dockerfile exists | `ai/oracle/runtime/Dockerfile` exists |
| Compose file exists | `ai/oracle/runtime/compose.yaml` exists |
| Output placeholder exists | `ai/oracle/runtime/output/.gitkeep` exists |
| No extra runtime files | Runtime folder contains only approved files and the approved output directory |
| Non-root rule | Dockerfile defines or uses a non-root runtime user |
| Workdir rule | Dockerfile uses a `/workspace` working directory |
| No secrets copied | Dockerfile does not copy `.env`, credentials, wallets, keys, tokens, or certificates |
| Read-only project mount | Compose mounts repository read-only |
| Writable output mount only | Compose writable mount is limited to `ai/oracle/runtime/output/` |
| No host root mount | Compose does not mount `/`, drive roots, or broad host directories |
| No privileged mode | Compose does not use privileged container mode |
| Restart policy | Compose uses `restart: "no"` |
| No ports unless justified | Compose exposes no ports unless explicitly required |
| No `.env` mount | Compose does not mount `.env` or `.env.local` |
| No app modification | No `app/` file is changed |
| No container started | Creation task does not start containers |
| No image built | Creation task does not build images |

## 6. Final Decision

Safe to create Dockerfile/compose in next explicit task? YES

Approved next action:

- Create only `ai/oracle/runtime/Dockerfile`, `ai/oracle/runtime/compose.yaml`, and `ai/oracle/runtime/output/.gitkeep` in the next explicit task.
- Keep the files local-development-only, minimal, non-root, read-only by default, and secret-free.
- Do not build, run, or implement MCP code during that task.
