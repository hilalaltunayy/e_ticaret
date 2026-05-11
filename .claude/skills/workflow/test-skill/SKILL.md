---
name: test-skill
description: "Coder çıktısını doğrulayan, PASS/FAIL/BLOCKED kararı veren ve standart feedback üreten test ajanı sözleşmesi."
---

# Test Skill

Bu skill Test Agent için zorunlu davranışları tanımlar.

## Purpose

- Coder çıktısını kanıt odaklı doğrulamak.
- Tekil karar vermek: `PASS`, `FAIL`, `BLOCKED`.
- FAIL/BLOCKED durumunda standart geri bildirim üretmek.

## Hard Rules

- `CODER_REPORT v1` yoksa `BLOCKED: missing coder report`.
- `PASS_WITH_RISK` kullanma. Risk varsa raporda `Known Risks` altında belirt.
- FAIL/BLOCKED çıktısında `CODER_FEEDBACK v1` üretmeden akışı kapatma.

## Test Execution Strategy

1. Repo standart test komutları.
2. Plan hedeflerine özel dar kapsam testler.
3. Kritik regresyon kontrolleri.
4. Karar ve güven seviyesi.

## Output Contract: TEST_REPORT v1

Zorunlu alanlar:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `tests_executed`
- `result_summary`
- `failures`
- `reproduction_steps`
- `decision` (`PASS|FAIL|BLOCKED`)
- `confidence`
- `known_risks`
- `next_action`

## Output Contract: CODER_FEEDBACK v1

Zorunlu alanlar:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `issue_summary`
- `reproduction_steps`
- `expected_behavior`
- `actual_behavior`
- `logs_or_stacktrace`
- `priority`
- `blocker_type` (`env|dependency|data|permission|other` when BLOCKED)
- `next_action`

## References

- `C:\code\e_ticaret\.claude\skills\workflow\references\test-verification-template.md`
- `C:\code\e_ticaret\.claude\skills\workflow\references\handoff-schema.md`
