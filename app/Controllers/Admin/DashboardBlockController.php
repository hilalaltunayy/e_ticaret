<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardBlockService;
use App\Services\DashboardService;

class DashboardBlockController extends BaseController
{
    public function __construct(
        private ?DashboardBlockService $dashboardBlockService = null,
        private ?DashboardService $dashboardService = null
    ) {
        $this->dashboardBlockService = $this->dashboardBlockService ?? new DashboardBlockService();
        $this->dashboardService = $this->dashboardService ?? new DashboardService();
    }

    public function index()
    {
        return redirect()->to(site_url('admin/dashboard'));
    }

    public function fetch(string $id)
    {
        $block = $this->dashboardBlockService->getBlockForEdit($id);
        if (! is_array($block)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Dashboard blogu bulunamadi.',
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'block' => $block,
        ]);
    }

    public function store()
    {
        $dashboard = $this->dashboardService->getActiveDashboard($this->actorId());
        if (! is_array($dashboard) || empty($dashboard['id'])) {
            return redirect()->to(site_url('admin/dashboard'))
                ->withInput()
                ->with('error', 'Aktif dashboard bulunamadi.')
                ->with('dashboard_block_errors', ['Aktif dashboard bulunamadi.'])
                ->with('dashboard_block_modal', 'create');
        }

        $result = $this->dashboardBlockService->addBlock((string) $dashboard['id'], $this->request->getPost());
        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/dashboard'))
                ->withInput()
                ->with('error', 'Yeni blok eklenemedi.')
                ->with('dashboard_block_errors', $result['errors'] ?? ['Yeni blok eklenemedi.'])
                ->with('dashboard_block_modal', 'create');
        }

        return redirect()->to(site_url('admin/dashboard'))->with('success', 'Yeni dashboard karti eklendi.');
    }

    public function update(string $id)
    {
        $post = $this->request->getPost();
        $result = $this->dashboardBlockService->updateBlock($id, $post);

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/dashboard'))
                ->with('error', 'Dashboard karti guncellenemedi.')
                ->with('dashboard_block_edit_errors', $result['errors'] ?? ['Dashboard karti guncellenemedi.'])
                ->with('dashboard_block_modal', 'edit')
                ->with('dashboard_block_edit_id', $id)
                ->with('dashboard_block_edit_old', $post);
        }

        return redirect()->to(site_url('admin/dashboard'))->with('success', 'Dashboard karti guncellendi.');
    }

    public function delete(string $id)
    {
        $result = $this->dashboardBlockService->deleteBlock($id);
        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/dashboard'))
                ->with('error', 'Dashboard karti silinemedi.')
                ->with('dashboard_block_delete_errors', $result['errors'] ?? ['Dashboard karti silinemedi.']);
        }

        return redirect()->to(site_url('admin/dashboard'))->with('success', 'Dashboard karti silindi.');
    }

    private function actorId(): ?string
    {
        $user = session()->get('user') ?? [];
        $id = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));

        return $id === '' ? null : $id;
    }
}
