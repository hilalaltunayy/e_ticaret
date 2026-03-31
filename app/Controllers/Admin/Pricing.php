<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\PriceRuleModel;
use App\Models\ProductsModel;

class Pricing extends BaseController
{
    public function index()
    {
        return view('admin/pricing/index', [
            'title' => 'Kampanya / Fiyat Paneli',
        ]);
    }

    public function rules()
    {
        $model = new PriceRuleModel();
        $rules = $model->findAll();

        return view('admin/pricing/rules', [
            'title' => 'Fiyat Kuralları',
            'rules' => $rules,
        ]);
    }

    public function createRule()
    {
        return view('admin/pricing/create', [
            'title' => 'Yeni Fiyat Kuralı',
            'meta' => $this->ruleFormMeta(),
            'formData' => $this->defaultFormData(),
            'errors' => session('price_rule_errors') ?? [],
        ]);
    }

    public function storeRule()
    {
        $result = $this->validateAndNormalizeRuleInput();
        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('price_rule_errors', $result['errors'] ?? ['Fiyat kuralı kaydedilemedi.']);
        }

        $model = new PriceRuleModel();
        $inserted = $model->insert($result['data'], true);
        if ($inserted === false) {
            return redirect()->back()
                ->withInput()
                ->with('price_rule_errors', ['Fiyat kuralı kaydedilemedi.']);
        }

