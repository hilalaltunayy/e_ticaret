<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ShippingAutomationService;
use DomainException;
use Throwable;

class ShippingAutomation extends BaseController
{
    public function __construct(private ?ShippingAutomationService $service = null)
    {
        $this->service = $this->service ?? new ShippingAutomationService();
    }

    public function index()
    {
        return view('admin/shipping/automation', [
            'title' => 'Kargo Otomasyon Kuralları',
            'companies' => $this->service->getCompanies(),
            'initialType' => 'city',
            'kpi' => [
                'active_rule' => $this->service->countActiveRules(),
                'auto_assignment_7d' => $this->service->countAutoAssignmentsLast7Days(),
                'sla_compliance' => $this->service->calculateSlaRate(),
                'avg_delivery_days' => $this->service->calculateAverageDeliveryTime(),
            ],
        ]);
    }

    public function rules()
    {
        $type = (string) ($this->request->getGet('type') ?? 'city');

        try {
            return $this->response->setJSON([
                'ok' => true,
                'data' => $this->service->list($type),
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Kurallar alınamadı.',
            ]);
        }
    }

    public function show(string $id)
    {
        try {
            return $this->response->setJSON([
                'ok' => true,
                'data' => $this->service->find($id),
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Kural alınamadı.',
            ]);
        }
    }

    public function create()
    {
        $type = (string) ($this->request->getPost('rule_type') ?? $this->request->getPost('type') ?? '');

        try {
            $this->service->create($type, [
                'rule_type' => $type,
                'city' => $this->request->getPost('city'),
                'desi_min' => $this->request->getPost('desi_min'),
                'desi_max' => $this->request->getPost('desi_max'),
                'sla_days' => $this->request->getPost('sla_days'),
                'primary_company_id' => $this->request->getPost('primary_company_id'),
                'secondary_company_id' => $this->request->getPost('secondary_company_id'),
                'is_active' => $this->request->getPost('is_active'),
            ]);

            return $this->response->setJSON([
                'ok' => true,
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Kural kaydedilemedi.',
            ]);
        }
    }

    public function update(string $id)
    {
        $type = (string) ($this->request->getPost('rule_type') ?? $this->request->getPost('type') ?? '');

        try {
            $this->service->update($type, $id, [
                'rule_type' => $type,
                'city' => $this->request->getPost('city'),
                'desi_min' => $this->request->getPost('desi_min'),
                'desi_max' => $this->request->getPost('desi_max'),
                'sla_days' => $this->request->getPost('sla_days'),
                'primary_company_id' => $this->request->getPost('primary_company_id'),
                'secondary_company_id' => $this->request->getPost('secondary_company_id'),
                'is_active' => $this->request->getPost('is_active'),
            ]);

            return $this->response->setJSON([
                'ok' => true,
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Kural güncellenemedi.',
            ]);
        }
    }
}