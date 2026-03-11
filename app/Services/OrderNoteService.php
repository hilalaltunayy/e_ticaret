<?php

namespace App\Services;

use App\Models\OrderModel;

class OrderNoteService
{
    public function __construct(
        private ?OrderModel $orderModel = null
    ) {
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function addAdminNoteToOrderIdentifier(string $identifier, string $note, array $actor): array
    {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'type' => 'not_found',
                'order' => null,
                'note' => trim($note),
            ];
        }

        $normalizedNote = trim($note);
        $prefix = '[' . date('Y-m-d H:i') . '] ' . (trim((string) ($actor['role'] ?? '')) !== '' ? trim((string) ($actor['role'] ?? '')) : 'admin');
        $existing = trim((string) ($order['notes_admin'] ?? ''));
        $updatedNote = $existing === '' ? ($prefix . ': ' . $normalizedNote) : ($existing . PHP_EOL . $prefix . ': ' . $normalizedNote);
        $actorId = trim((string) ($actor['id'] ?? ''));

        $this->orderModel->update((string) $order['id'], [
            'notes_admin' => $updatedNote,
            'updated_by' => $actorId !== '' ? $actorId : null,
        ]);

        return [
            'success' => true,
            'type' => 'success',
            'order' => $order,
            'note' => $normalizedNote,
        ];
    }
}
