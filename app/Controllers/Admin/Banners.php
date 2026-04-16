<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BannerService;
use RuntimeException;

class Banners extends BaseController
{
    private BannerService $bannerService;

    public function __construct()
    {
        $this->bannerService = new BannerService();
    }

    public function index()
    {
        $bannerData = $this->bannerService->listBanners();
        $bannerId = session()->getFlashdata('banner_drawer_id');
        $selectedBanner = $this->bannerService->getBannerById(is_string($bannerId) ? $bannerId : null);

        return view('admin/banners/index', [
            'title' => 'Banner Yönetimi',
            'banners' => $bannerData['items'],
            'summary' => $bannerData['summary'],
            'bannerTypes' => $this->bannerService->getBannerTypes(),
            'defaultBanner' => $this->bannerService->defaultBanner(),
            'selectedBanner' => $selectedBanner,
            'drawerShouldOpen' => (bool) session()->getFlashdata('banner_drawer_open'),
        ]);
    }

    public function save()
    {
        $validationRules = [
            'banner_id' => 'permit_empty|max_length[36]',
            'banner_name' => 'required|min_length[3]|max_length[160]',
            'banner_type' => 'required|in_list[hero,inline,announcement]',
            'title' => 'required|min_length[3]|max_length[180]',
            'subtitle' => 'permit_empty|max_length[1000]',
            'image_path' => 'permit_empty|max_length[255]',
            'button_text' => 'permit_empty|max_length[120]',
            'button_link' => 'permit_empty|max_length[255]',
            'display_order' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[999]',
            'is_active' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($validationRules)) {
            return redirect()->to(site_url('admin/banners'))
                ->withInput()
                ->with('error', 'Banner kaydı doğrulanamadı. Lütfen alanları kontrol edin.')
                ->with('validation', $this->validator->getErrors())
                ->with('banner_drawer_open', true)
                ->with('banner_drawer_id', $this->request->getPost('banner_id'));
        }

        try {
            $bannerId = $this->bannerService->saveBanner($this->request->getPost(), $this->actorId());
        } catch (RuntimeException $exception) {
            return redirect()->to(site_url('admin/banners'))
                ->withInput()
                ->with('error', $exception->getMessage())
                ->with('banner_drawer_open', true)
                ->with('banner_drawer_id', $this->request->getPost('banner_id'));
        }

        return redirect()->to(site_url('admin/banners'))
            ->with('success', 'Banner kaydı başarıyla kaydedildi.')
            ->with('banner_drawer_open', true)
            ->with('banner_drawer_id', $bannerId);
    }

    public function toggle(string $id)
    {
        try {
            $this->bannerService->toggleBanner($id, $this->actorId());

            return redirect()->to(site_url('admin/banners'))
                ->with('success', 'Banner durumu güncellendi.');
        } catch (RuntimeException $exception) {
            return redirect()->to(site_url('admin/banners'))
                ->with('error', $exception->getMessage());
        }
    }

    private function actorId(): ?string
    {
        $userId = session('user_id') ?? session('id') ?? null;

        return is_scalar($userId) ? (string) $userId : null;
    }
}
