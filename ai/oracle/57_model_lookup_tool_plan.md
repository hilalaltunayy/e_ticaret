# 57 model_lookup Tool Plan

## Purpose

Plan a read-only `model_lookup` tool for the Oracle MCP runtime.

The tool should inspect model, migration, and seeder files safely without modifying application code. It should help trace model classes to table names, allowed fields, validation rules, return types, soft-delete behavior, UUID/base model behavior, migration fields, and seeder references.

This is a planning document only. It does not implement tool logic, modify runtime files, modify `app/`, run Docker, access the database, read `.env`, or read secrets.

## Orchestration Context

This tool belongs to the Oracle MCP Server expansion flow:

1. Plan
2. Implementation
3. Test
4. Commit candidate
5. Domain KB update candidate

No Domain KB changes are made in this step. No Git commits are created in this step. No application behavior is modified.

## Initial Read-Only Sources

The first implementation may inspect these sources as read-only text:

- `app/Models/`
- `app/Database/Migrations/`
- `app/Database/Seeds/`

No PHP execution is allowed. No database connection is allowed. Model, migration, and seeder files must be parsed as text only.

## Tool Contract

Tool name:

```text
model_lookup
```

Tool purpose:

- Search model classes, table names, allowed fields, validation rules, return types, soft-delete settings, UUID/base model references, migration fields, and seeder references.
- Help connect model behavior to database schema evidence.
- Return limited, source-anchored evidence for faster Product, Order, User, Role, Permission, Category, Author, Type, and base UUID model review.

Tool access level:

```text
read_only_model_sources
```

Allowed behavior:

- Read approved model, migration, and seeder source files as text.
- Search model class names.
- Search table names.
- Search field names.
- Search CodeIgniter model properties such as `$table`, `$primaryKey`, `$allowedFields`, `$validationRules`, `$returnType`, and `$useSoftDeletes`.
- Search migration field definitions and seeder references.
- Return limited, source-anchored evidence.

Prohibited behavior:

- Do not write, delete, rename, move, format, or modify files.
- Do not execute PHP.
- Do not bootstrap CodeIgniter.
- Do not instantiate models.
- Do not run migrations or seeders.
- Do not query or write the database.
- Do not read `.env`.
- Do not read secrets, wallets, keys, tokens, certificates, or credentials.
- Do not modify models, migrations, seeders, services, controllers, filters, routes, views, or config files.

## Supported Query Types

| Input Type | Description | Example |
|-----------|-------------|---------|
| Model class name | Fully or partially matching model class | `UserModel`, `ProductsModel`, `OrderModel`, `RolePermissionModel` |
| Table name | Database table name or partial table name | `users`, `products`, `orders`, `role_permissions`, `user_permissions` |
| Field name | Column or model field name | `role`, `permission_id`, `status`, `deleted_at`, `created_at` |
| Model keyword | CodeIgniter model property or behavior keyword | `allowedFields`, `validationRules`, `returnType`, `useSoftDeletes`, `table`, `primaryKey`, `uuid` |

## Input Format

Proposed input fields:

| Field | Type | Required | Description | Example |
|------|------|----------|-------------|---------|
| `query` | string | Yes | Model class, table name, field name, or keyword | `ProductsModel` |
| `search_mode` | string | No | `auto`, `model`, `table`, `field`, `migration`, `seeder`, or `keyword` | `auto` |
| `max_results` | integer | No | Maximum evidence rows | `20` |
| `include_migrations` | boolean | No | Whether migration sources should be inspected | `true` |
| `include_seeders` | boolean | No | Whether seeder sources should be inspected | `true` |

Input rules:

- `query` must not be empty.
- `max_results` should default to `20`.
- `max_results` should have a hard maximum such as `100`.
- Query text must be treated as text only.
- Absolute source overrides must not be accepted.
- The tool must not accept arbitrary filesystem paths outside the approved source list.

## Output Format

