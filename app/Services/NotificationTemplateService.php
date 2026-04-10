<?php

namespace App\Services;

use App\Models\NotificationEmailTemplateModel;

class NotificationTemplateService
{
    public function __construct(
        private ?NotificationEmailTemplateModel $notificationEmailTemplateModel = null,
        private ?NotificationEmailService $notificationEmailService = null
    ) {
        $this->notificationEmailTemplateModel = $this->notificationEmailTemplateModel ?? new NotificationEmailTemplateModel();
        $this->notificationEmailService = $this->notificationEmailService ?? new NotificationEmailService();
    }

    public function getTemplateOptions(): array
    {
        return [
            'custom' => 'Manuel İçerik',
            'welcome' => 'Hoş Geldiniz',
            'campaign' => 'Kampanya Duyurusu',
            'reminder' => 'Hatırlatma',
        ];
    }

    public function getSupportedPlaceholders(): array
    {
        return [
            '{name}' => 'Alıcının adı',
            '{email}' => 'Alıcı e-posta adresi',
            '{site_name}' => 'Site adı',
        ];
    }

    public function getTemplateDefaults(): array
    {
        $definitions = $this->getTemplateDefinitions();
        $defaults = [];

        foreach ($definitions as $key => $definition) {
            $defaults[$key] = [
                'subject' => $definition['subject'],
                'message' => $definition['message'],
            ];
        }

        return $defaults;
    }

    public function listSavedTemplates(): array
    {
        if (! $this->templateTableExists()) {
            return [];
        }

        $items = [];
        foreach ($this->notificationEmailTemplateModel->listForAdmin() as $row) {
            $items[] = $this->mapTemplateForAdmin($row);
        }

        return $items;
    }

    public function getTemplateById(string $templateId): ?array
    {
        if (! $this->templateTableExists()) {
            return null;
        }

        $templateId = trim($templateId);
        if ($templateId === '') {
            return null;
        }

        $row = $this->notificationEmailTemplateModel->find($templateId);

        return is_array($row) ? $this->mapTemplateForAdmin($row) : null;
    }

    public function saveTemplate(array $input, ?string $actorId = null): array
    {
        if (! $this->templateTableExists()) {
            return [
                'success' => false,
                'error' => 'Hazır şablon tablosu henüz oluşturulmamış görünüyor.',
            ];
        }

        $templateId = trim((string) ($input['template_id'] ?? ''));
        $existing = $templateId !== '' ? $this->notificationEmailTemplateModel->find($templateId) : null;
        $actorId = $this->cleanActorId($actorId);

        $payload = [
            'template_name' => trim((string) ($input['template_name'] ?? '')),
            'template_type' => $this->sanitizeTemplateType((string) ($input['template_type'] ?? 'custom')),
            'subject' => trim((string) ($input['template_subject'] ?? '')),
            'message' => trim((string) ($input['template_message'] ?? '')),
            'is_active' => (int) ((string) ($input['template_is_active'] ?? '1') === '1'),
            'updated_by' => $actorId,
        ];

        if (is_array($existing)) {
            if (! $this->notificationEmailTemplateModel->update($templateId, $payload)) {
                return [
                    'success' => false,
                    'error' => 'Şablon güncellenemedi.',
                ];
            }

            return [
                'success' => true,
                'template_id' => $templateId,
            ];
        }

        $payload['id'] = NotificationEmailTemplateModel::uuidV4();
        $payload['created_by'] = $actorId;

        if (! $this->notificationEmailTemplateModel->insert($payload, false)) {
            return [
                'success' => false,
                'error' => 'Şablon kaydedilemedi.',
            ];
        }

        return [
            'success' => true,
            'template_id' => (string) $payload['id'],
        ];
    }

    public function sendStoredTemplateTest(string $templateId, string $toEmail, string $recipientName = ''): array
    {
        return $this->sendStoredTemplateTestWithContext($templateId, $toEmail, $recipientName);
    }

    public function sendStoredTemplateTestWithContext(string $templateId, string $toEmail, string $recipientName = '', array $context = []): array
    {
        if (! $this->templateTableExists()) {
            return [
                'success' => false,
                'error' => 'Hazır şablon tablosu henüz oluşturulmamış görünüyor.',
            ];
        }

        $template = $this->getTemplateById($templateId);
        if ($template === null) {
            return [
                'success' => false,
                'error' => 'Gönderilecek şablon bulunamadı.',
            ];
        }

        $rendered = $this->renderStoredTemplate($template, [
            'name' => $recipientName,
            'email' => $toEmail,
        ]);

        return $this->notificationEmailService->sendTestEmail(
            $toEmail,
            $rendered['subject'],
            $rendered['message'],
            [
                'template_type' => (string) ($template['template_type'] ?? ''),
                'source_type' => (string) ($context['source_type'] ?? 'template'),
                'created_by' => $context['created_by'] ?? null,
            ]
        );
    }

