# 14 Oracle MCP Gitignore Safety Plan

## Purpose

Plan a minimal safe root `.gitignore` before any Oracle MCP runtime implementation.

This document is planning-only. It does not create or edit `.gitignore`, does not read `.env`, does not create runtime files, and does not modify application code.

## 1. Current State

- Git CLI now works in Git CMD, according to user-provided context.
- `git status --short app` returned empty, according to user-provided context.
- `app/` has no visible changes, according to user-provided context.
- Docker Desktop engine is running, according to user-provided context.
- `docker info` works, according to user-provided context.
- Docker reports: `WARNING: daemon is not using the default seccomp profile`.
- Root `.env` exists.
- Root `.gitignore` does not exist.
- `ai/oracle/runtime/` does not exist.
- Runtime creation is still not allowed.

## 2. Why `.gitignore` Blocks Runtime

A root `.gitignore` is required before runtime implementation because local runtime work will introduce files that must never be committed, especially local environment files and runtime-generated data.

The current root `.env` increases the risk. Even if it is local-only, the repository currently lacks a root `.gitignore` that explicitly protects it.

Runtime implementation should not start until `.env` and local runtime outputs are protected by a reviewed ignore policy.

## 3. Minimal Required Ignore Entries

The minimal required entries are:

- `.env`
- `.env.*`
- `!.env.example`
- `writable/logs/*`
- `writable/session/*`
- `writable/debugbar/*`

These entries protect local secrets and common CodeIgniter writable runtime data while allowing a safe placeholder `.env.example`.

## 4. Entries That Must NOT Be Ignored

Do not ignore:

- `app/`
- `ai/oracle/*.md`
- `composer.json`
- `composer.lock`
- `public/`

Reason:

- `app/` is application source and must remain visible to Git.
- `ai/oracle/*.md` contains planning and validation documentation.
- `composer.json` and `composer.lock` define dependency state and must remain trackable.
- `public/` may contain project assets and should not be broadly hidden without a separate asset policy.

## 5. Review Notes for CodeIgniter Writable Files

CodeIgniter commonly uses `writable/` for logs, sessions, cache, debugbar data, and generated runtime files.

This plan proposes only a narrow ignore set:

- `writable/logs/*`
- `writable/session/*`
- `writable/debugbar/*`

Further `writable/` ignore rules should be reviewed separately before being added. Do not broadly ignore all of `writable/` unless the project policy confirms no tracked writable files are needed.

## 6. Docker/MCP Future Ignore Notes

- Future Oracle runtime local environment files should be ignored.
- Real runtime `.env` files must stay local.
- `.env.example` should remain trackable with placeholders only.
- Do not broadly ignore `ai/oracle/runtime/` yet.
- Runtime source files, Dockerfile, compose files, and README may need to be tracked later when implementation is explicitly approved.
- Generated runtime outputs can be ignored later with a narrow rule after the output directory is finalized.

## 7. Proposed `.gitignore` Content

```gitignore
# Local environment secrets
.env
.env.*
!.env.example

# CodeIgniter runtime data
writable/logs/*
writable/session/*
writable/debugbar/*
```

## 8. Final Decision

Is it safe to create `.gitignore` next? YES

Blocking issues:

- None for creating a minimal root `.gitignore` with the proposed entries.

Non-blocking notes:

- Docker seccomp warning should be reviewed before runtime implementation, but it does not block creating `.gitignore`.
- `.env` contents must not be printed or copied.
- `.env` must not be deleted.
- `.env` must not be committed.
- Runtime creation remains blocked until readiness validation is rerun and returns `READY`.

Approved next action:

- Create a minimal root `.gitignore` with the proposed entries only, if explicitly requested in the next task.