Proposed output fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `status` | string | `success`, `no_results`, `partial`, `needs_review`, or `error` | `success` |
| `query` | string | Original safe query | `role_permissions` |
| `matches` | array | Source-anchored model/schema evidence | See match fields below |
| `result_count` | integer | Number of returned matches | `5` |
| `truncated` | boolean | Whether results were limited | `false` |
| `warnings` | array | Non-fatal uncertainty notes | `[]` |
| `sources` | array | Source roots inspected | `["app/Models/", "app/Database/Migrations/"]` |

Proposed match fields:

| Field | Type | Description | Example |
|------|------|-------------|---------|
| `source_file` | string | Evidence source path | `app/Models/RolePermissionModel.php` |
| `line_number` | integer/null | Line number when available | `14` |
| `matched_text` | string | Safe matched line or normalized text | `protected $table = 'role_permissions';` |
| `detected_type` | string | `model_class`, `table_name`, `allowed_field`, `validation_rule`, `return_type`, `relationship_hint`, `migration_field`, or `seeder_reference` | `table_name` |
| `model_class` | string/null | Model class when detectable | `RolePermissionModel` |
| `table_name` | string/null | Table name when detectable | `role_permissions` |
| `field` | string/null | Field name when detectable | `permission_id` |
| `related_migration` | string/null | Migration file when detectable | `app/Database/Migrations/2025...CreateRolePermissions.php` |
| `related_seeder` | string/null | Seeder file when detectable | `app/Database/Seeds/InitialAuthSeeder.php` |
| `risk_hint` | string/null | Static risk hint when detectable | `Model table reference found but migration field evidence was not confirmed.` |
| `confidence` | string | `high`, `medium`, or `low` | `medium` |

Safe no-result response:

```json
{
  "status": "no_results",
  "query": "missing_model",
  "matches": [],
  "result_count": 0,
  "truncated": false,
  "warnings": [],
  "sources": []
}
```

## Safety Boundaries

`model_lookup` must:

- Be read-only.
- Read only approved model, migration, and seeder source files.
- Use relative source paths only.
- Never execute PHP.
- Never instantiate models.
- Never bootstrap CodeIgniter.
- Never run migrations or seeders.
- Never query or write the database.
- Never read `.env`.
- Never read secret-like files.
- Never modify `app/`.
- Never modify Docker files or runtime configuration unless explicitly requested in the later implementation task.
- Enforce result limits.
- Return `needs_review` when source evidence is ambiguous.

## Ignored Folders

The implementation should ignore unsafe or irrelevant folders:

```text
.git
vendor
node_modules
writable
public/uploads
ai/oracle/output
ai/oracle/runtime/output
```

## Result Limits

- Default `max_results`: `20`.
- Hard maximum `max_results`: `100`.
- Stop collecting once the hard limit is reached.
- Return `truncated: true` when results are limited.

## Parsing Strategy

Initial implementation should use conservative text parsing:

- Parse `app/Models/**/*.php` for model declarations:
  - `class UserModel`
  - `class ProductsModel`
  - `class RolePermissionModel`
- Detect table configuration:
  - `protected $table = 'users';`
  - `protected $table = 'role_permissions';`
- Detect primary key and UUID/base model hints:
  - `protected $primaryKey`
  - `uuid`
  - `BaseModel`
  - `BaseUuidModel`
- Detect allowed fields:
  - `protected $allowedFields = [...]`
  - individual field strings inside the property block
- Detect validation rules:
  - `protected $validationRules = [...]`
  - field names inside the rule array
- Detect return types:
  - `protected $returnType`
  - DTO/entity class strings
- Detect soft deletes:
  - `protected $useSoftDeletes = true`
  - `deleted_at`
- Detect relationship hints:
  - model names ending in `PermissionModel`
  - fields such as `user_id`, `role_id`, `permission_id`, `product_id`, `order_id`, `category_id`
  - service/controller references are out of scope for the first version unless they appear inside model files
