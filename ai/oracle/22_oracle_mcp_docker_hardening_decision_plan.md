# 22 Oracle MCP Docker Hardening Decision Plan

## Purpose

Define the Docker hardening decisions required before any Oracle MCP Dockerfile or Docker Compose creation.

This is read-only planning. It does not create Dockerfile, Compose files, MCP code, scripts, automation, secrets, or application changes.

## 1. Current Blocker

Current blocker:

```text
WARNING: daemon is not using the default seccomp profile
```

Why it matters:

- Docker seccomp is part of the normal container security baseline.
- The current warning means Docker may not be using the expected default seccomp profile.
- Dockerfile or Compose hardening should not proceed until this warning is reviewed, resolved, or explicitly accepted as a local-development limitation.

Current state:

- `ai/oracle/runtime/` exists.
- Runtime folder contains only `README.md`.
- Dockerfile is not created.
- `docker-compose.yml` is not created.
- MCP server code is not created.
- Scripts, automation, and secrets are not created.

## 2. What Must Be Decided Before Dockerfile/Compose

Before Dockerfile or Compose creation, decide:

- Is the seccomp warning expected in this local Docker Desktop environment?
- Is the warning acceptable for local-only development?
- Can Docker Desktop be adjusted to use the default seccomp profile?
- Should Dockerfile/Compose creation wait until the warning is resolved?
- What minimum hardening rules must every future Dockerfile/Compose file follow?
- Which terminal/session should be used for Docker work?
- Which output directories are allowed for future writable mounts?
- How will secrets be passed without committing them?

## 3. Safe User-Side Manual Checks

The user may run these manually in the terminal where Docker works:

```text
docker info
docker version
docker compose version
```

Review manually:

- Whether the seccomp warning still appears.
- Whether Docker Desktop settings show any custom engine/security configuration.
- Whether Docker is running in a corporate, WSL, or custom security environment.

Do not change settings during this check unless there is a separate explicit task.

## 4. Docker Desktop Settings Review

Docker Desktop settings likely need user review.

Recommended user-side review:

- Open Docker Desktop.
- Review the engine/security configuration.
- Check whether a custom seccomp profile or daemon configuration is in use.
- Check whether Docker Desktop is running with expected permissions.
- Do not paste secrets into Docker Desktop settings.
- Do not change settings until a separate explicit decision is made.

Codex should not modify Docker Desktop settings.

## 5. Minimum Docker Hardening Principles for Later

Future Dockerfile/Compose design must follow these principles:

- Use a non-root user in the container when possible.
- Mount repository read-only by default.
- Do not put secrets in the image.
- Do not mount host root.
- Use explicit `.env.example` placeholders only.
- Do not use privileged containers.
- Limit writable mounts to approved Oracle output directories.
- Do not mount `.env` unless explicitly required and approved.
- Do not log secrets.
- Do not copy wallet files, private keys, or credentials into images.
- Prefer least privilege for container user, filesystem, network, and environment access.
- Keep `app/` read-only.

## 6. Stop Conditions

Stop before Dockerfile or Compose creation if:

- The seccomp warning is not understood.
- Docker Desktop settings are unclear.
- Docker cannot run in the same terminal/session intended for implementation.
- Any request asks to use privileged containers.
- Any request asks to mount the host root.
- Any request asks to bake secrets into an image.
- Any request asks to commit `.env` or credentials.
- Any request asks to make `app/` writable.
- Any request asks to skip safety review.

## 7. Final Decision

Dockerfile/compose planning allowed next? YES

Dockerfile/compose creation allowed next? NO

Blocking issues:

- Docker seccomp warning is not yet resolved or explicitly accepted.
- Docker hardening rules have not yet been converted into a concrete Dockerfile/Compose design.

Non-blocking notes:

- Runtime folder README work is already complete.
- Planning a Dockerfile/Compose design is safe as long as no Dockerfile or Compose file is created.
- Actual Dockerfile/Compose creation remains blocked.

Approved next action:

- Create a Dockerfile/Compose design document next.
- Do not create Dockerfile or `docker-compose.yml` until that design is validated and explicitly approved.
