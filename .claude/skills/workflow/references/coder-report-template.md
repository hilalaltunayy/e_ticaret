# coder-report-template.md

## CODER_REPORT v1

### Metadata
```yaml
id: CODER-YYYYMMDD-XXX
source_agent: Coder Agent
target_agent: Test Agent
plan_reference: PLAN-YYYYMMDD-XXX
status: CODING_DONE
```

### Scope
- Approved scope implemented:
- Out-of-scope requests encountered:

### Artifacts
- Changed files:
- Updated symbols:
- Generated assets:

### Acceptance Criteria Mapping
1. Criterion:
- Implementation evidence:
- Status: Met/Not Met

### Implementation Summary
- Change group 1:
- Change group 2:

### Impact Summaries (Before Symbol Edits)
- Symbol:
- Direct callers:
- Affected processes:
- Risk level:

### Commands Executed
```bash
# command
# output-summary
```

### Expected Results
- 

### Actual Results
- 

### Open Issues / Known Risks
- 

### Scope Deviation (if any)
- Deviation:
- Reason:
- Proposed mini-plan:
- Approval required: Yes/No

### Next Action
- Emit `TEST_INPUT_PACKET v1` and handoff to Test Agent.

---

## TEST_INPUT_PACKET v1

### Metadata
```yaml
id: TESTIN-YYYYMMDD-XXX
source_agent: Coder Agent
target_agent: Test Agent
status: READY_FOR_TEST
```

### Scope
- Behavior changes under test:

### Artifacts
- Changed files:
- Build outputs (if any):

### Acceptance Criteria
1. 
2. 

### Risks
- 

### Suggested Test Commands
```bash
# prioritized commands
```

### Critical Regression Points
- 

### Next Action
- Test Agent executes and returns `TEST_REPORT v1`.
