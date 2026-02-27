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

        $payload = [
            'name' => $name,
            'integration_type' => $integrationType,
            'note' => $note,
            'api_key' => $apiKey,
            'webhook_url' => $webhookUrl,
        ];
        unset($payload);

        return redirect()->to($returnUrl)->with('success', 'Kaydedilecek (yakında).');
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
}
