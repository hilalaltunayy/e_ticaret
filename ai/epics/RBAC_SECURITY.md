# Epic: RBAC and Security

## Goal
Establish a secure and strict authorization system across the application.

---

## Scope

- role system (admin, secretary, user)
- permission system
- route-level protection
- service-level protection
- authentication system

---

## Rules

- deny-by-default is applied
- UI hiding is not enough
- route + filter required
- critical actions must be protected in service layer
- secretary is never admin

---

## Security Areas

- auth validation
- session control
- permission checks
- ownership validation

---

## Success Criteria

- unauthorized access is blocked
- role-based access works correctly
- system is securely isolated
