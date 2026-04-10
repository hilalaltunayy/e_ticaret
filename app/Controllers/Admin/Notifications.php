<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\NotificationEmailService;
use App\Services\NotificationLogService;
use App\Services\NotificationSmsService;
use App\Services\NotificationTemplateService;

class Notifications extends BaseController
{
    public function __construct(
        private ?NotificationEmailService $notificationEmailService = null,
        private ?NotificationTemplateService $notificationTemplateService = null,
        private ?NotificationLogService $notificationLogService = null,
        private ?NotificationSmsService $notificationSmsService = null
    ) {
        $this->notificationEmailService = $this->notificationEmailService ?? new NotificationEmailService();
        $this->notificationTemplateService = $this->notificationTemplateService ?? new NotificationTemplateService();
        $this->notificationLogService = $this->notificationLogService ?? new NotificationLogService();
        $this->notificationSmsService = $this->notificationSmsService ?? new NotificationSmsService();
    }

    public function index()
    {
        $selectedTemplateId = (string) (session()->getFlashdata('template_drawer_id') ?? '');
        $selectedTemplate = $selectedTemplateId !== ''
            ? $this->notificationTemplateService->getTemplateById($selectedTemplateId)
            : null;
        $savedTemplates = $this->notificationTemplateService->listSavedTemplates();
        $draftTemplateCount = count(array_filter($savedTemplates, static fn (array $item): bool => ! ($item['is_active'] ?? false)));
        $recentEmailLogs = $this->notificationLogService->getRecentDeliveryLogs(5);

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
                    'status' => 'Test gönderimi hazır',
                    'statusClass' => 'bg-light-warning text-warning',
                ],
            ],
            'historySummary' => $this->notificationLogService->getDeliveryHistorySummary($draftTemplateCount),
            'recentEmailLogs' => $recentEmailLogs,
            'placeholders' => [
                [
                    'title' => 'Hazır Şablonlar',
                    'description' => 'Hoş geldiniz, kampanya ve hatırlatma akışları tekli test gönderimi için hazırlandı.',
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
            'templateOptions' => $this->notificationTemplateService->getTemplateOptions(),
            'templateDefaults' => $this->notificationTemplateService->getTemplateDefaults(),
            'placeholderHelp' => $this->notificationTemplateService->getSupportedPlaceholders(),
            'savedTemplates' => $savedTemplates,
            'selectedTemplate' => $selectedTemplate,
            'drawerShouldOpen' => (bool) session()->getFlashdata('template_drawer_open'),
        ]);
    }

    public function sendTestEmail()
    {
        $validator = \Config\Services::validation();
        $post = [
            'test_email_template' => trim((string) $this->request->getPost('test_email_template')),
            'test_email_name' => trim((string) $this->request->getPost('test_email_name')),
            'test_email_to' => trim((string) $this->request->getPost('test_email_to')),
            'test_email_subject' => trim((string) $this->request->getPost('test_email_subject')),
            'test_email_message' => trim((string) $this->request->getPost('test_email_message')),
        ];

        $validator->setRules([
            'test_email_template' => 'required|max_length[50]',
            'test_email_name' => 'permit_empty|max_length[120]',
            'test_email_to' => 'required|valid_email|max_length[160]',
            'test_email_subject' => 'required|min_length[3]|max_length[180]',
            'test_email_message' => 'required|min_length[5]|max_length[5000]',
        ]);

        if (! $validator->run($post)) {
            $errors = $validator->getErrors();
            $message = reset($errors);

            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', is_string($message) ? $message : 'Form alanlarını kontrol edin.');
        }

        $renderedEmail = $this->notificationTemplateService->renderTemplate(
            $post['test_email_template'],
            $post['test_email_subject'],
            $post['test_email_message'],
            [
                'name' => $post['test_email_name'],
                'email' => $post['test_email_to'],
            ]
        );

        $result = $this->notificationEmailService->sendTestEmail(
            $post['test_email_to'],
            $renderedEmail['subject'],
            $renderedEmail['message'],
            [
                'template_type' => $post['test_email_template'],
                'source_type' => 'manual',
                'created_by' => $this->actorId(),
            ]
        );

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Test e-postası gönderilemedi.'));
        }

        return redirect()->to(site_url('admin/notifications'))
            ->with('success', 'Test e-postası şablonlu içerikle gönderildi.');
    }

    public function sendTestSms()
    {
        $validator = \Config\Services::validation();
        $post = [
            'test_sms_to' => trim((string) $this->request->getPost('test_sms_to')),
            'test_sms_name' => trim((string) $this->request->getPost('test_sms_name')),
            'test_sms_message' => trim((string) $this->request->getPost('test_sms_message')),
        ];

        $validator->setRules([
            'test_sms_to' => 'required|min_length[10]|max_length[20]|regex_match[/^[0-9+\s\-\(\)]+$/]',
            'test_sms_name' => 'permit_empty|max_length[120]',
            'test_sms_message' => 'required|min_length[3]|max_length[612]',
        ]);

        if (! $validator->run($post)) {
            $errors = $validator->getErrors();
            $message = reset($errors);

            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', is_string($message) ? $message : 'SMS form alanlarını kontrol edin.');
        }

        $result = $this->notificationSmsService->sendTestSms(
            $post['test_sms_to'],
            $post['test_sms_name'],
            $post['test_sms_message'],
            [
                'source_type' => 'sms_test',
                'created_by' => $this->actorId(),
            ]
        );

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('error', (string) ($result['error'] ?? 'Test SMS gönderilemedi.'));
        }

        return redirect()->to(site_url('admin/notifications'))
            ->with('success', 'Test SMS gönderimi başlatıldı.');
    }

    public function saveTemplate()
    {
        $validator = \Config\Services::validation();
        $post = [
            'template_id' => trim((string) $this->request->getPost('template_id')),
            'template_name' => trim((string) $this->request->getPost('template_name')),
            'template_type' => trim((string) $this->request->getPost('template_type')),
            'template_subject' => trim((string) $this->request->getPost('template_subject')),
            'template_message' => trim((string) $this->request->getPost('template_message')),
            'template_is_active' => (string) $this->request->getPost('template_is_active'),
        ];

        $validator->setRules([
            'template_id' => 'permit_empty|max_length[36]',
            'template_name' => 'required|min_length[3]|max_length[160]',
            'template_type' => 'required|max_length[32]',
            'template_subject' => 'required|min_length[3]|max_length[180]',
            'template_message' => 'required|min_length[5]|max_length[5000]',
            'template_is_active' => 'required|in_list[0,1]',
        ]);

        if (! $validator->run($post)) {
            $errors = $validator->getErrors();
            $message = reset($errors);

            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('template_drawer_open', true)
                ->with('template_drawer_id', $post['template_id'])
                ->with('error', is_string($message) ? $message : 'Şablon alanlarını kontrol edin.');
        }

        $result = $this->notificationTemplateService->saveTemplate($post, $this->actorId());

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('template_drawer_open', true)
                ->with('template_drawer_id', $post['template_id'])
                ->with('error', (string) ($result['error'] ?? 'Şablon kaydedilemedi.'));
        }

        $templateId = (string) ($result['template_id'] ?? '');

        return redirect()->to(site_url('admin/notifications'))
            ->with('template_drawer_open', true)
            ->with('template_drawer_id', $templateId)
            ->with('success', 'Hazır e-posta şablonu kaydedildi.');
    }

    public function sendSavedTemplateTest()
    {
        $validator = \Config\Services::validation();
        $post = [
            'template_id' => trim((string) $this->request->getPost('template_id')),
            'template_test_email_to' => trim((string) $this->request->getPost('template_test_email_to')),
            'template_test_email_name' => trim((string) $this->request->getPost('template_test_email_name')),
        ];

        $validator->setRules([
            'template_id' => 'required|max_length[36]',
            'template_test_email_to' => 'required|valid_email|max_length[160]',
            'template_test_email_name' => 'permit_empty|max_length[120]',
        ]);

        if (! $validator->run($post)) {
            $errors = $validator->getErrors();
            $message = reset($errors);

            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('template_drawer_open', true)
                ->with('template_drawer_id', $post['template_id'])
                ->with('error', is_string($message) ? $message : 'Test gönderim alanlarını kontrol edin.');
        }

        $result = $this->notificationTemplateService->sendStoredTemplateTestWithContext(
            $post['template_id'],
            $post['template_test_email_to'],
            $post['template_test_email_name'],
            [
                'source_type' => 'template',
                'created_by' => $this->actorId(),
            ]
        );

        if (! ($result['success'] ?? false)) {
            return redirect()->to(site_url('admin/notifications'))
                ->withInput()
                ->with('template_drawer_open', true)
                ->with('template_drawer_id', $post['template_id'])
                ->with('error', (string) ($result['error'] ?? 'Hazır şablon ile test e-postası gönderilemedi.'));
        }

        return redirect()->to(site_url('admin/notifications'))
            ->with('template_drawer_open', true)
            ->with('template_drawer_id', $post['template_id'])
            ->with('success', 'Hazır şablon üzerinden test e-postası gönderildi.');
    }

    private function actorId(): ?string
    {
        $actorId = trim((string) (session()->get('user_id') ?? ''));

        return $actorId !== '' ? $actorId : null;
    }
}
