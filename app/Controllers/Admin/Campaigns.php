<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\DTO\Marketing\CampaignDTO;
use App\Services\CampaignService;

class Campaigns extends BaseController
{
    public function __construct(private ?CampaignService $campaignService = null)
    {
        $this->campaignService = $this->campaignService ?? new CampaignService();
    }

    public function index()
    {
        $payload = $this->campaignService->listCampaigns();

        return view('admin/campaigns/index', [
            'title' => 'Kampanya Yönetimi',
            'campaigns' => $payload['items'] ?? [],
            'summary' => $payload['summary'] ?? [],
        ]);
    }

    public function create()
    {
        return view('admin/campaigns/create', [
            'title' => 'Kampanya Ekle',
            'meta' => $this->campaignService->getCampaignFormMeta(),
            'formData' => $this->defaultFormData(),
            'errors' => session('campaign_errors') ?? [],
        ]);
    }

    public function store()
    {
        $dto = CampaignDTO::fromRequest($this->request->getPost());
        $result = $this->campaignService->createCampaign($dto, $this->actorId());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('campaign_errors', $result['errors'] ?? ['Kampanya kaydedilemedi.']);
        }

        return redirect()->to(site_url('admin/campaigns'))->with('success', 'Kampanya oluşturuldu.');
    }

    public function edit(string $id)
    {
        $campaign = $this->campaignService->getCampaignForEdit($id);
        if (! is_array($campaign)) {
            return redirect()->to(site_url('admin/campaigns'))->with('error', 'Kampanya bulunamadı.');
        }

        return view('admin/campaigns/edit', [
            'title' => 'Kampanya Düzenle',
            'campaignId' => $id,
            'meta' => $this->campaignService->getCampaignFormMeta(),
            'formData' => $campaign,
            'errors' => session('campaign_errors') ?? [],
        ]);
    }

    public function update(string $id)
    {
        $dto = CampaignDTO::fromRequest($this->request->getPost());
        $result = $this->campaignService->updateCampaign($id, $dto, $this->actorId());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('campaign_errors', $result['errors'] ?? ['Kampanya güncellenemedi.']);
        }

        return redirect()->to(site_url('admin/campaigns'))->with('success', 'Kampanya güncellendi.');
    }

    public function toggle(string $id)
    {
        $ok = $this->campaignService->toggleCampaignStatus($id, $this->actorId());
        if (! $ok) {
            return redirect()->to(site_url('admin/campaigns'))->with('error', 'Kampanya durumu güncellenemedi.');
        }

        return redirect()->to(site_url('admin/campaigns'))->with('success', 'Kampanya durumu güncellendi.');
    }

    public function delete(string $id)
    {
        $ok = $this->campaignService->deleteCampaign($id, $this->actorId());
        if (! $ok) {
            return redirect()->to(site_url('admin/campaigns'))->with('error', 'Kampanya silinemedi.');
        }

        return redirect()->to(site_url('admin/campaigns'))->with('success', 'Kampanya silindi.');
    }

    private function actorId(): ?string
    {
        $user = session()->get('user') ?? [];
        $id = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        return $id === '' ? null : $id;
    }

    private function defaultFormData(): array
    {
        return [
            'name' => old('name', ''),
            'slug' => old('slug', ''),
            'campaign_type' => old('campaign_type', 'cart_discount'),
            'discount_type' => old('discount_type', 'percent'),
            'discount_value' => old('discount_value', ''),
            'min_cart_amount' => old('min_cart_amount', ''),
            'starts_at' => old('starts_at', ''),
            'ends_at' => old('ends_at', ''),
            'priority' => old('priority', '0'),
            'stop_further_rules' => (int) old('stop_further_rules', 0),
            'is_active' => (int) old('is_active', 1),
            'category_ids' => old('category_ids', []),
            'product_ids' => old('product_ids', []),
        ];
    }
}

