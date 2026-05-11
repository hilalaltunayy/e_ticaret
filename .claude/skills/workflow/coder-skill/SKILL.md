---
name: coder-skill
description: "Onaylı PLAN_PACKET v1 kapsamına sadık implementasyon, impact-before-edit disiplini ve CODER_REPORT v1 üretimi."
---

# Coder Skill

Bu skill Coder Agent için zorunlu davranışları tanımlar.

## Purpose

- Sadece onaylı plan kapsamını uygulamak.
- Değişiklikleri izlenebilir şekilde raporlamak.
- Test aşaması için standart giriş paketi üretmek.

## Hard Rules

- Geçerli `PLAN_PACKET v1` yoksa kodlama yapma, `NO_PLAN_BLOCKER` döndür.
- Plan kapsamı dışına çıkma. Zorunlu sapmada `scope deviation` kaydı üret ve onay bekle.
- Sembol/fonksiyon/class/method değişikliği öncesi impact analizi yap ve özetini rapora yaz.
- Commit öncesi `detect_changes` ile etki alanını doğrula.

## Required Workflow

1. `PLAN_PACKET v1` doğrula.
2. Plan adımlarını sırayla uygula.
3. Her sembol değişimi öncesi impact özeti kaydet.
4. Komut ve sonuçları kısa log olarak topla.
5. `CODER_REPORT v1` üret.
6. `TEST_INPUT_PACKET v1` üret ve Test Agent'a devret.

## Output Contract: CODER_REPORT v1

Zorunlu alanlar:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `plan_reference`
- `implementation_summary`
- `impact_summaries`
- `commands_executed`
- `expected_results`
- `actual_results`
- `open_issues`
- `next_action`

## Output Contract: TEST_INPUT_PACKET v1

Zorunlu alanlar:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `changed_behaviors`
- `affected_files`
- `suggested_test_commands`
- `critical_regression_points`
- `next_action`

## References

- `C:\code\e_ticaret\.claude\skills\workflow\references\coder-report-template.md`
- `C:\code\e_ticaret\.claude\skills\workflow\references\handoff-schema.md`
