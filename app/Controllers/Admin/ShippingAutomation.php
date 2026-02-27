<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ShippingAutomationService;
use InvalidArgumentException;
use RuntimeException;
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
            'title' => 'Kargo Optimizasyonu',
            'companies' => $this->service->getCompanies(),
            'initialType' => 'city',
            'initialRules' => $this->service->listRulesByType('city'),
            'kpi' => $this->service->getKpi(),
        ]);
    }

    public function rules()
    {
        $type = trim((string) $this->request->getGet('type'));
        if ($type === '') {
            $type = 'city';
        }

        try {
            $rules = $this->service->listRulesByType($type);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->response->setJSON([
            'success' => true,
            'type' => $type,
            'rules' => $rules,
            'kpi' => $this->service->getKpi(),
            'csrf' => $this->csrfPayload(),
        ]);
    }

    public function create()
    {
        $payload = (array) $this->request->getPost();

        try {
            $id = $this->service->createRule($payload);
            $type = trim((string) ($payload['rule_type'] ?? 'city'));
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kural kaydedildi.',
                'id' => $id,
                'type' => $type,
                'rules' => $this->service->listRulesByType($type),
                'kpi' => $this->service->getKpi(),
                'csrf' => $this->csrfPayload(),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        } catch (Throwable $e) {
            return $this->jsonError('Kural kaydedilemedi.', 500);
        }
    }

    public function update(string $id)
    {
        $payload = (array) $this->request->getPost();

        try {
            $this->service->updateRule($id, $payload);
            $type = trim((string) ($payload['rule_type'] ?? 'city'));
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kural güncellendi.',
                'type' => $type,
                'rules' => $this->service->listRulesByType($type),
                'kpi' => $this->service->getKpi(),
                'csrf' => $this->csrfPayload(),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        } catch (Throwable $e) {
            return $this->jsonError('Kural güncellenemedi.', 500);
        }
    }

    public function toggle(string $id)
    {
        try {
            $this->service->toggleRule($id);
            $type = trim((string) $this->request->getPost('rule_type'));
            if ($type === '') {
                $type = trim((string) $this->request->getGet('type'));
            }
            if ($type === '') {
                $type = 'city';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kural durumu güncellendi.',
                'type' => $type,
                'rules' => $this->service->listRulesByType($type),
                'kpi' => $this->service->getKpi(),
                'csrf' => $this->csrfPayload(),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        } catch (Throwable $e) {
            return $this->jsonError('Kural durumu güncellenemedi.', 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->service->deleteRule($id);
            $type = trim((string) $this->request->getPost('rule_type'));
            if ($type === '') {
                $type = trim((string) $this->request->getGet('type'));
            }
            if ($type === '') {
                $type = 'city';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kural silindi.',
                'type' => $type,
                'rules' => $this->service->listRulesByType($type),
                'kpi' => $this->service->getKpi(),
                'csrf' => $this->csrfPayload(),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        } catch (Throwable $e) {
            return $this->jsonError('Kural silinemedi.', 500);
        }
    }

    private function jsonError(string $message, int $status)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => false,
            'message' => $message,
            'csrf' => $this->csrfPayload(),
        ]);
    }

    private function csrfPayload(): array
    {
        return [
            'name' => csrf_token(),
            'hash' => csrf_hash(),
        ];
    }
}
