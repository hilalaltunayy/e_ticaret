<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserPermissionModel;
use App\Services\Admin\AdminReviewModerationService;

class Reviews extends BaseController
{
    private AdminReviewModerationService $moderationService;
    private UserPermissionModel $userPermissionModel;

    public function __construct()
    {
        $this->moderationService = new AdminReviewModerationService();
        $this->userPermissionModel = new UserPermissionModel();
    }

    public function index()
    {
        $listing = $this->moderationService->getReviewListing([
            'status' => (string) ($this->request->getGet('status') ?? ''),
            'q' => (string) ($this->request->getGet('q') ?? ''),
        ]);

        return view('admin/reviews/index', [
            'title' => 'Urun Yorumlari',
            'summary' => $listing['summary'] ?? [],
            'items' => $listing['items'] ?? [],
            'filters' => $listing['filters'] ?? [],
            'canDeleteReviews' => $this->canDeleteReviews(),
        ]);
    }

    public function approve(string $id)
    {
        return $this->handleStatusAction($this->moderationService->approve($id));
    }

    public function hide(string $id)
    {
        return $this->handleStatusAction($this->moderationService->hide($id));
    }

    public function reject(string $id)
    {
        return $this->handleStatusAction($this->moderationService->reject($id));
    }

    public function delete(string $id)
    {
        if (! $this->canDeleteReviews()) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Bu işlem için yorum silme yetkiniz bulunmuyor.');
        }

        $result = $this->moderationService->deleteReview($id);

        return redirect()->to(site_url('admin/reviews'))
            ->with(($result['success'] ?? false) ? 'success' : 'error', (string) ($result['message'] ?? 'İşlem tamamlanamadı.'));
    }

    private function handleStatusAction(array $result)
    {
        return redirect()->to(site_url('admin/reviews'))
            ->with(($result['success'] ?? false) ? 'success' : 'error', (string) ($result['message'] ?? 'İşlem tamamlanamadı.'));
    }

    private function canDeleteReviews(): bool
    {
        $user = is_array(session()->get('user')) ? session()->get('user') : [];
        $userId = trim((string) ($user['id'] ?? session()->get('user_id') ?? ''));
        $role = trim((string) ($user['role'] ?? session()->get('role') ?? ''));

        if ($userId === '') {
            return false;
        }

        return $this->userPermissionModel->isAllowed($userId, 'delete_reviews', $role);
    }
}