    public function renderTemplate(string $templateType, string $subject, string $message, array $variables = []): array
    {
        $template = $this->getTemplateDefinition($templateType);
        $variables = $this->normalizeVariables($variables);

        $resolvedSubject = trim($subject) !== '' ? trim($subject) : $template['subject'];
        $resolvedMessage = trim($message) !== '' ? trim($message) : $template['message'];

        return [
            'subject' => $this->replacePlaceholders($resolvedSubject, $variables),
            'message' => $this->replacePlaceholders($resolvedMessage, $variables),
        ];
    }

    private function renderStoredTemplate(array $template, array $variables = []): array
    {
        return $this->renderTemplate(
            (string) ($template['template_type'] ?? 'custom'),
            (string) ($template['subject'] ?? ''),
            (string) ($template['message'] ?? ''),
            $variables
        );
    }

    private function getTemplateDefinition(string $templateType): array
    {
        $templates = $this->getTemplateDefinitions();

        return $templates[$templateType] ?? $templates['custom'];
    }

    private function getTemplateDefinitions(): array
    {
        return [
            'custom' => [
                'subject' => 'BeAble Pro test e-postası',
                'message' => "Merhaba {name},\n\nBu alan manuel içerik için ayrılmıştır. Buraya kendi test mesajınızı güvenle yazabilir, konu ve metin üzerinde doğrudan düzenleme yapabilirsiniz.\n\nİletişim e-posta adresi: {email}",
            ],
            'welcome' => [
                'subject' => '{site_name} ailesine hoş geldiniz',
                'message' => "Merhaba {name},\n\n{site_name} deneyimine hoş geldiniz. Hesabınız için temel bilgilendirme süreci tamamlandı ve bu e-posta ilk karşılama mesajı olarak iletilmiştir.\n\nİletişim e-posta adresi: {email}",
            ],
            'campaign' => [
                'subject' => '{site_name} için size özel kampanya duyurusu',
                'message' => "Merhaba {name},\n\n{site_name} üzerinde sizin için hazırlanan kampanya içerikleri yayına alınmıştır. Güncel fırsatları inceleyerek avantajlı tekliflerden yararlanabilirsiniz.\n\nİletişim e-posta adresi: {email}",
            ],
            'reminder' => [
                'subject' => '{site_name} hatırlatma bildirimi',
                'message' => "Merhaba {name},\n\nBu mesaj {site_name} tarafından size özel bir hatırlatma iletmek için gönderilmiştir. İlgili işleminizi uygun olduğunuzda tamamlayabilirsiniz.\n\nİletişim e-posta adresi: {email}",
            ],
        ];
    }

    private function mapTemplateForAdmin(array $row): array
    {
        $templateType = $this->sanitizeTemplateType((string) ($row['template_type'] ?? 'custom'));

        return [
            'id' => (string) ($row['id'] ?? ''),
            'template_name' => (string) ($row['template_name'] ?? ''),
            'template_type' => $templateType,
            'template_type_label' => $this->getTemplateOptions()[$templateType] ?? 'Manuel İçerik',
            'subject' => (string) ($row['subject'] ?? ''),
            'message' => (string) ($row['message'] ?? ''),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'created_at_label' => $this->formatDateLabel((string) ($row['created_at'] ?? '')),
            'updated_at_label' => $this->formatDateLabel((string) ($row['updated_at'] ?? '')),
        ];
    }

    private function formatDateLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'Henüz yok';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d.m.Y H:i', $timestamp);
    }

    private function normalizeVariables(array $variables): array
    {
        $siteName = trim((string) ($variables['site_name'] ?? ''));
        if ($siteName === '') {
            $app = config('App');
            $siteName = trim((string) ($app->appName ?? ''));
        }

        if ($siteName === '') {
            $siteName = 'BeAble Pro';
        }

        return [
            '{name}' => trim((string) ($variables['name'] ?? '')) !== '' ? trim((string) $variables['name']) : 'Değerli Kullanıcı',
            '{email}' => trim((string) ($variables['email'] ?? '')),
            '{site_name}' => $siteName,
        ];
    }

    private function sanitizeTemplateType(string $templateType): string
    {
        $templateType = trim($templateType);

        return array_key_exists($templateType, $this->getTemplateOptions()) ? $templateType : 'custom';
    }

    private function cleanActorId(?string $actorId): ?string
    {
        $actorId = trim((string) $actorId);

        return $actorId !== '' ? $actorId : null;
    }

    private function replacePlaceholders(string $content, array $variables): string
    {
        return strtr($content, $variables);
    }

    private function templateTableExists(): bool
    {
        return db_connect()->tableExists('notification_email_templates');
    }
}
