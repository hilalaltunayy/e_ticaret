<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardBlockService;
use App\Services\DashboardBuilderService;

class DashboardBuilder extends BaseController
{
    public function __construct(
        private ?DashboardBuilderService $dashboardBuilderService = null,
        private ?DashboardBlockService $dashboardBlockService = null
    ) {
        $this->dashboardBuilderService = $this->dashboardBuilderService ?? new DashboardBuilderService();
        $this->dashboardBlockService = $this->dashboardBlockService ?? new DashboardBlockService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];
        $userId = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        $dashboard = $this->dashboardBuilderService->getOrCreateAdminDashboard($userId);

        return view('admin/dashboard_builder/index', [
            'builderDashboard' => $dashboard,
            'builderBlocks' => $this->dashboardBuilderService->getBuilderBlocks($userId),
            'builderBlockTypes' => $this->dashboardBlockService->getAvailableBlockTypes(),
        ]);
    }

    public function reorder()
    {
        $user = session()->get('user') ?? [];
        $userId = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        $blocks = json_decode((string) ($this->request->getPost('blocks') ?? '[]'), true);

        if ($userId === '') {
            return $this->response->setStatusCode(403)->setJSON($this->withCsrf([
                'success' => false,
                'message' => 'Kullanici bilgisi bulunamadi.',
            ]));
        }

        if (! is_array($blocks)) {
            return $this->response->setStatusCode(422)->setJSON($this->withCsrf([
                'success' => false,
                'message' => 'Gecerli sira verisi gonderilmedi.',
            ]));
        }

        $result = $this->dashboardBuilderService->saveBlockOrder($userId, $blocks);
        $status = ($result['success'] ?? false) ? 200 : 422;

        return $this->response
            ->setStatusCode($status)
            ->setJSON($this->withCsrf($result));
    }

    public function resize()
    {
        $user = session()->get('user') ?? [];
        $userId = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        $blockId = trim((string) ($this->request->getPost('id') ?? ''));
        $width = $this->request->getPost('width');
        $height = $this->request->getPost('height');

        if ($userId === '') {
            return $this->response->setStatusCode(403)->setJSON($this->withCsrf([
                'success' => false,
                'message' => 'Kullanici bilgisi bulunamadi.',
            ]));
        }

        $result = $this->dashboardBuilderService->resizeBlock($userId, $blockId, $width, $height);
        $status = ($result['success'] ?? false) ? 200 : 422;

        return $this->response
            ->setStatusCode($status)
            ->setJSON($this->withCsrf($result));
    }

    private function withCsrf(array $payload): array
    {
        $payload['csrf'] = [
            'token' => csrf_token(),
            'hash' => csrf_hash(),
        ];

        return $payload;
    }
}
