<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ShippingCompanies extends BaseController
{
    public function create()
    {
        $returnUrl = $this->normalizeReturnUrl($this->request->getGet('return_url'));

        return view('admin/shipping/companies_create', [
            'title' => 'Kargo Firması Ekle',
            'returnUrl' => $returnUrl,
        ]);
    }

    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $integrationType = trim((string) $this->request->getPost('integration_type'));
        $note = trim((string) $this->request->getPost('note'));
        $apiKey = trim((string) $this->request->getPost('api_key'));
        $webhookUrl = trim((string) $this->request->getPost('webhook_url'));
        $returnUrl = $this->normalizeReturnUrl($this->request->getPost('return_url'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Firma adı zorunludur.');
        }

        $db = db_connect();
        if (! $db->tableExists('shipping_companies')) {
            return redirect()->back()->withInput()->with('error', 'Kargo firmaları tablosu bulunamadı.');
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_companies')
        );

        $payload = [];

        $nameField = $this->firstExistingField($fields, ['name', 'company_name', 'title']);
        if ($nameField === null) {
            return redirect()->back()->withInput()->with('error', 'Firma adı alanı bulunamadı.');
        }
        $payload[$nameField] = $name;

        $integrationField = $this->firstExistingField($fields, ['integration_type', 'api_type', 'integration']);
        if ($integrationField !== null) {
            $payload[$integrationField] = $integrationType !== '' ? $integrationType : 'Yok';
        }

        $noteField = $this->firstExistingField($fields, ['note', 'notes', 'description']);
        if ($noteField !== null) {
            $payload[$noteField] = $note !== '' ? $note : null;
        }

        $apiKeyField = $this->firstExistingField($fields, ['api_key']);
        if ($apiKeyField !== null) {
            $payload[$apiKeyField] = $apiKey !== '' ? $apiKey : null;
        }

        $webhookField = $this->firstExistingField($fields, ['webhook_url', 'webhook']);
        if ($webhookField !== null) {
            $payload[$webhookField] = $webhookUrl !== '' ? $webhookUrl : null;
        }

        if (in_array('is_active', $fields, true)) {
            $payload['is_active'] = 1;
        } elseif (in_array('active', $fields, true)) {
            $payload['active'] = 1;
        } elseif (in_array('status', $fields, true)) {
            $payload['status'] = 'active';
        }

        $now = date('Y-m-d H:i:s');
        if (in_array('created_at', $fields, true)) {
            $payload['created_at'] = $now;
        }
        if (in_array('updated_at', $fields, true)) {
            $payload['updated_at'] = $now;
        }

        $ok = $db->table('shipping_companies')->insert($payload);
        if (! $ok) {
            $error = $db->error();
            $message = trim((string) ($error['message'] ?? ''));
            return redirect()->back()->withInput()->with('error', $message !== '' ? $message : 'Firma kaydedilemedi.');
        }

        return redirect()->to($returnUrl)->with('success', 'Kargo firması kaydedildi.');
    }

    private function normalizeReturnUrl(?string $returnUrl): string
    {
        $fallback = site_url('admin/shipping');
        $value = trim((string) $returnUrl);

        if ($value === '') {
            return $fallback;
        }

        $host = (string) parse_url($value, PHP_URL_HOST);
        if ($host !== '') {
            return $fallback;
        }

        return $value;
    }

    /**
     * @param array<int, string> $fields
     * @param array<int, string> $candidates
     */
    private function firstExistingField(array $fields, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $fields, true)) {
                return $candidate;
            }
        }

        return null;
    }

}
