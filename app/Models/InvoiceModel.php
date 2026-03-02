<?php

namespace App\Models;

class InvoiceModel extends BaseUuidModel
{
    protected $table            = 'invoices';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id',
        'order_id',
        'invoice_no',
        'series',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'grand_total',
        'vat_rate',
        'ubl_xml_path',
        'pdf_path',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $useAutoIncrement = false;

    public function findByOrderId(string $orderId): ?array
    {
        $id = trim($orderId);
        if ($id === '') {
            return null;
        }

        return $this->where('order_id', $id)->first();
    }
}
