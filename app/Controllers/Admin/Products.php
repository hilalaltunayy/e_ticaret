<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ProductsService;

class Products extends BaseController
{
    public function __construct(
        private ?ProductsService $productsService = null
    ) {
        $this->productsService = $this->productsService ?? new ProductsService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];
        $authors = $this->productsService->getAdminAuthors();

        return view('admin/products/index', [
            'title' => 'Ürünler',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'authors' => $authors,
        ]);
    }

    public function datatables()
    {
        $params = $this->request->getGet();
        $result = $this->productsService->datatablesList($params);
        $rows = $result['data'] ?? [];

        $data = array_map(function (array $row) {
            $id = (string) ($row['id'] ?? '');
            $active = (int) ($row['is_active'] ?? 0) === 1
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-danger text-danger">Pasif</span>';

            $editHref = $id !== '' ? site_url('admin/products/edit/' . $id) : '#';
            $deactivateHref = $id !== '' ? site_url('admin/products/deactivate/' . $id) : '#';

            return [
                'id' => esc($id),
                'product_name' => esc((string) ($row['product_name'] ?? '-')),
                'author_name' => esc((string) ($row['author_name'] ?? '-')),
                'type' => esc((string) ($row['type'] ?? '-')),
                'category_name' => esc((string) ($row['category_name'] ?? '-')),
                'price' => number_format((float) ($row['price'] ?? 0), 2, ',', '.'),
                'stock_overview' => sprintf(
                    '<span class="badge bg-light-primary text-primary me-1">Stok: %d</span><span class="badge bg-light-warning text-warning me-1">Rezerve: %d</span><span class="badge bg-light-success text-success">Satılabilir: %d</span>',
                    (int) ($row['stock_count'] ?? 0),
                    (int) ($row['reserved_count'] ?? 0),
                    (int) ($row['available_stock'] ?? 0)
                ),
                'is_active' => $active,
                'actions' => '<a href="' . esc($editHref) . '" class="btn btn-sm btn-outline-primary me-1">Düzenle</a>'
                    . '<a href="' . esc($deactivateHref) . '" class="btn btn-sm btn-outline-danger">Satıştan kaldır</a>',
            ];
        }, $rows);

        return $this->response->setJSON([
            'draw' => (int) ($params['draw'] ?? 0),
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $data,
        ]);
    }

    public function create()
    {
        $user = session()->get('user') ?? [];

        return view('admin/products/product_create', [
            'title' => 'Create Product',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'categories' => $this->productsService->getAdminCategories(),
            'authors' => $this->productsService->getAdminAuthors(),
            'latestAuthors' => $this->productsService->getLatestAdminAuthors(5),
            'types' => $this->productsService->getAdminTypes(),
            'validation' => session('validation'),
        ]);
    }

    public function store()
    {
        $newCategory = trim((string) ($this->request->getPost('new_category_name') ?? ''));

        $rules = [
            'product_name' => 'required|min_length[2]|max_length[255]',
            'type' => 'required|in_list[basili,dijital,paket]',
            'category_id' => $newCategory === '' ? 'required' : 'permit_empty',
            'author_id' => 'permit_empty|max_length[64]',
            'new_author_name' => 'permit_empty|min_length[2]|max_length[255]',
            'new_category_name' => 'permit_empty|min_length[2]|max_length[255]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'stock_count' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Lütfen zorunlu alanları kontrol edin.');
        }

        $input = $this->request->getPost();
        $authorId = trim((string) ($input['author_id'] ?? ''));
        $newAuthorName = trim((string) ($input['new_author_name'] ?? ''));
        $categoryId = trim((string) ($input['category_id'] ?? ''));

        if ($authorId === '__new__') {
            if ($newAuthorName === '') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Yeni yazar adı zorunludur.');
            }

            $createdAuthorId = $this->productsService->findOrCreateAuthorByName($newAuthorName);
            $authorId = (string) $createdAuthorId;

            if ($authorId === '' || $authorId === '0') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Yazar oluşturulamadı. Lütfen tekrar deneyin.');
            }
        }

        if ($newCategory !== '') {
            $createdCategoryId = $this->productsService->findOrCreateCategoryByName($newCategory);
            $categoryId = (string) $createdCategoryId;

            if ($categoryId === '' || $categoryId === '0') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Kategori oluşturulamadı. Lütfen tekrar deneyin.');
            }
        }

        $payload = [
            'product_name' => trim((string) ($input['product_name'] ?? '')),
            'author_id' => $authorId,
            'author' => '',
            'category_id' => $categoryId,
            'description' => trim((string) ($input['description'] ?? '')),
            'price' => (float) ($input['price'] ?? 0),
            'stock_count' => (int) ($input['stock_count'] ?? 0),
            'type' => (string) ($input['type'] ?? 'basili'),
            'is_active' => (int) ($input['is_active'] ?? 1),
        ];

        $createdId = $this->productsService->createProduct($payload);

        if (! $createdId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ürün kaydedilemedi. Lütfen tekrar deneyin.');
        }

        return redirect()->to(site_url('admin/products'))
            ->with('success', 'Ürün başarıyla eklendi.');
    }
}
