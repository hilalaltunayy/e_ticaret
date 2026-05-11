# planning-template.md

## PLAN_PACKET v1

### Metadata
```yaml
id: PLAN-YYYYMMDD-XXX
source_agent: Planning Agent
target_agent: Coder Agent
status: PLANNED_APPROVED
```

### Scope
- In-scope:
- Out-of-scope:

### Problem Summary
- Current state:
- Desired state:
- Constraints:

### Artifacts
- Candidate files:
- Candidate symbols:
- Related flows/processes:

### Acceptance Criteria
1. 
2. 
3. 

### Risks
- Risk:
- Impact:
- Mitigation:

### Technical Approach
- Step 1:
- Step 2:
- Step 3:

### TEST_EXPECTATIONS
- Functional scenarios:
- Regression areas:
- Success threshold:

### GitNexus Evidence
- Query evidence:
- Context evidence:
- Impact evidence:
- Targeted file-read note (if any):

### GITNEXUS_FIRST_EVIDENCE
- query:
- context:
- impact:
- direct_read_fallback_note:

### WAITING_FOR_USER_APPROVAL
- required: true
- approval_status: PENDING
- blocker: "Do not start coding before explicit user approval."

### Next Action
- Handoff to Coder Agent with `PLAN_PACKET v1`.

## Planning-Only Enforcement (Additive)

- If user asks to avoid file changes/code changes or asks to see plan first, emit only `PLAN_PACKET v1`.
- In that case `WAITING_FOR_USER_APPROVAL.approval_status` must remain `PENDING`.
- Move to Coder phase only after explicit approval from user.
