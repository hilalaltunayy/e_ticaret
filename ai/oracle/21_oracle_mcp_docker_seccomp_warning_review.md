# 21 Oracle MCP Docker Seccomp Warning Review

## Purpose

Review the Docker seccomp warning before any Oracle MCP Dockerfile or Docker Compose implementation starts.

This is a read-only review. It does not modify Docker Desktop settings, start containers, build images, create Docker files, create MCP code, create scripts, create automation, create secrets, or modify application code.

## 1. What the Warning Means

Warning:

```text
WARNING: daemon is not using the default seccomp profile
```

Beginner-friendly meaning:

- Docker runs containers with security rules.
- One of those security layers is called `seccomp`.
- Seccomp limits which low-level system calls a container can make.
- Docker normally uses a default seccomp profile to reduce container risk.
- This warning means the Docker daemon is not using the usual default seccomp profile.

This does not automatically mean Docker is broken, but it means the security posture should be reviewed before relying on Docker for hardened runtime isolation.

## 2. Why It Matters for Docker Hardening

Oracle MCP runtime is planned to use Docker for local isolation.

If Docker is not using the default seccomp profile, container isolation may not match the expected default Docker security baseline.

This matters before Dockerfile or Compose hardening because:

- hardening assumptions may be wrong;
- container restrictions may be weaker or customized;
- security documentation should reflect the actual Docker daemon behavior;
- future runtime validation should confirm whether this warning is acceptable for local development.

## 3. Does It Block Runtime README/Folder Work?

No.

The warning does not block:

- creating `ai/oracle/runtime/`;
- creating `ai/oracle/runtime/README.md`;
- writing planning or validation documentation.

Reason:

- README/folder work does not run containers.
- README/folder work does not depend on Docker isolation.
- No Dockerfile, Compose file, or runtime process is created at this stage.

## 4. Does It Block Dockerfile/Compose Creation?

Yes, for now.

Dockerfile or Compose creation should wait until the seccomp warning is reviewed and documented as acceptable, resolved, or explicitly accepted as a local-development limitation.

Reason:

- Dockerfile/Compose design should not assume the default security profile when Docker reports otherwise.
- Runtime hardening should be based on the actual Docker daemon security state.

## 5. Safe Manual Checks User Can Perform

Run these manually in the terminal where Docker works:

```text
docker info
```

Check whether the seccomp warning still appears.

Optional read-only checks:

```text
docker version
docker compose version
```

If comfortable with Docker Desktop settings, review Docker Desktop security or engine configuration visually.

Important:

- Do not change Docker Desktop settings as part of this review unless there is a separate explicit task.
- Do not start containers.
- Do not build images.

## 6. What NOT To Do

- Do not create Dockerfile.
- Do not create `docker-compose.yml`.
- Do not create MCP server code.
- Do not create scripts.
- Do not create automation.
- Do not create or read secrets.
- Do not modify Docker Desktop settings in this task.
- Do not start containers.
- Do not build images.
- Do not make the repository writable in Docker.
- Do not modify `app/`.

## 7. Recommended Next Action

Recommended next action:

- Create a Docker hardening review plan before Dockerfile/Compose creation.

That plan should decide whether the seccomp warning is:

- acceptable for local-only development;
- caused by a known Docker Desktop configuration;
- resolvable before implementation;
- a blocker for any container execution.

Until then, continue only with documentation, planning, or non-Docker runtime folder README work.

## 8. Final Decision

Dockerfile/compose creation allowed now? NO

Blocking issues:

- Docker daemon reports it is not using the default seccomp profile.
- Docker hardening assumptions have not been reviewed yet.

Non-blocking notes:

- The warning does not block the already completed runtime folder and README work.
- The warning does not block documentation-only planning.
- The warning must be reviewed before Dockerfile or Compose creation.
