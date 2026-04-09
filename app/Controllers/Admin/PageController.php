<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CheckoutPageBuilderService;
use App\Services\CheckoutPreviewRenderer;
use App\Services\PageBuilderService;
use App\Services\PageService;
use App\Services\PageVersionService;
use App\Services\ProductListPreviewRenderer;
use CodeIgniter\Exceptions\PageNotFoundException;

class PageController extends BaseController
{
    public function __construct(
        private ?PageService $pageService = null,
        private ?PageVersionService $pageVersionService = null,
        private ?PageBuilderService $pageBuilderService = null,
        private ?ProductListPreviewRenderer $productListPreviewRenderer = null,
        private ?CheckoutPageBuilderService $checkoutPageBuilderService = null,
        private ?CheckoutPreviewRenderer $checkoutPreviewRenderer = null
    ) {
        $this->pageService = $this->pageService ?? new PageService();
        $this->pageVersionService = $this->pageVersionService ?? new PageVersionService();
        $this->pageBuilderService = $this->pageBuilderService ?? new PageBuilderService();
        $this->productListPreviewRenderer = $this->productListPreviewRenderer ?? new ProductListPreviewRenderer();
        $this->checkoutPageBuilderService = $this->checkoutPageBuilderService ?? new CheckoutPageBuilderService();
        $this->checkoutPreviewRenderer = $this->checkoutPreviewRenderer ?? new CheckoutPreviewRenderer();
    }

    public function index()
    {
        return view('admin/pages/index', [
            'title' => 'Sayfa Yonetimi',
            'pages' => $this->pageService->listPageItems(),
            'tablesReady' => $this->pageService->tablesReady(),
        ]);
    }

    public function show($code)
    {
        $versionOverview = $this->pageBuilderService->getVersionOverview((string) $code);

        if (is_array($versionOverview)) {
            return view('admin/pages/version', [
                'title' => 'Page Version Detayi',
                'version' => $versionOverview['version'],
                'blocks' => $versionOverview['blocks'],
            ]);
        }

        $pageOverview = $this->pageBuilderService->getPageOverview((string) $code);

        if (! is_array($pageOverview)) {
            throw PageNotFoundException::forPageNotFound('Page bulunamadi.');
        }

        return view('admin/pages/show', [
            'title' => 'Sayfa Detayi',
            'page' => $pageOverview['page'],
            'publishedVersion' => $pageOverview['publishedVersion'],
            'drafts' => $pageOverview['drafts'],
        ]);
    }

    public function drafts($code)
    {
        $page = $this->pageService->findPageByCode((string) $code);

        if (! is_array($page)) {
            throw PageNotFoundException::forPageNotFound('Page bulunamadi.');
        }

        return view('admin/pages/drafts', [
            'title' => 'Sayfa Draftlari',
            'page' => $page,
            'drafts' => $this->pageVersionService->getDraftListByPageCode((string) $code),
        ]);
    }

    public function builder($pageCode)
    {
        $builderData = $this->pageBuilderService->getBuilderData((string) $pageCode);

        if (! is_array($builderData)) {
            throw PageNotFoundException::forPageNotFound('Builder sayfasi bulunamadi.');
        }

        $view = ($builderData['page']['code'] ?? '') === 'product_list'
            ? 'admin/pages/product_list_builder'
            : ((($builderData['page']['code'] ?? '') === 'checkout')
                ? 'admin/pages/checkout_builder'
                : 'admin/pages/builder');

        $productListPreview = [];
        if (($builderData['page']['code'] ?? '') === 'product_list') {
            $oldInput = session()->getFlashdata('_ci_old_input');
            $productListPreview = is_array($oldInput)
                ? $this->productListPreviewRenderer->buildFromFormInput($oldInput, $builderData['productListConfig'] ?? [])
                : $this->productListPreviewRenderer->build($builderData['productListConfig'] ?? []);
        }

        $checkoutBuilderState = ['layoutBlock' => null, 'config' => []];
        $checkoutPreview = [];
        if (($builderData['page']['code'] ?? '') === 'checkout') {
            $checkoutBuilderState = $this->checkoutPageBuilderService->getBuilderState((string) ($builderData['draft']['id'] ?? ''));
            $oldInput = session()->getFlashdata('_ci_old_input');
            $checkoutPreview = is_array($oldInput)
                ? $this->checkoutPreviewRenderer->buildFromFormInput($oldInput, $checkoutBuilderState['config'] ?? [])
                : $this->checkoutPreviewRenderer->build($checkoutBuilderState['config'] ?? []);
        }

        return view($view, [
            'title' => 'Page Builder',
            'page' => $builderData['page'],
            'draft' => $builderData['draft'],
            'publishedVersion' => $builderData['publishedVersion'],
            'blockTypes' => $builderData['blockTypes'],
            'blocks' => $builderData['blocks'],
            'productListLayoutBlock' => $builderData['productListLayoutBlock'] ?? null,
            'productListConfig' => $builderData['productListConfig'] ?? [],
            'productListPreview' => $productListPreview,
            'checkoutLayoutBlock' => $checkoutBuilderState['layoutBlock'] ?? null,
            'checkoutConfig' => $checkoutBuilderState['config'] ?? [],
            'checkoutPreview' => $checkoutPreview,
            'builderPolicy' => $builderData['builderPolicy'],
            'builderOptions' => $builderData['builderOptions'],
        ]);
    }

