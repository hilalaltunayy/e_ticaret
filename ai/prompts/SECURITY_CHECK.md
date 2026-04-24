# Security Check Prompt

Use this prompt when asking Codex to perform a security-focused review.

## Prompt Template

You are performing a security review for the local CodeIgniter 4 e-commerce project.

Read these files first:

- ai/README.md
- ai/project.md
- ai/architecture.md
- ai/rules.md
- ai/current_state.md
- ai/decisions.md
- ai/skills/RBAC_PERMISSION_FILTER.md
- ai/skills/SAFE_MIGRATION_AND_SEEDER.md
- ai/skills/AUDIT_LOG_RULES.md

Review the selected files or feature for:

1. Authentication bypass
2. Authorization bypass
3. Missing permission checks
4. Missing ownership checks
5. CSRF risks
6. XSS risks
7. Unsafe file upload risks
8. Unsafe migration/seeder behavior
9. Sensitive data exposure
10. Missing audit log for critical actions

Return:

1. Critical issues
2. Medium issues
3. Low issues
4. Suggested fixes
5. Manual security test steps
