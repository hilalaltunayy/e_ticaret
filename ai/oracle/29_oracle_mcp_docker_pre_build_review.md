# 29 Oracle MCP Docker Pre-Build Review

## Purpose

Review whether the minimal local Oracle MCP Dockerfile and Compose file are safe for the first Docker build.

This document is review-only. It does not modify Dockerfile, Compose, runtime output files, application code, secrets, or Git state. It does not start containers or build images.

## Current Context

- `ai/oracle/28_oracle_mcp_docker_creation_validation.md` returned `PASS WITH NOTES`.
- User manually confirmed that `git status --short app` is empty.
- User manually confirmed that `docker info` works.
- Docker still reports: `WARNING: daemon is not using the default seccomp profile`.
- `ai/oracle/25_oracle_mcp_docker_seccomp_acceptance.md` accepted the seccomp warning for local development only.
- `ai/oracle/runtime/Dockerfile` exists.
- `ai/oracle/runtime/compose.yaml` exists.
- `ai/oracle/runtime/output/.gitkeep` exists.
- No Docker build, run, Compose up, or container start has been executed yet.

## Review Checklist

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Dockerfile safe for first build | Minimal placeholder-only Dockerfile | Uses `alpine:3.20`, non-root `oracle` user, `/workspace`, harmless echo command | Pass | Source: `ai/oracle/runtime/Dockerfile` |
| Compose safe for first build | Build-only configuration with safe mounts | Uses local Dockerfile, read-only repo mount, output-only writable mount | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No `C:\` mount | Must not mount Windows drive root | No `C:\` mount is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No `C:/` mount | Must not mount Windows drive root | No `C:/` mount is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No host root mount | Must not mount host root or broad host folder | No host root mount is present | Pass | Relative repo mount is scoped from runtime directory |
| Project mount read-only | Repository mounted read-only | `../../..:/workspace:ro` | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| Only output mount writable | Writable mount limited to Oracle runtime output | `./output:/workspace/ai/oracle/runtime/output:rw` | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No `.env` mount | `.env` must not be mounted | No `.env` reference is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No `env_file` | Compose must not use env files | No `env_file` key is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No secrets | Compose must not define secrets | No `secrets` key is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No ports exposed | No port exposure before MCP transport approval | No `ports` key is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| No privileged mode | Container must not be privileged | No `privileged` key is present | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| Restart policy | Must not auto-restart | `restart: "no"` | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| Read-only root filesystem | Container filesystem should be read-only | `read_only: true` | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| Harmless temporary writable path | Only harmless tmpfs if needed | `tmpfs: /tmp` | Pass | Source: `ai/oracle/runtime/compose.yaml` |
| `app/` clean | Application code must be unchanged | User manually confirmed `git status --short app` is empty | Pass | Manual confirmation supplied by user |
| Seccomp warning accepted locally | Accepted only for local development | Accepted in `25_oracle_mcp_docker_seccomp_acceptance.md` | Pass | Still requires production/hardening review |
| Build command location | First build must run from runtime folder | Required path: `ai/oracle/runtime` | Pass | Instructional rule only |
| Build-only command | First Docker action must build only | Required command: `docker compose build` | Pass | Do not run containers yet |

## First Build Rule

The first build must be run manually from:

```text
ai/oracle/runtime
```

Allowed first build command:

```text
docker compose build
```

This command is approved only for building the local placeholder image. It must not be combined with `up`, `run`, container start, scripts, automation, or application changes.

## Explicitly Prohibited Before Post-Build Validation

Do not run:

```text
docker compose up
docker run
docker compose run
```

Also do not:

- Start containers.
- Create MCP server code.
- Create scripts or automation.
- Mount `.env`.
- Add secrets.
- Modify `app/`.
- Expose ports.
- Use privileged mode.
- Change Docker Desktop settings as part of this build step.

## Post-Build Validation Commands

After the first build is manually executed, the next validation should inspect the build result without starting containers.

Suggested manual validation commands:

```text
docker image ls
docker compose config
docker compose ps
```

Expected safe outcomes:

- The local image exists.
- Compose config still shows the repository mount as read-only.
- Compose config still shows only `./output` as writable.
- Compose config does not show `.env`, `env_file`, secrets, ports, privileged mode, host networking, or host root mounts.
- `docker compose ps` should show no running service if no container was started.

Do not print secrets during validation.

## Blocking Issues

- None for the first local build-only step.

## Non-Blocking Notes

- Docker seccomp warning is accepted for local development only.
- Docker seccomp warning is not accepted for production by default.
- Production, hardened deployment, network exposure, ports, real Oracle credentials, wallets, and MCP server runtime still require separate review.

## Approved Next Action

- Manually run `docker compose build` from `ai/oracle/runtime`.
- Do not run `docker compose up`, `docker compose run`, `docker run`, or any container start command.
- After the build, create a separate post-build validation report.

## Final Decision

First build allowed next? YES

The first build is allowed only as a local build-only action using `docker compose build` from `ai/oracle/runtime`.
