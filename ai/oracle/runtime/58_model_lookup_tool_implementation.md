# 58 model_lookup Tool Implementation

## Files Created/Updated

- `ai/oracle/runtime/tools/model_lookup.py`
- `ai/oracle/runtime/registry.py`
- `ai/oracle/runtime/server.py`
- `ai/oracle/runtime/README.md`

## Implementation Summary

- Implemented `model_lookup` as a read-only text inspection tool.
- The tool reads only:
  - `app/Models/`
  - `app/Database/Migrations/`
  - `app/Database/Seeds/`
- The tool does not execute PHP.
- The tool does not instantiate models.
- The tool does not run migrations or seeders.
- The tool does not access the database.
- The tool does not read `.env` or secrets.
- The tool does not modify `app/`, models, migrations, or seeders.
- It supports model class, table name, field name, and model keyword searches.
- It returns source file, line number, matched text, detected type, model class, table name, field, related migration/seeder, risk hint, confidence, max result limit, and status when detectable.
- It safely returns `no_results` when no evidence matches.

## Local Test Command

```text
python ai/oracle/runtime/server.py
```

Additional focused local test command:

```text
python -c "import sys; sys.path.insert(0, 'ai/oracle/runtime'); from tools.model_lookup import model_lookup; queries=['UserModel','ProductsModel','OrderModel','RolePermissionModel','users','role_permissions','allowedFields','useSoftDeletes','uuid','missing_model_xyz']; [print(q, model_lookup(q, repo_root='.', max_results=3)['status'], model_lookup(q, repo_root='.', max_results=3)['result_count']) for q in queries]"
```

## Expected Output

The runtime output should include:

```text
Registered tools:
- repo_file_lookup (implemented)
- route_lookup (implemented)
- model_lookup (implemented)
- controller_lookup (implemented)
- permission_lookup (implemented)
- filter_lookup (implemented)
Sample model_lookup:
- query: UserModel
  status: success
- query: RolePermissionModel
  status: success
- query: allowedFields
  status: success
```

The focused test should show successful matches for known model/schema terms and `no_results` for `missing_model_xyz`.

## Test Status

Local Python runtime validation passed with:

```text
python ai/oracle/runtime/server.py
```

Focused query validation passed for:

- `UserModel`
- `ProductsModel`
- `OrderModel`
- `RolePermissionModel`
- `users`
- `role_permissions`
- `allowedFields`
- `useSoftDeletes`
- `uuid`
- `missing_model_xyz`
- empty query handling

Known model/schema terms returned `success`, `missing_model_xyz` returned `no_results`, and an empty query returned `error`.

Docker was not run in this implementation task.

## Suggested Commit Message

```text
docs(oracle): add read-only model lookup runtime tool
```

If using GitNexus task IDs later, prefix the commit message with the relevant task id.

## Domain KB Update Candidate Notes

Later Domain KB updates should consider:

- `ai/domain-kb/01_domain_index.md`: note that Oracle runtime now has model lookup support.
- `ai/domain-kb/10_schema_model_matrix.md`: use `model_lookup` evidence to improve model-to-table and migration-to-domain mapping.
- `ai/domain-kb/09_claim_id_registry.md`: add stable claim IDs if model/schema findings become automation-relevant.
- `ai/domain-kb/kb-manifest.yaml`: if Oracle runtime files are tracked by KB drift policy, include `ai/oracle/runtime/tools/model_lookup.py` as an Oracle module watched path.

No Domain KB changes were made in this task.

## Final Decision

Ready for `model_lookup` docker test? YES