- Parse `app/Database/Migrations/**/*.php` for table and field evidence:
  - `$this->forge->addField(...)`
  - `$this->forge->createTable('products')`
  - field names inside migration arrays
- Parse `app/Database/Seeds/**/*.php` for seeder references:
  - table inserts
  - model usage
  - permission/role/user/product/category seed references

Confidence rules:

- `high`: Direct model class, `$table`, `$allowedFields`, `$validationRules`, or migration table/field match.
- `medium`: Seeder or migration reference appears related but does not fully prove model ownership.
- `low`: Keyword appears in a source file but the domain relationship is unclear.
- `needs_review`: Dynamic table names, inherited behavior, indirect model references, or field-level schema/model mismatch.

## Known Limitations

- Text parsing cannot prove runtime behavior.
- Inherited model properties may be missed unless parent classes are inspected.
- Dynamic table names, dynamic validation rules, or trait-provided fields may require manual review.
- A model can reference a table that is created by a migration with a different naming pattern.
- Seeder evidence can confirm inserted data shape but not runtime permissions or business behavior.
- Field-level migration/model mismatch requires a later schema extractor or manual schema audit.
- Relationships are hints only unless foreign keys or explicit model methods are directly present.

## Test Examples

Required example searches:

| Query | Expected Focus |
|------|----------------|
| `UserModel` | Find user model class, table, allowed fields, and validation hints |
| `ProductsModel` | Find product model class, product table, allowed fields, and soft-delete hints |
| `OrderModel` | Find order model class, order table, allowed fields, and related migrations |
| `RolePermissionModel` | Find role-permission model class and `role_permissions` table evidence |
| `users` | Find model table, migration table, and seeder references for users |
| `role_permissions` | Find RBAC table/model/migration/seeder evidence |
| `allowedFields` | Find model allowed field declarations |
| `useSoftDeletes` | Find soft-delete behavior in models |
| `uuid` | Find UUID/base model behavior hints |

Additional useful examples:

- `permission_id`
- `status`
- `deleted_at`
- `created_at`
- `category_id`
- `product_id`
- `returnType`
- `validationRules`
- `primaryKey`

## Validation Checklist

Before implementation, validate:

| Check | Expected Result |
|------|-----------------|
| Read-only behavior | No writes, deletes, renames, moves, formats, or modifications |
| Source scope | Reads only `app/Models/`, `app/Database/Migrations/`, and `app/Database/Seeds/` |
| No PHP execution | Parses text only |
| No DB access | Does not connect to or query database |
| No secrets | Does not read `.env` or secret-like files |
| Model evidence | Can detect model classes, table names, allowed fields, validation rules, return types, and soft-delete hints |
| Schema evidence | Can detect migration table and field evidence when present |
| Seeder evidence | Can detect seeder references when present |
| Output evidence | Includes source file, line number, matched text, detected type, and related fields |
| Result limits | Enforces default and hard maximum result limits |
| No-results safe | Returns `no_results` without error |
| Uncertainty safe | Returns `needs_review` or low confidence for ambiguous evidence |

## Suggested Future Commit Message

```text
docs(oracle): plan read-only model lookup runtime tool
```

If using GitNexus task IDs later, prefix the commit message with the relevant task id.

## Domain KB Update Candidate Notes

Later Domain KB updates should consider:

- `ai/domain-kb/01_domain_index.md`: note that Oracle runtime plans model/schema lookup support.
- `ai/domain-kb/10_schema_model_matrix.md`: use future `model_lookup` evidence to improve model-to-table and migration-to-domain mapping.
- `ai/domain-kb/09_claim_id_registry.md`: add stable claim IDs if model/schema findings become automation-relevant.
- `ai/domain-kb/kb-manifest.yaml`: if Oracle runtime files are tracked by KB drift policy, include the future `ai/oracle/runtime/tools/model_lookup.py` path as an Oracle module watched path.

No Domain KB changes are made in this task.

## Final Decision

Ready for `model_lookup` implementation? YES
