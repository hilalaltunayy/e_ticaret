<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\NotificationEmailService;

class Notifications extends BaseController
{
    public function __construct(
        private ?NotificationEmailService $notificationEmailService = null
    ) {
        $this->notificationEmailService = $this->notificationEmailService ?? new NotificationEmailService();
    }

    public function index()
    {
        return view('admin/notifications/index', [
            'title' => 'E-posta / SMS Gönderimi',
            'deliveryChannels' => [
                [
                    'title' => 'E-posta Gönderimi',
                    'icon' => 'ti ti-mail',
                    'description' => 'Toplu e-posta, kampanya duyurusu ve operasyonel bildirim akışları için başlangıç alanı.',
                    'status' => 'Test gönderimi açık',
                    'statusClass' => 'bg-light-success text-success',
                ],
                [
                    'title' => 'SMS Gönderimi',
                    'icon' => 'ti ti-device-mobile-message',
                    'description' => 'Kısa bilgilendirme, hatırlatma ve hızlı erişim mesajları için başlangıç alanı.',
                    'status' => 'Yakında aktif',
                    'statusClass' => 'bg-light-secondary text-secondary',
                ],
            ],
            'historySummary' => [
                ['label' => 'Bugün planlanan', 'value' => '0'],
                ['label' => 'Son başarılı gönderim', 'value' => 'Henüz yok'],
                ['label' => 'Taslak şablon', 'value' => '3'],
            ],
            'placeholders' => [
                [
                    'title' => 'Hazır Şablonlar',
                    'description' => 'Sipariş, kampanya ve duyuru şablonları bu alanda listelenecek.',
                ],
                [
                    'title' => 'Gönderim Listeleri',
                    'description' => 'Hedef kitle seçimleri ve filtrelenmiş alıcı segmentleri yakında eklenecek.',
                ],
                [
                    'title' => 'Yakında Eklenecek Alanlar',
                    'description' => 'Zamanlanmış gönderim, raporlama ve kanal bazlı durum izleme sonraki sprintlere bırakıldı.',
                ],
            ],
        ]);
    }

    public function sendTestEmail()
    {
        $validator = \Config\Services::validation();
        $post = [
            'test_email_to' => trim((string) $this->request->getPost('test_email_to')),
            'test_email_subject' => trim((string) $this->request->getPost('test_email_subject')),
            'test_email_message' => trim((string) $this->request->getPost('test_email_message')),
        ];

        $validator->setRules([
            'test_email_to' => 'required|valid_email|max_length[160]',
            'test_email_subject' => 'required|min_length[3]|max_length[180]',
            'test_email_message' => 'required|min_length[5]|max_length[5000]',
        ]);

        if (! $validator->run($post)) {
            $errors = $validator->getErrors();
            $message = reset($errors);

            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', is_string($message) ? $message : 'Form alanlarini kontrol edin.');
        }

        $result = $this->notificationEmailService->sendTestEmail(
            $post['test_email_to'],
            $post['test_email_subject'],
            $post['test_email_message']
        );

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Test e-postasi gonderilemedi.'));
        }

        return redirect()->to(site_url('admin/notifications'))
            ->with('success', 'Test e-postasi gonderildi.');
    }
}
