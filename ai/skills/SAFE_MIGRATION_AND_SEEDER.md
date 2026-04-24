# Safe Migration and Seeder Skill

This skill defines how to safely create and modify database schema and seed data.

## Purpose

Prevent data loss and ensure repeatable, safe database changes.

## Migration Rules

- Do not drop or truncate tables unnecessarily
- Avoid destructive operations
- Do not modify old migrations unless required
- Create new additive migrations for changes
- Check table existence before creating
- Check field existence before adding columns
- Document risky changes clearly
- Be careful with foreign keys
- Write safe down() methods if rollback is needed
- Always assume production safety

## Seeder Rules

- Seeder must be idempotent (no duplicates on re-run)
- Use unique constraints for roles and permissions
- Never store plain text passwords
- Keep permission codes consistent
- Do not blindly delete existing data
- Separate admin, secretary, and user seed data

## Commands

php spark migrate
php spark db:seed SeederName

Rollback (use carefully):

php spark migrate:rollback

## Important Tables in This Project

Be careful when modifying:

- users
- roles
- permissions
- role_permissions
- user_permissions
- products
- orders
- page_versions
- block_instances
