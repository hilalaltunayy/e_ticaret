# Epic: Secretary Panel

## Goal
Create a permission-controlled subset of the admin panel for secretary users.

---

## Scope

- permission-based access
- limited management screens
- review moderation
- limited order operations
- shipment updates

---

## Rules

- secretary has no access by default
- access is granted by admin
- backend protection is mandatory
- UI hiding is not sufficient

---

## Behavior

- admin enables → secretary sees
- admin disables → secretary cannot access

---

## Success Criteria

- secretary only accesses allowed areas
- admin control works correctly
- no permission violations
