<?php

namespace App\Services;

use App\Models\BannerModel;
use RuntimeException;

class BannerService
{
    private BannerModel $bannerModel;

    public function __construct(?BannerModel $bannerModel = null)
    {
        $this->bannerModel = $bannerModel ?? new BannerModel();
    }

    public function getBannerTypes(): array
    {
        return [
            'hero' => 'Hero Banner',
            'inline' => 'Ara Banner',
            'announcement' => 'Duyuru Bannerı',
        ];
    }

    public function defaultBanner(): array
    {
        return [
            'id' => null,
            'banner_name' => '',
            'banner_type' => 'hero',
            'banner_type_label' => 'Hero Banner',
            'title' => '',
            'subtitle' => '',
            'image_path' => '',
            'button_text' => '',
            'button_link' => '',
            'display_order' => 0,
            'is_active' => 1,
            'created_at' => null,
            'updated_at' => null,
            'created_at_label' => '-',
            'updated_at_label' => '-',
        ];
    }

    public function listBanners(): array
    {
        if (! $this->bannerTableExists()) {
            return [
                'items' => [],
                'summary' => [
                    'total' => 0,
                    'active' => 0,
                    'passive' => 0,
                ],
            ];
        }

        $items = array_map(fn (array $banner): array => $this->mapBanner($banner), $this->bannerModel->listForAdmin());
        $active = count(array_filter($items, static fn (array $banner): bool => (int) $banner['is_active'] === 1));

        return [
            'items' => $items,
            'summary' => [
                'total' => count($items),
                'active' => $active,
                'passive' => count($items) - $active,
            ],
        ];
    }

    public function getBannerById(?string $id): ?array
    {
        if (! $id || ! $this->bannerTableExists()) {
            return null;
        }

        $banner = $this->bannerModel->find($id);

        return $banner ? $this->mapBanner($banner) : null;
    }

    public function saveBanner(array $input, ?string $actorId = null): string
    {
        if (! $this->bannerTableExists()) {
            throw new RuntimeException('Banner tablosu henüz hazır değil. Lütfen migration çalıştırın.');
        }

        $id = trim((string) ($input['banner_id'] ?? ''));
        $payload = [
            'banner_name' => trim((string) ($input['banner_name'] ?? '')),
            'banner_type' => trim((string) ($input['banner_type'] ?? 'hero')),
            'title' => trim((string) ($input['title'] ?? '')),
            'subtitle' => $this->nullableText($input['subtitle'] ?? null),
            'image_path' => $this->nullableText($input['image_path'] ?? null),
            'button_text' => $this->nullableText($input['button_text'] ?? null),
            'button_link' => $this->nullableText($input['button_link'] ?? null),
            'display_order' => (int) ($input['display_order'] ?? 0),
            'is_active' => (int) ($input['is_active'] ?? 0) === 1 ? 1 : 0,
            'updated_by' => $actorId,
        ];

        if ($id !== '') {
            if (! $this->bannerModel->find($id)) {
                throw new RuntimeException('Düzenlenmek istenen banner bulunamadı.');
            }

            $this->bannerModel->update($id, $payload);

            return $id;
        }

        $payload['created_by'] = $actorId;
        $newId = $this->bannerModel->insert($payload, true);

        if (! $newId) {
            throw new RuntimeException('Banner kaydı oluşturulamadı.');
        }

        return (string) $newId;
    }

    public function toggleBanner(string $id, ?string $actorId = null): void
    {
        if (! $this->bannerTableExists()) {
            throw new RuntimeException('Banner tablosu henüz hazır değil. Lütfen migration çalıştırın.');
        }

        $banner = $this->bannerModel->find($id);
        if (! $banner) {
            throw new RuntimeException('Durumu değiştirilecek banner bulunamadı.');
        }

        $this->bannerModel->update($id, [
            'is_active' => (int) $banner['is_active'] === 1 ? 0 : 1,
            'updated_by' => $actorId,
        ]);
    }

    private function bannerTableExists(): bool
    {
        return db_connect()->tableExists('banners');
    }

    private function mapBanner(array $banner): array
    {
        $types = $this->getBannerTypes();
        $banner['banner_type_label'] = $types[$banner['banner_type']] ?? ucfirst((string) $banner['banner_type']);
        $banner['created_at_label'] = ! empty($banner['created_at']) ? date('d.m.Y H:i', strtotime((string) $banner['created_at'])) : '-';
        $banner['updated_at_label'] = ! empty($banner['updated_at']) ? date('d.m.Y H:i', strtotime((string) $banner['updated_at'])) : '-';

        return $banner;
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
