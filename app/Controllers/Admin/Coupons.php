<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\DTO\Marketing\CouponDTO;
use App\Services\CouponService;

class Coupons extends BaseController
{
    public function __construct(private ?CouponService $couponService = null)
    {
        $this->couponService = $this->couponService ?? new CouponService();
    }

    public function index()
    {
        $payload = $this->couponService->listCoupons();

        return view('admin/coupons/index', [
            'title' => 'Kupon Yönetimi',
            'coupons' => $payload['items'] ?? [],
            'summary' => $payload['summary'] ?? [],
        ]);
    }

    public function create()
    {
        return view('admin/coupons/create', [
            'title' => 'Kupon Ekle',
            'meta' => $this->couponService->getCouponFormMeta(),
            'formData' => $this->defaultFormData(),
            'errors' => session('coupon_errors') ?? [],
        ]);
    }

    public function store()
    {
        $dto = CouponDTO::fromRequest($this->request->getPost());
        $result = $this->couponService->createCoupon($dto, $this->actorId());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('coupon_errors', $result['errors'] ?? ['Kupon kaydedilemedi.']);
        }

        return redirect()->to(site_url('admin/coupons'))->with('success', 'Kupon oluşturuldu.');
    }

    public function edit(string $id)
    {
        $coupon = $this->couponService->getCouponForEdit($id);
        if (! is_array($coupon)) {
            return redirect()->to(site_url('admin/coupons'))->with('error', 'Kupon bulunamadı.');
        }

        return view('admin/coupons/edit', [
            'title' => 'Kupon Düzenle',
            'couponId' => $id,
            'meta' => $this->couponService->getCouponFormMeta(),
            'formData' => $coupon,
            'errors' => session('coupon_errors') ?? [],
        ]);
    }

    public function update(string $id)
    {
        $dto = CouponDTO::fromRequest($this->request->getPost());
        $result = $this->couponService->updateCoupon($id, $dto, $this->actorId());

        if (! ($result['success'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('coupon_errors', $result['errors'] ?? ['Kupon güncellenemedi.']);
        }

        return redirect()->to(site_url('admin/coupons'))->with('success', 'Kupon güncellendi.');
    }

    public function toggle(string $id)
    {
        $ok = $this->couponService->toggleCouponStatus($id, $this->actorId());
        if (! $ok) {
            return redirect()->to(site_url('admin/coupons'))->with('error', 'Kupon durumu güncellenemedi.');
        }

        return redirect()->to(site_url('admin/coupons'))->with('success', 'Kupon durumu güncellendi.');
    }

    public function delete(string $id)
    {
        $ok = $this->couponService->deleteCoupon($id, $this->actorId());
        if (! $ok) {
            return redirect()->to(site_url('admin/coupons'))->with('error', 'Kupon silinemedi.');
        }

        return redirect()->to(site_url('admin/coupons'))->with('success', 'Kupon silindi.');
    }

    private function actorId(): ?string
    {
        $user = session()->get('user') ?? [];
        $id = trim((string) ($user['id'] ?? $user['user_id'] ?? ''));
        return $id === '' ? null : $id;
    }

    private function defaultFormData(): array
    {
        return [
            'code' => old('code', ''),
            'coupon_kind' => old('coupon_kind', 'discount'),
            'discount_type' => old('discount_type', 'percent'),
            'discount_value' => old('discount_value', ''),
            'min_cart_amount' => old('min_cart_amount', ''),
            'max_usage_total' => old('max_usage_total', ''),
            'max_usage_per_user' => old('max_usage_per_user', ''),
            'starts_at' => old('starts_at', ''),
            'ends_at' => old('ends_at', ''),
            'is_active' => (int) old('is_active', 1),
            'is_first_order_only' => (int) old('is_first_order_only', 0),
            'category_ids' => old('category_ids', []),
            'product_ids' => old('product_ids', []),
        ];
    }
}

