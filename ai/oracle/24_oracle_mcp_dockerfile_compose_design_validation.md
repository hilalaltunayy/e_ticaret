# 24 Oracle MCP Dockerfile Compose Design Validation

## Purpose

Validate `23_oracle_mcp_dockerfile_compose_design.md` against Docker hardening and safety requirements before any Dockerfile or Compose file creation.

This is read-only validation. It does not create Dockerfile, Compose files, MCP code, scripts, automation, secrets, containers, images, or application changes.

## Validation Results

| Check | Expected | Actual | Status | Notes |
|------|----------|--------|--------|------|
| Non-root container rule | Future Dockerfile should use a non-root user. | Design requires creating and running as a non-root user. | Pass | Meets hardening requirement. |
| Read-only repository mount rule | Future Compose should mount repository read-only by default. | Design specifies repository mount as `/workspace/repo:ro`. | Pass | Meets default isolation requirement. |
| Approved writable output mount rule | Writable mount should be limited to approved Oracle outputs. | Design limits writable output to `/workspace/repo/ai/oracle/outputs:rw`. | Pass | Output directory must not contain secrets. |
| No host root mount rule | Compose must not mount host root or broad user home directories. | Design explicitly forbids host root, broad user home, and Docker config mounts. | Pass | Meets filesystem safety requirement. |
| No privileged container rule | Future container must not be privileged. | Design says do not use privileged containers or broad Linux capabilities. | Pass | Meets container safety requirement. |
| No secrets baked into image rule | Docker image must not include secrets. | Design forbids copying `.env`, API keys, wallets, private keys, certificates, tokens, or credentials into the image. | Pass | Meets secret safety requirement. |
| Placeholder-only env example rule | `.env.example` may contain placeholders only. | Design states `.env.example` may contain placeholders only. | Pass | Meets local secret policy. |
| `app/` read-only rule | App code must remain read-only and untouched. | Design says `app/` must remain untouched and repository mount is read-only by default. | Pass | Meets app safety requirement. |
| Docker seccomp warning handling | Warning must be reviewed before creation. | Design documents the warning and says Dockerfile/Compose creation remains blocked until warning is reviewed, resolved, or accepted. | Pass with notes | The warning itself remains unresolved. |
| Actual Dockerfile/Compose creation still blocked | Design must not permit immediate creation. | Design says Dockerfile/compose creation allowed after this design? `NO`. | Pass | Correctly blocks creation. |

## Runtime Folder Check

Current runtime folder contents:

- `README.md`

No Dockerfile, Compose file, MCP code, script, automation, or secret file is present in the runtime folder.

## Security Notes

- The design is safe for planning.
- The design does not authorize Dockerfile or Compose creation.
- The design preserves app read-only behavior.
- The design preserves no-secrets-in-image behavior.
- The design requires future validation before actual file creation.

## Remaining Issue

Docker seccomp warning remains unresolved or not explicitly accepted:

```text
WARNING: daemon is not using the default seccomp profile
```

This is not a design failure, because the design correctly keeps Dockerfile/Compose creation blocked. It remains a hardening decision that must be resolved or explicitly accepted before actual Dockerfile/Compose creation.

## Final Decision

Decision: PASS WITH NOTES

Dockerfile/compose creation allowed next? NO

Blocking issues:

- Docker seccomp warning is still unresolved or not explicitly accepted.
- Dockerfile/Compose creation has not yet been explicitly approved.

Non-blocking notes:

- Dockerfile/Compose design is complete enough for planning.
- Runtime folder remains minimal and contains only `README.md`.
- No implementation files were created by this validation.

Approved next action:

- Create a seccomp decision record or Docker hardening acceptance document next.
- Do not create Dockerfile or `docker-compose.yml` until that decision is recorded and explicitly approved.