    public function updateProductListBuilder()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->updateProductListConfig($versionId, $this->request->getPost());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Product list ayarlari guncellenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Product list ayarlari guncellendi.');
    }

    public function updateCheckoutBuilder()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->checkoutPageBuilderService->updateConfig($versionId, $this->request->getPost());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Checkout ayarlari guncellenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Checkout ayarlari guncellendi.');
    }

    public function createDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $page = $this->pageService->findPageByCode($pageCode);

        if (! is_array($page)) {
            return redirect()->back()->with('draft_error', 'Sayfa bulunamadi.');
        }

        $result = $this->pageBuilderService->createDraft((string) $page['id']);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->with('draft_error', (string) ($result['error'] ?? 'Yeni draft olusturulamadi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Yeni draft olusturuldu.');
    }

    public function duplicateDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->duplicateDraft($versionId);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->with('draft_error', (string) ($result['error'] ?? 'Version kopyalanamadi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/drafts'))
            ->with('success', 'Version kopyalanarak yeni draft olusturuldu.');
    }

    public function archiveDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->archiveDraft($versionId);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->with('draft_error', (string) ($result['error'] ?? 'Version arsivlenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/drafts'))
            ->with('success', 'Version arsivlendi.');
    }

    public function unpublishVersion()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->unpublishVersion($versionId);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->with('draft_error', (string) ($result['error'] ?? 'Canlidaki version geri cekilemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/drafts'))
            ->with('success', 'Published version canlidan cekildi.');
    }

    public function addBlock()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));
        $blockTypeId = trim((string) $this->request->getPost('block_type_id'));
        $result = $this->pageBuilderService->addBlock($versionId, $blockTypeId, $this->request->getPost());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Block eklenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Block eklendi.');
    }

    public function deleteBlock()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $blockId = trim((string) $this->request->getPost('block_id'));

        $result = $this->pageBuilderService->deleteBlock($blockId);

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
                ->with('error', (string) ($result['error'] ?? 'Block silinemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Block silindi.');
    }

    public function reorderBlock()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $blockId = trim((string) $this->request->getPost('block_id'));
        $direction = trim((string) $this->request->getPost('direction'));

        $result = $this->pageBuilderService->reorderBlock($blockId, $direction);

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
                ->with('error', (string) ($result['error'] ?? 'Block sirasi guncellenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Block sirasi guncellendi.');
    }

    public function updateBlock()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $blockId = trim((string) $this->request->getPost('block_id'));

        $result = $this->pageBuilderService->updateBlockConfig($blockId, $this->request->getPost());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Block ayarlari guncellenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Block ayarlari guncellendi.');
    }

    public function updateDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->updateDraftMeta($versionId, $this->request->getPost());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('draft_error', (string) ($result['error'] ?? 'Draft bilgileri guncellenemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Draft bilgileri guncellendi.');
    }

    public function publishDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->publishDraft($versionId);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('draft_error', (string) ($result['error'] ?? 'Draft publish edilemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Draft canliya alindi.');
    }

    public function scheduleDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));
        $scheduledAt = trim((string) $this->request->getPost('scheduled_publish_at'));

        $result = $this->pageBuilderService->scheduleDraft($versionId, $scheduledAt);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('draft_error', (string) ($result['error'] ?? 'Draft schedule edilemedi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Draft planlandi.');
    }

    public function unscheduleDraft()
    {
        $pageCode = trim((string) $this->request->getPost('page_code'));
        $versionId = trim((string) $this->request->getPost('version_id'));

        $result = $this->pageBuilderService->unscheduleDraft($versionId);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('draft_error', (string) ($result['error'] ?? 'Draft planlamasi kaldirilamadi.'));
        }

        return redirect()->to(site_url('admin/pages/' . $pageCode . '/builder'))
            ->with('success', 'Draft tekrar taslak durumuna alindi.');
    }
}
