# 23 Oracle MCP Dockerfile Compose Design

## Purpose

Design the future Dockerfile and Docker Compose approach for the local Oracle MCP runtime without creating Dockerfile, Compose, MCP code, scripts, automation, secrets, or application changes.

## Current Constraints

- Dockerfile/Compose planning is allowed.
- Dockerfile/Compose creation is not allowed yet.
- Docker seccomp warning remains unresolved or not explicitly accepted.
- `ai/oracle/runtime/` exists and contains only `README.md`.
- `app/` must remain untouched.
- No secrets may be created, read, printed, or committed.
- No containers may be started.
- No images may be built.
- Docker Desktop settings must not be modified in this task.

## Proposed Future Dockerfile Design

### Base Image Strategy

- Use a minimal, maintained base image suitable for the chosen MCP runtime language.
- Prefer a slim image when available.
- Pin major runtime versions when implementation starts.
- Avoid images that require unnecessary system privileges.
- Do not include application runtime dependencies unless Oracle MCP actually needs them.

### Non-Root User

- Create and run as a non-root user inside the container.
- Avoid running Oracle MCP as root.
- Ensure the non-root user can read the mounted repository.
- Allow writes only to the explicitly approved Oracle output mount.

### Working Directory

- Use a predictable working directory, for example:

```text
/workspace/repo
```

- Treat the repository mount as read-only by default.
- Keep runtime source code in the runtime image or future approved runtime directory, not in `app/`.

### Dependency Install Approach

- Install only dependencies needed by the Oracle MCP server.
- Keep dependency installation deterministic.
- Avoid installing unnecessary global tools.
- Do not install dependencies into the application project.
- Do not modify `composer.json`, `composer.lock`, or application dependencies for Oracle MCP.

### No Secrets Baked Into Image

- Do not copy `.env` into the image.
- Do not copy API keys into the image.
- Do not copy Oracle wallets into the image.
- Do not copy private keys, certificates, tokens, or credentials into the image.
- Use runtime environment variables or Docker secrets later.
- `.env.example` may contain placeholders only.

## Proposed Future Compose Design

### Read-Only Repository Mount

Future Compose should mount the repository read-only by default:

```text
/workspace/repo:ro
```

The exact host path should be configurable and should not be hardcoded to one developer's machine.

### Approved Writable Output Mount

Writable output should be limited to an approved Oracle output directory, for example:

```text
/workspace/repo/ai/oracle/outputs:rw
```

The output directory must not contain secrets.

### No Host Root Mount

- Do not mount the host root.
- Do not mount broad user home directories.
- Do not mount Docker Desktop configuration directories.
- Mount only the project repository and approved output directory.

### No Privileged Container

- Do not use privileged containers.
- Do not add broad Linux capabilities unless a later security review approves them.
- Do not disable security controls to make early development easier.

### Explicit Env Handling

- Use `.env.example` for placeholders only.
- Real `.env` must remain local and gitignored.
- Real secrets should be passed via local environment variables or Docker secrets later.
- Do not print environment variable values in logs.
- Do not store secrets in Compose files.

## Files That May Be Created Later, But Not Now

Future implementation may create these only after explicit approval:

- `ai/oracle/runtime/Dockerfile`
- `ai/oracle/runtime/docker-compose.yml`
- `ai/oracle/runtime/.env.example`
- `ai/oracle/runtime/README.md` updates
- `ai/oracle/runtime/src/`
- `ai/oracle/runtime/src/server.*`
- `ai/oracle/runtime/src/config.*`
- `ai/oracle/runtime/src/tools/`

These files are not created by this document.

## Files That Must Never Be Created/Committed

Never create or commit:

- real `.env`
- `.env.local`
- Oracle wallet files
- API keys
- private keys
- passwords
- certificates
- access tokens
- cloud credentials
- database credentials
- any file containing real secrets

Also do not commit generated logs or runtime data that may contain sensitive output.

## Docker Seccomp Warning Impact

Current warning:

```text
WARNING: daemon is not using the default seccomp profile
```

Impact:

- This warning does not block writing this design document.
- This warning does block Dockerfile/Compose creation until it is reviewed, resolved, or explicitly accepted as a local-development limitation.
- Docker hardening assumptions must not rely on the default seccomp profile until the Docker daemon behavior is understood.

Required handling:

- Review Docker Desktop settings manually.
- Confirm whether the seccomp warning is expected.
- Decide whether local-only development can proceed with this warning.
- Document that decision before creating Dockerfile or Compose files.

## Validation Checklist Before Actual Creation

Before creating Dockerfile or Compose files, validate:

- Docker seccomp warning has been reviewed.
- Dockerfile/Compose creation is explicitly requested.
- Runtime folder still contains only approved files.
- `app/` is clean and remains read-only.
- `.env` is ignored and not tracked.
- No secret values are included in planned files.
- Repository mount is read-only by default.
- Writable mount is limited to approved Oracle output directory.
- Container is non-root.
- Container is not privileged.
- Host root is not mounted.
- No application dependency files are modified.
- No Docker command will start containers or build images unless separately approved.

## Final Decision

Dockerfile/compose creation allowed after this design? NO

Required validation next:

- Create a validation report for this Dockerfile/Compose design.
- Dockerfile/Compose creation may be reconsidered only after that validation passes and the seccomp warning decision is explicitly recorded.
