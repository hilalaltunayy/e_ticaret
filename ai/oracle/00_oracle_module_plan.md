# 00 Oracle Module Plan

## Purpose

Define Oracle as the AI repository guide for this e-commerce project.

## Oracle Role

Oracle must act as:

- repository guide
- domain knowledge reader
- KB-aware advisor
- task and plan assistant
- safety checker before code changes
- future MCP tool provider

## Core Responsibilities

- Read Domain KB.
- Answer “where is this feature implemented?”
- Map user/system request to affected domains.
- Identify affected paths.
- Suggest required KB updates.
- Support task/ticket creation.
- Support plan creation from task.
- Check whether KB update is required.
- Check whether GitNexus metadata is complete.
- Never modify app code directly in guide mode.

## Inputs

| Input | Description | Required? | Example |
|-------|-------------|-----------|---------|
| `user_request` | Human or system request that needs repository guidance. | Yes | `Where is product pricing implemented?` |
| `task_id` | Optional GitNexus task identifier. | No | `GNX-1234` |
| `changed_paths` | Repository paths changed or proposed for change. | No | `app/Config/Routes.php` |
| `domain_context` | Known or suspected domain context. | No | `Product / Catalog` |
| `kb_files` | KB files available for Oracle review. | Yes | `ai/domain-kb/01_domain_index.md` |
| `repo_paths` | Repository paths Oracle may inspect or map. | Yes | `app/Models/ProductsModel.php` |
| `risk_level` | Risk level from task metadata or Oracle assessment. | No | `high` |

## Outputs

| Output | Description | Example |
|--------|-------------|---------|
| `affected_domains` | Domains likely affected by the request or changed paths. | `Auth`, `User / Role / Permission` |
| `affected_paths` | Repository paths likely relevant to the task. | `app/Config/Filters.php` |
| `relevant_kb_files` | KB documents Oracle recommends reading or updating. | `ai/domain-kb/03_security_filter_audit.md` |
| `task_draft` | Draft GitNexus task metadata. | `task_id`, title, domain, risk level |
| `plan_draft` | Draft task plan with steps and expected changes. | `Read Routes.php`, `update route matrix` |
| `risk_notes` | Safety, security, RBAC, schema, or route risk notes. | `Route change may require validation.` |
| `kb_update_required` | Whether KB update is required or likely required. | `true` |
| `validation_needed` | Whether validation is required before commit. | `true` |

## Oracle and Domain KB Relationship

- Oracle reads Domain KB.
- Oracle does not replace Domain KB.
- Domain KB remains the source of truth for repository domains, claims, route/security/schema baselines, GitNexus contract rules, and KB update policy.
- Oracle uses `ai/domain-kb/kb-manifest.yaml` for path-to-domain and path-to-KB mapping.
- Oracle should cite or reference relevant KB files when giving repository guidance.
- If Oracle finds a gap or drift, it should recommend KB review rather than silently rewriting repository facts.

## Oracle and GitNexus Relationship

- GitNexus manages task, plan, branch, commit, validation, and KB update linkage.
- Oracle helps create and review tasks/plans.
- Oracle can advise whether KB update is required.
- Oracle can check whether GitNexus metadata appears complete.
- Oracle should not commit code.
- Oracle should not become the task store; it should assist GitNexus rather than replace it.

## Future MCP Tools

Future tools to design later, not implement here:

- `repo_lookup`
- `domain_lookup`
- `route_lookup`
- `permission_lookup`
- `kb_impact_check`
- `task_create`
- `plan_create`
- `validation_check`
- `kb_update_required_check`

## Local Docker Direction

- Oracle MCP will eventually run locally in Docker.
- Docker is not implemented in this document.
- Docker will be used for isolation and repeatable local runtime.
- Secrets must not be committed to the repository.
- Repository may contain only `.env.example` or configuration templates.
- Real secrets must live in local `.env`, Docker secrets, or environment variables.

## Security Rules

- Never commit API keys, tokens, private keys, passwords, or certificates.
- Do not store real secrets in repo.
- Use `.env.example` for placeholders only.
- Add real `.env` to `.gitignore` when implementation starts.
- Oracle must operate read-only unless explicitly authorized.

## Out of Scope

- Actual MCP implementation
- Dockerfile creation
- API integration
- Secret creation
- GitNexus implementation
- Orchestrator implementation
- App code changes

## First Implementation Recommendation

- Start with documentation-only Oracle planning.
- Validate Oracle responsibilities.
- Then design local Docker MCP runtime.
- Then define tool schemas.
- Then implement minimal local MCP.

## Final Summary

This plan is ready for validation.

Oracle is defined as a KB-aware repository guide, not an implementation module. It should help humans and future systems understand domains, affected paths, KB impact, task planning, risk, and validation needs while keeping Domain KB as the source of truth and leaving GitNexus responsible for task and commit linkage.
