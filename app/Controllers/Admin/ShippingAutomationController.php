<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\DTO\Shipping\ShippingSimulationRequestDTO;
use App\Services\ShippingAutomationService;
use App\Services\ShippingSimulationService;
use DomainException;
use Throwable;

class ShippingAutomationController extends BaseController
{
    public function __construct(
        private ?ShippingAutomationService $automationService = null,
        private ?ShippingSimulationService $simulationService = null,
    ) {
        $this->automationService = $this->automationService ?? new ShippingAutomationService();
        $this->simulationService = $this->simulationService ?? new ShippingSimulationService();
    }

    public function index()
    {
        return view('admin/shipping/automation', [
            'title' => 'Kargo Otomasyon Kuralları',
            'companies' => $this->automationService->getCompanies(),
            'initialType' => 'city',
            'kpi' => [
                'active_rule' => $this->automationService->countActiveRules(),
                'auto_assignment_7d' => $this->automationService->countAutoAssignmentsLast7Days(),
                'sla_compliance' => $this->automationService->calculateSlaRate(),
                'avg_delivery_days' => $this->automationService->calculateAverageDeliveryTime(),
            ],
        ]);
    }

    public function simulate()
    {
        try {
            $dto = ShippingSimulationRequestDTO::fromArray([
                'city' => $this->request->getPost('city'),
                'slaDays' => $this->request->getPost('sla_days'),
                'desi' => $this->request->getPost('desi'),
                'cod' => $this->request->getPost('cod'),
            ]);

            $result = $this->simulationService->simulate($dto);

            return $this->response->setJSON([
                'ok' => true,
                'data' => $result,
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Simülasyon hesaplanamadı.',
            ]);
        }
    }

    public function rules()
    {
        $type = (string) ($this->request->getGet('type') ?? 'city');

        try {
            return $this->response->setJSON([
                'ok' => true,
                'data' => $this->automationService->list($type),
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
                'data' => $this->automationService->find($id),
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(404)->setJSON([
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
            $this->automationService->create($type, [
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
            $this->automationService->update($type, $id, [
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