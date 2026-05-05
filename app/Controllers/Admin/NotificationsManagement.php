<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\Admin\AdminNotificationManagementService;
use DomainException;

class NotificationsManagement extends BaseController
{
    public function __construct(private ?AdminNotificationManagementService $service = null)
    {
        $this->service = $this->service ?? new AdminNotificationManagementService();
    }

    public function index()
    {
        return view('admin/notifications_management/index', [
            'title' => 'Operasyon Bildirimleri',
            'pageData' => $this->service->getPageData($this->actorId(), $this->actorRole()),
        ]);
    }

    public function update()
    {
        try {
            $this->service->savePreferences(
                $this->actorId(),
                $this->actorRole(),
                $this->request->getPost()
            );

            return redirect()->to(site_url('admin/notifications-management'))
                ->with('success', 'Bildirim tercihleri kaydedildi.');
        } catch (DomainException $e) {
            return redirect()->to(site_url('admin/notifications-management'))
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function actorId(): string
    {
        $user = session()->get('user');
        $userId = is_array($user) ? (string) ($user['id'] ?? '') : '';

        return $userId !== '' ? $userId : (string) (session('user_id') ?? '');
    }

    private function actorRole(): string
    {
        $user = session()->get('user');
        $role = is_array($user) ? (string) ($user['role'] ?? '') : '';

        return $role !== '' ? $role : (string) (session('role') ?? '');
    }
}
