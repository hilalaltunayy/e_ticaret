# test-verification-template.md

## TEST_REPORT v1

### Metadata
```yaml
id: TEST-YYYYMMDD-XXX
source_agent: Test Agent
target_agent: Final Reporter
decision: PASS|FAIL|BLOCKED
status: TEST_VERIFIED
```

### Scope
- Verified behaviors:
- Non-verified areas:

### Artifacts
- Inputs used (`CODER_REPORT v1`, `TEST_INPUT_PACKET v1`):
- Logs, screenshots, traces:

### Acceptance Criteria Verification
1. Criterion:
- Evidence:
- Status: Met/Not Met

### Tests Executed
```bash
# command + short result
```

### Result Summary
- Passed:
- Failed:
- Skipped:

### Failures
- Case:
- Error:
- Impact:

### Reproduction Steps
1. 
2. 
3. 

### Known Risks
- 

### Confidence
- High / Medium / Low
- Rationale:

### Next Action
- PASS: emit `FINAL_STATUS v1`.
- FAIL/BLOCKED: emit `CODER_FEEDBACK v1`.

---

## CODER_FEEDBACK v1

### Metadata
```yaml
id: FEEDBACK-YYYYMMDD-XXX
source_agent: Test Agent
target_agent: Coder Agent
decision: FAIL|BLOCKED
```

### Scope
- Affected feature/scope:

### Artifacts
- Failing tests:
- Logs / stack traces:

### Acceptance Criteria Impact
- Broken criterion(s):

### Issue Summary
- 

### Reproduction Steps
1. 
2. 
3. 

### Expected Behavior
- 

### Actual Behavior
- 

### Logs or Stacktrace
```text
...
```

### Priority
- P0 / P1 / P2 / P3

### Blocker Type (for BLOCKED)
- env / dependency / data / permission / other

### Next Action
- Return to Coder Agent for fix or unblock actions.
