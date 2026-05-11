---
name: plan-skill
description: "Planlama ajanı için GitNexus-first analiz, kapsam tanımı ve PLAN_PACKET v1 üretim sözleşmesi."
---

# Plan Skill

Bu skill Planning Agent için zorunlu davranışları tanımlar.

## Purpose

- Implementasyon öncesi kapsamı netleştirmek.
- GitNexus-first bağlam toplamak.
- Coder için uygulanabilir, sınırları belirli `PLAN_PACKET v1` üretmek.

## Non-Goals

- Kod yazmak.
- Dosya mutasyonu yapmak.
- Plan dışı teknik çözüm dayatmak.

## Required Workflow

1. Problem ve hedefi netleştir.
2. GitNexus-first inceleme yap:
- `query` ile akış keşfi.
- `context` ile kritik sembol ilişkileri.
- Gerekirse `impact` ile riskli değişim alanları.
3. GitNexus sonucu yetersizse hedefli dosya okumasına geç ve şu notu ekle:
- `GitNexus yetersiz kaldı, hedefli dosya okumasına geçildi.`
4. Uygulama sırasını, riskleri, test stratejisini oluştur.
5. `PLAN_PACKET v1` üret ve Coder'a devret.

## Planning Trigger Rules (Mandatory)

- Aşağıdaki iş tipleri daima delivery-class kabul edilir ve ilk çıktı Planning phase olmalıdır:
  - development
  - UI change
  - bugfix
  - refactor
  - migration
  - integration
  - cleanup
  - test
- Aşağıdaki ifadeler plan-only trigger kabul edilir:
  - "şimdilik dosya değiştirme"
  - "kod yazma"
  - "önce ne yapacağını söyle"
  - "planını sun"
  - "implementasyon başlatma"
- Plan-only durumda serbest metin plan verilmez; çıktı yalnızca `PLAN_PACKET v1` olmalıdır.
- Kullanıcı açık onay vermeden Coder aşamasına geçilemez.

## Output Contract: PLAN_PACKET v1

Aşağıdaki başlıklar sabittir ve eksiksiz olmalıdır:

- `id`
- `source_agent`
- `target_agent`
- `scope`
- `out_of_scope`
- `artifacts`
- `acceptance_criteria`
- `risks`
- `test_expectations`
- `implementation_steps`
- `gitnexus_evidence`
- `next_action`

## Required Normalized Keys (Enforced)

- Plan çıktısında aşağıdaki alanlar zorunludur ve büyük/küçük harf duyarlı isimlerle yazılmalıdır:
  - `TEST_EXPECTATIONS`
  - `GITNEXUS_FIRST_EVIDENCE`
  - `WAITING_FOR_USER_APPROVAL`
- `WAITING_FOR_USER_APPROVAL` değeri planning çıktısında onay bekleme durumunu açıkça ifade etmelidir.

## Quality Checklist

- Kapsam ölçülebilir mi?
- Out-of-scope açık mı?
- Kabul kriterleri test edilebilir mi?
- Risk -> mitigation eşleşmeleri var mı?
- Test beklentisi fonksiyonel + regresyon kapsıyor mu?
- Teknik iddialarda GitNexus veya hedefli okuma kanıtı var mı?

## References

- `C:\code\e_ticaret\.claude\skills\workflow\references\planning-template.md`
- `C:\code\e_ticaret\.claude\skills\workflow\references\handoff-schema.md`