        return redirect()->to(site_url('admin/pricing/rules'))->with('success', 'Fiyat kuralı oluşturuldu.');
    }

    public function editRule(string $id)
    {
        $model = new PriceRuleModel();
        $rule = $model->find($id);
        if (! is_array($rule)) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı bulunamadı.');
        }

        return view('admin/pricing/edit', [
            'title' => 'Fiyat Kuralı Düzenle',
            'ruleId' => $id,
            'meta' => $this->ruleFormMeta(),
            'formData' => $this->mapRuleForForm($rule),
            'errors' => session('price_rule_errors') ?? [],
        ]);
    }

    public function updateRule(string $id)
    {
        $model = new PriceRuleModel();
        $rule = $model->find($id);
        if (! is_array($rule)) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı bulunamadı.');
        }

        $result = $this->validateAndNormalizeRuleInput();
        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('price_rule_errors', $result['errors'] ?? ['Fiyat kuralı güncellenemedi.']);
        }

        $updated = $model->update($id, $result['data']);
        if (! $updated) {
            return redirect()->back()
                ->withInput()
                ->with('price_rule_errors', ['Fiyat kuralı güncellenemedi.']);
        }

        return redirect()->to(site_url('admin/pricing/rules'))->with('success', 'Fiyat kuralı güncellendi.');
    }

    public function toggleRule(string $id)
    {
        $model = new PriceRuleModel();
        $rule = $model->find($id);
        if (! is_array($rule)) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı bulunamadı.');
        }

        $nextStatus = (int) ($rule['is_active'] ?? 0) === 1 ? 0 : 1;
        $updated = $model->update($id, ['is_active' => $nextStatus]);
        if (! $updated) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı durumu güncellenemedi.');
        }

        return redirect()->to(site_url('admin/pricing/rules'))->with('success', 'Fiyat kuralı durumu güncellendi.');
    }

    public function deleteRule(string $id)
    {
        $model = new PriceRuleModel();
        $rule = $model->find($id);
        if (! is_array($rule)) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı bulunamadı.');
        }

        $deleted = $model->delete($id);
        if (! $deleted) {
            return redirect()->to(site_url('admin/pricing/rules'))->with('error', 'Fiyat kuralı silinemedi.');
        }

        return redirect()->to(site_url('admin/pricing/rules'))->with('success', 'Fiyat kuralı silindi.');
    }

    private function ruleFormMeta(): array
    {
        return [
            'categories' => (new CategoryModel())->getAllForAdmin(),
            'products' => (new ProductsModel())->getAllActivePrintedProductsForSelect(),
        ];
    }

    private function defaultFormData(): array
    {
        return [
            'name' => old('name', ''),
            'type' => old('type', 'percentage'),
            'value' => old('value', ''),
            'target' => old('target', 'global'),
            'target_id' => old('target_id', ''),
            'priority' => old('priority', '0'),
            'is_active' => (int) old('is_active', 1),
            'category_ids' => old('category_ids', []),
            'product_ids' => old('product_ids', []),
        ];
    }

    private function mapRuleForForm(array $rule): array
    {
        $target = (string) ($rule['target'] ?? 'global');
        $targetIds = $this->extractSelectedIds((string) ($rule['target_id'] ?? ''));

        return [
            'name' => (string) ($rule['name'] ?? ''),
            'type' => (string) ($rule['type'] ?? 'percentage'),
            'value' => $rule['value'] !== null ? (string) $rule['value'] : '',
            'target' => $target,
            'target_id' => (string) ($rule['target_id'] ?? ''),
            'priority' => (string) ($rule['priority'] ?? '0'),
            'is_active' => (int) ($rule['is_active'] ?? 1),
            'category_ids' => $target === 'category' ? $targetIds : [],
            'product_ids' => $target === 'product' ? $targetIds : [],
        ];
    }

    private function validateAndNormalizeRuleInput(): array
    {
        $name = trim((string) $this->request->getPost('name'));
        $type = trim((string) $this->request->getPost('type'));
        $valueRaw = trim((string) $this->request->getPost('value'));
        $target = trim((string) $this->request->getPost('target'));
        $priorityRaw = trim((string) $this->request->getPost('priority'));
        $isActiveRaw = (string) $this->request->getPost('is_active');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Kural adı zorunludur.';
        }

        if (! in_array($type, ['percentage', 'fixed'], true)) {
            $errors[] = 'Kural tipi geçersiz.';
        }

        if ($valueRaw === '' || ! is_numeric($valueRaw)) {
            $errors[] = 'Değer alanı sayısal olmalıdır.';
        }

        $value = is_numeric($valueRaw) ? (float) $valueRaw : 0.0;
        if ($value < 0) {
            $errors[] = 'Değer negatif olamaz.';
        }
        if ($type === 'percentage' && $value > 100) {
            $errors[] = 'Yüzde değer 100\'den büyük olamaz.';
        }

        if (! in_array($target, ['global', 'category', 'product'], true)) {
            $errors[] = 'Hedef tipi geçersiz.';
        }

        if ($priorityRaw === '' || filter_var($priorityRaw, FILTER_VALIDATE_INT) === false || (int) $priorityRaw < 0) {
            $errors[] = 'Öncelik 0 veya daha büyük bir tam sayı olmalıdır.';
        }

        if (! in_array($isActiveRaw, ['0', '1'], true)) {
            $errors[] = 'Durum alanı geçersiz.';
        }

        $selectedIds = [];
        if ($target === 'category') {
            $selectedIds = $this->normalizeSelectedIds($this->request->getPost('category_ids'));
            if ($selectedIds === []) {
                $errors[] = 'Kategori hedefi için en az bir kategori seçilmelidir.';
            } elseif (! $this->allIdsExist($selectedIds, new CategoryModel())) {
                $errors[] = 'Seçilen kategorilerden biri geçersiz.';
            }
        } elseif ($target === 'product') {
            $selectedIds = $this->normalizeSelectedIds($this->request->getPost('product_ids'));
            if ($selectedIds === []) {
                $errors[] = 'Ürün hedefi için en az bir ürün seçilmelidir.';
            } elseif (! $this->allIdsExist($selectedIds, new ProductsModel())) {
                $errors[] = 'Seçilen ürünlerden biri geçersiz.';
            }
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        return [
            'success' => true,
            'data' => [
                'name' => $name,
                'type' => $type,
                'value' => $value,
                'target' => $target,
                'target_id' => $target === 'global' ? null : implode(',', $selectedIds),
                'priority' => (int) $priorityRaw,
                'is_active' => (int) $isActiveRaw,
            ],
        ];
    }

    private function normalizeSelectedIds($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $item) {
            $id = trim((string) $item);
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function extractSelectedIds(string $raw): array
    {
        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, static fn (string $id): bool => $id !== '');

        return array_values(array_unique($parts));
    }

    private function allIdsExist(array $ids, $model): bool
    {
        return count($model->whereIn('id', $ids)->findAll()) === count($ids);
    }
}
