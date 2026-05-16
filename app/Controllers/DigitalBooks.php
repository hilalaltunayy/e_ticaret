<?php

namespace App\Controllers;

use App\Services\DigitalBookLibraryService;
use App\Services\DigitalBookHighlightService;
use App\Services\DigitalBookReaderService;
use App\Services\StorefrontHomeService;
use CodeIgniter\Exceptions\PageNotFoundException;

class DigitalBooks extends BaseController
{
    private StorefrontHomeService $storefrontHomeService;
    private DigitalBookLibraryService $digitalBookLibraryService;
    private DigitalBookReaderService $digitalBookReaderService;
    private DigitalBookHighlightService $digitalBookHighlightService;

    public function __construct()
    {
        $this->storefrontHomeService = new StorefrontHomeService();
        $this->digitalBookLibraryService = new DigitalBookLibraryService();
        $this->digitalBookReaderService = new DigitalBookReaderService();
        $this->digitalBookHighlightService = new DigitalBookHighlightService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Dijital kitaplarinizi gormek icin giris yapmalisiniz.');
        }

        return view('site/digital_books/index', [
            'title' => 'Dijital Kitaplarim',
            'books' => $this->digitalBookLibraryService->getOwnedDigitalBooksForUser($userId),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function read(string $productId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Okuyucuya erismek icin giris yapmalisiniz.');
        }

        $access = $this->digitalBookLibraryService->getDigitalBookAccessDecision($userId, $productId);
        if (! (bool) ($access['allowed'] ?? false)) {
            if (($access['reason'] ?? '') === 'not_found') {
                throw PageNotFoundException::forPageNotFound();
            }

            return $this->response
                ->setStatusCode(403)
                ->setBody('Forbidden');
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $payload = $this->digitalBookReaderService->buildReaderPayload($productId, $page);
        if (trim((string) ($payload['book']['product_id'] ?? '')) === '') {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('site/digital_books/reader', [
            'title' => 'Kitabi Oku',
            'reader' => $payload,
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function page(string $productId, int $pageNo)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'auth_required',
                'message' => 'Giris yapmaniz gerekiyor.',
            ]);
        }

        $access = $this->digitalBookLibraryService->getDigitalBookAccessDecision($userId, $productId);
        if (! (bool) ($access['allowed'] ?? false)) {
            if (($access['reason'] ?? '') === 'not_found') {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'not_found',
                    'message' => 'Kitap bulunamadi.',
                ]);
            }

            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'forbidden',
                'message' => 'Bu dijital kitaba erisim yetkiniz yok.',
            ]);
        }

        $payload = $this->digitalBookReaderService->buildReaderPayload($productId, $pageNo);
        if (trim((string) ($payload['book']['product_id'] ?? '')) === '') {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'not_found',
                'message' => 'Kitap bulunamadi.',
            ]);
        }

        return $this->response->setJSON([
            'productId' => (string) ($payload['book']['product_id'] ?? ''),
            'currentIndex' => (int) ($payload['current_page'] ?? 1),
            'total' => (int) ($payload['total_pages'] ?? 0),
            'hasPrev' => (bool) ($payload['has_prev'] ?? false),
            'hasNext' => (bool) ($payload['has_next'] ?? false),
            'content' => (string) ($payload['content'] ?? ''),
            'isEmpty' => (bool) ($payload['is_empty'] ?? true),
        ]);
    }

    public function highlights(string $productId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'auth_required',
                'message' => 'Giris yapmaniz gerekiyor.',
            ]);
        }

        $access = $this->digitalBookLibraryService->getDigitalBookAccessDecision($userId, $productId);
        if (! (bool) ($access['allowed'] ?? false)) {
            if (($access['reason'] ?? '') === 'not_found') {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'not_found',
                    'message' => 'Kitap bulunamadi.',
                ]);
            }

            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'forbidden',
            ]);
        }

        $pageNo = (int) ($this->request->getGet('page') ?? 0);
        $items = $this->digitalBookHighlightService->listHighlights($userId, $productId, $pageNo > 0 ? $pageNo : null);

        return $this->response->setJSON([
            'highlights' => $items,
        ]);
    }

    public function createHighlight(string $productId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'auth_required',
                'message' => 'Giris yapmaniz gerekiyor.',
            ]);
        }

        $access = $this->digitalBookLibraryService->getDigitalBookAccessDecision($userId, $productId);
        if (! (bool) ($access['allowed'] ?? false)) {
            if (($access['reason'] ?? '') === 'not_found') {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'not_found',
                    'message' => 'Kitap bulunamadi.',
                ]);
            }

            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'forbidden',
            ]);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $result = $this->digitalBookHighlightService->createHighlight($userId, $productId, $payload);
        if (! ($result['success'] ?? false)) {
            return $this->response->setStatusCode((int) ($result['status'] ?? 422))->setJSON([
                'error' => (string) ($result['error'] ?? 'invalid_request'),
                'highlight' => $result['highlight'] ?? null,
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'highlight' => $result['highlight'] ?? null,
        ]);
    }

    public function deleteHighlight(string $productId, string $highlightId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'auth_required',
                'message' => 'Giris yapmaniz gerekiyor.',
            ]);
        }

        $access = $this->digitalBookLibraryService->getDigitalBookAccessDecision($userId, $productId);
        if (! (bool) ($access['allowed'] ?? false)) {
            if (($access['reason'] ?? '') === 'not_found') {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'not_found',
                    'message' => 'Kitap bulunamadi.',
                ]);
            }

            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'forbidden',
            ]);
        }

        $result = $this->digitalBookHighlightService->deleteHighlight($userId, $productId, $highlightId);
        if (! ($result['success'] ?? false)) {
            return $this->response->setStatusCode((int) ($result['status'] ?? 404))->setJSON([
                'error' => (string) ($result['error'] ?? 'not_found'),
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
        ]);
    }

    private function getCurrentUserId(): ?string
    {
        if (! session()->get('isLoggedIn')) {
            return null;
        }

        $fromUser = trim((string) (session()->get('user')['id'] ?? ''));
        if ($fromUser !== '') {
            return $fromUser;
        }

        $fromLegacy = trim((string) session()->get('user_id'));
        return $fromLegacy !== '' ? $fromLegacy : null;
    }
}
