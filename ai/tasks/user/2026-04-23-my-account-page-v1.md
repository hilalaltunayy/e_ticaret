# Task: User Auth Flow (v1)

## Epic
user-frontend

## Status
planned

## Goal
Build the basic user authentication flow for the storefront.

This task covers registration, login, logout, forgot password, reset password, and verification-style screens where needed.

---

## Scope

- user registration screen
- user login screen
- logout flow
- forgot password screen
- reset password screen
- check mail / verification screen if needed
- user session integration with storefront

---

## Out of Scope

- admin login redesign
- secretary login redesign
- social login
- two-factor authentication
- advanced account security
- AI-based auth assistance

---

## Relevant Context

- ai/project.md
- ai/rules.md
- ai/architecture.md
- ai/current_state.md
- ai/backlog.md
- ai/decisions.md
- ai/epics/user-frontend.md
- ai/epics/rbac-security.md

---

## Expected Layers / Files

- routes
- auth controller
- DTO
- auth service
- user model
- views
- session/auth helpers if already used

---

## UI / Template

- primary source: BeAble Pro
- primary path: C:\code\e_ticaret\app\dist\pages
- template files:
  - login-v3.html
  - register-v2.html
  - forgot-password-v2.html
  - reset-password-v2.html
  - check-mail-v2.html
  - code-verification-v2.html

- usage rules:
  - use these auth templates as the base
  - adapt colors, spacing, and branding to the existing storefront
  - do not redesign auth UI from scratch unless no suitable section exists
  - do not break the existing admin / secretary authentication flow
  - user auth must visually feel connected to the storefront

---

## Constraints / Rules

- do not modify admin login unless explicitly required
- do not break existing role-based redirects
- user login must redirect to the storefront/user area
- admin and secretary must keep their own access behavior
- no business logic in controllers
- service layer must handle auth rules
- DTOs should be used for login/register inputs
- password handling must stay secure
- validation errors must be user-friendly

---

## Acceptance Criteria

- user can register
- user can log in
- user can log out
- invalid login attempts are handled safely
- user session works on storefront pages
- admin and secretary auth flows are not broken
- forgot/reset password screens exist at UI level or are prepared for integration

---

## Manual Test Steps

1. open user registration page
2. create a new user account
3. log out
4. log in with the created account
5. try invalid credentials
6. verify user is redirected correctly
7. verify admin login still works
8. verify secretary login still works
9. open forgot password screen
10. open reset/check-mail style screens if implemented

---

## Risks / Notes

- auth changes can break admin/secretary access
- route filters must remain strict
- password reset may need later backend/mail integration
- user role assignment must be correct

---

## Progress Log

- 2026-04-23: task created
