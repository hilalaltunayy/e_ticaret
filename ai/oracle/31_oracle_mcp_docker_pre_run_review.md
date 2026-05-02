# 31 Oracle MCP Docker Pre-Run Review

## Purpose

Review whether the first local Oracle MCP placeholder container run is safe after the successful Docker build validation.

This document is review-only. It does not run containers, modify Dockerfile, modify Compose, create MCP code, create scripts, create automation, read secrets, or modify application files.

## Current Context

- `ai/oracle/30_oracle_mcp_docker_post_build_validation.md` returned `PASS`.
- Docker image exists: `runtime-oracle-mcp-runtime:latest`.
- No `oracle-mcp-runtime` container is currently running.
- Dockerfile command is a harmless placeholder echo.
- `compose.yaml` mounts the project read-only.
- `compose.yaml` has only one writable mount: `ai/oracle/runtime/output/`.
- `compose.yaml` does not use `.env`, `env_file`, secrets, ports, privileged mode, or host networking.

## 1. Placeholder Run Purpose

The first placeholder run exists only to verify that the built local Docker image can start, execute the harmless placeholder command, and exit cleanly.

It must not validate MCP behavior, Oracle connectivity, repository mutation, credential access, networking, ports, or application runtime behavior.

## 2. Exact Allowed Command For Next Step

The only allowed next runtime command is:

```text
docker compose run --rm oracle-mcp-runtime
```

This command must be run from:

```text
C:\code\e_ticaret\ai\oracle\runtime
```

No other Docker run command is approved by this review.

## 3. Why This Command Is Allowed

`docker compose run --rm oracle-mcp-runtime` is allowed for the first placeholder run because:

- It targets only the approved `oracle-mcp-runtime` service.
- The Dockerfile command only prints a harmless placeholder message.
- `--rm` removes the temporary container after it exits.
- The repository mount is read-only.
- The only writable mount is the approved runtime output directory.
- No `.env`, `env_file`, or secrets are mounted.
- No ports are exposed.
- No privileged mode is configured.
- No MCP server code exists.
- No Oracle credential or wallet file exists in the runtime.

## 4. Why `docker compose up` Is Still Not Allowed

`docker compose up` is still not allowed because:

- It is service-oriented and may create longer-lived container state.
- It is unnecessary for a one-shot placeholder command.
- It can keep containers running if the command changes later.
- It is not needed until a real MCP server lifecycle is designed and validated.
- The current runtime is only a placeholder, not an MCP service.

## 5. Expected Output

Expected command output:

```text
Oracle MCP runtime placeholder
```

Any output that includes secrets, credentials, unexpected repository writes, network server startup, Oracle connection attempts, or MCP server startup must be treated as a stop condition.

## 6. Expected Side Effects

Expected safe side effects:

- A temporary container may be created.
- The temporary container should be removed after the command exits because `--rm` is used.
- No `app/` writes should occur.
- No `.env` access should occur.
- No ports should be opened.
- No MCP server should start.
- No Oracle connection should be attempted.
- No files should be generated except possible harmless Docker-managed build/run metadata outside the repository.

## 7. Stop Conditions

Stop immediately if any of the following occurs:

- The command attempts to read or print `.env`.
- The command prints secrets, credentials, tokens, keys, certificates, or wallet content.
- The command modifies `app/`.
- The command starts a network service.
- The command exposes ports.
- The command attempts Oracle connectivity.
- The command starts MCP server logic.
- The command creates scripts, automation, or runtime code.
- The command fails because the read-only filesystem blocks an unexpected write outside `/tmp` or the approved output mount.
- The command leaves a running `oracle-mcp-runtime` container.
- The command requires `privileged` mode or host networking.

## 8. Post-Run Validation Checklist

After the placeholder run, create a separate post-run validation report and check:

| Check | Expected Result |
|------|-----------------|
| Placeholder output | Output is exactly or effectively `Oracle MCP runtime placeholder` |
| Exit behavior | Command exits cleanly |
| Temporary container removal | No `oracle-mcp-runtime` container remains running |
| No `app/` changes | `app/` remains unchanged |
| No `.env` access | No `.env` content was read or printed |
| No ports | No ports were exposed |
| No secrets | No secrets were printed |
| No MCP logic | No MCP server code ran |
| No Oracle logic | No Oracle connection was attempted |
| Runtime files | Dockerfile, compose.yaml, README, and output/.gitkeep remain unchanged |
| Output directory | No unexpected generated files exist in `ai/oracle/runtime/output/` |

## Blocking Issues

- None for the first local placeholder run.

## Approved Next Action

- Run only `docker compose run --rm oracle-mcp-runtime` from `C:\code\e_ticaret\ai\oracle\runtime`.
- Do not run `docker compose up`.
- Do not run `docker run`.
- Do not mount secrets.
- Do not modify `app/`.
- After the run, create a separate post-run validation report.

## Final Decision

First placeholder run allowed next? YES

The first placeholder run is allowed only with the exact command:

```text
docker compose run --rm oracle-mcp-runtime
```
