<?php

namespace App\DTO;

class ProductDTO
{
    public ?string $id;
    public string $product_name;
    public ?string $author;
    public mixed $category_id;
    public ?string $category_name = null;
    public ?string $description;
    public float $price;
    public ?int $stock;
    public string $type;
    public ?string $image;
    public ?string $image_url = null;
    public ?string $detail_url = null;
    public ?string $created_at = null;

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (string) $data['id'] : null;
        $this->product_name = (string) ($data['product_name'] ?? 'Isimsiz Urun');
        $this->author = isset($data['author']) ? (string) $data['author'] : 'Yazar Belirtilmemis';
        $this->category_id = $data['category_id'] ?? null;
        $this->category_name = isset($data['category_name'])
            ? (string) $data['category_name']
            : (isset($data['category']) ? (string) $data['category'] : null);
        $this->description = isset($data['description']) ? (string) $data['description'] : null;
        $this->price = (float) ($data['price'] ?? 0);
        $this->type = (string) ($data['type'] ?? 'basili');
        $this->image = isset($data['image']) ? (string) $data['image'] : null;
        $this->created_at = isset($data['created_at']) ? (string) $data['created_at'] : null;
        $this->stock = $this->type !== 'dijital'
            ? (int) ($data['stock_count'] ?? ($data['stock'] ?? 0))
            : 0;
    }
}
