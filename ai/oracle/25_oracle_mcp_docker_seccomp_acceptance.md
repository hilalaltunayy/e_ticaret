# 25 Oracle MCP Docker Seccomp Acceptance

## Purpose

Record the decision for the Docker seccomp warning before allowing the next Dockerfile/Compose creation planning step.

This is a decision document only. It does not create Dockerfile, Compose files, MCP code, scripts, automation, secrets, containers, images, or application changes.

## 1. What the Seccomp Warning Means

Warning:

```text
WARNING: daemon is not using the default seccomp profile
```

Meaning:

- Docker normally uses a default seccomp profile.
- Seccomp limits which low-level system calls a container can make.
- The warning means the Docker daemon is not using Docker's default seccomp profile.
- This can change the expected container security baseline.

This warning does not necessarily mean Docker is unusable, but it must be explicitly understood and recorded before Dockerfile or Compose work continues.

## 2. Why It Is Acceptable in Local-Only Development

The warning is accepted for the current local-only Oracle MCP development stage because:

- The project is not being deployed to production.
- The first Dockerfile/Compose work will still be local-only.
- The repository mount is planned as read-only by default.
- `app/` remains read-only.
- No secrets may be baked into images.
- No privileged containers are allowed.
- No host root mount is allowed.
- Writable mounts are limited to approved Oracle output directories.
- The first runtime work remains minimal and controlled.

Acceptance is limited to local development only.

## 3. Why It Is NOT Automatically Acceptable for Production

This warning is not automatically acceptable for production because:

- Production security expectations are stricter than local development.
- Container isolation assumptions must be validated in production-like environments.
- A non-default seccomp state may weaken expected restrictions.
- Production hardening needs explicit review of Docker daemon configuration.
- Security teams or deployment owners may require the default seccomp profile or a documented custom profile.

Any production, CI, remote, shared, or deployed environment must review this warning separately.

## 4. Explicit Decision

Accepted for local development: YES

Requires review for production: YES

Decision scope:

- Applies only to local Oracle MCP development.
- Does not apply to production.
- Does not apply to CI/CD.
- Does not apply to shared runtime environments.
- Does not allow privileged containers.
- Does not allow secrets in images.
- Does not allow writable `app/` mounts.

## 5. Risks and Mitigations

| Risk | Mitigation |
|------|------------|
| Container isolation may differ from Docker default expectations. | Keep runtime local-only and document the warning. |
| Security hardening assumptions may be incomplete. | Keep Dockerfile/Compose minimal and validate before use. |
| A future user may mistake local acceptance for production approval. | Explicitly state production requires separate review. |
| Runtime may accidentally receive broader filesystem access. | Use read-only repository mount and approved writable output mount only. |
| Secrets may leak into image or logs. | Do not bake secrets into image; redact logs and outputs. |
| Container may run with excessive privilege. | Do not use privileged containers; prefer non-root user. |

## 6. Other Hardening Rules Still Apply

All other hardening rules remain required:

- Use a non-root container user when possible.
- Mount repository read-only by default.
- Keep `app/` read-only.
- Do not mount host root.
- Do not use privileged containers.
- Do not bake secrets into images.
- Do not commit real `.env` files.
- Use `.env.example` placeholders only.
- Limit writable mounts to approved Oracle output directories.
- Do not create or read secrets without explicit approval.
- Do not start containers or build images unless explicitly approved in a later task.

## 7. Final Decision

Dockerfile/compose creation allowed next? YES

Approved next action:

- Create Dockerfile/Compose files only in the next separate explicit task, following the hardening rules in `23_oracle_mcp_dockerfile_compose_design.md` and this local-only seccomp acceptance decision.
