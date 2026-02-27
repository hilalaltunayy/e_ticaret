<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuthorModel;
use App\Models\ProductsModel;
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

        return view('admin/products/index', [
            'title' => 'Urunler',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
        ]);
    }
    public function datatables()
    {
        $params = $this->request->getGet();
        $result = $this->productsService->datatablesList($params);
        $rows = $result['data'] ?? [];

        $data = array_map(function (array $row) {
            $id = (string) ($row['id'] ?? '');
            $isActive = (int) ($row['is_active'] ?? 0) === 1
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-danger text-danger">Pasif</span>';

            $editHref = $id !== '' ? site_url('admin/products/edit/' . $id) : '#';
            $deactivateHref = $id !== '' ? site_url('admin/stock/deactivate/' . $id) : '#';

            return [
                'id' => esc($id),
                'title' => esc((string) ($row['title'] ?? '-')),
                'author_name' => esc((string) ($row['author_name'] ?? '-')),
                'type' => esc((string) ($row['type'] ?? '-')),
                'category_name' => esc((string) ($row['category_name'] ?? '-')),
                'price' => number_format((float) ($row['price'] ?? 0), 2, ',', '.'),
                'stock_total' => (int) ($row['stock_total'] ?? 0),
                'stock_reserved' => (int) ($row['stock_reserved'] ?? 0),
                'stock_available' => (int) ($row['stock_available'] ?? 0),
                'is_active' => $isActive,
                'actions' => '<a href="' . esc($editHref) . '" class="btn btn-sm btn-outline-primary me-1">Düzenle</a>'
                    . '<form method="post" action="' . esc($deactivateHref) . '" class="d-inline">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-sm btn-outline-danger">Satıştan kaldır</button>'
                    . '</form>',
            ];
        }, $rows);

        $payload = [
            'draw' => (int) ($params['draw'] ?? 0),
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $data,
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    public function create()
    {
        $user = session()->get('user') ?? [];

        return view('admin/products/product_create', [
            'title' => 'Yeni Urun',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'categories' => $this->productsService->getAdminCategories(),
            'authors' => $this->productsService->getAdminAuthors(),
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
            'category_id' => $newCategory === '' ? 'required|max_length[64]' : 'permit_empty|max_length[64]',
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
                ->with('error', 'Lutfen zorunlu alanlari kontrol edin.');
        }

        $input = $this->request->getPost();
        $authorId = trim((string) ($input['author_id'] ?? ''));
        $newAuthorName = trim((string) ($input['new_author_name'] ?? ''));
        $categoryId = trim((string) ($input['category_id'] ?? ''));

        if ($categoryId === '__new__') {
            return redirect()->back()->withInput()->with('error', 'Lutfen once yeni kategoriyi olusturun.');
        }

        if ($authorId === '__new__') {
            if ($newAuthorName === '') {
                return redirect()->back()->withInput()->with('error', 'Yeni yazar adi zorunludur.');
            }

            $createdAuthorId = $this->productsService->findOrCreateAuthorByName($newAuthorName);
            $authorId = (string) $createdAuthorId;

            if ($authorId === '' || $authorId === '0') {
                return redirect()->back()->withInput()->with('error', 'Yazar olusturulamadi. Lutfen tekrar deneyin.');
            }
        }

        if ($newCategory !== '') {
            $createdCategoryId = $this->productsService->findOrCreateCategoryByName($newCategory);
            $categoryId = (string) $createdCategoryId;

            if ($categoryId === '' || $categoryId === '0') {
                return redirect()->back()->withInput()->with('error', 'Kategori olusturulamadi. Lutfen tekrar deneyin.');
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
                ->with('error', 'Urun kaydedilemedi. Lutfen tekrar deneyin.');
        }

        return redirect()->to(site_url('admin/products'))
            ->with('success', 'Urun basariyla eklendi.');
    }

    public function edit(string $id)
    {
        $user = session()->get('user') ?? [];
        $product = (new ProductsModel())->find($id);

        if (! $product) {
            return redirect()->to(site_url('admin/products'))->with('error', 'Urun bulunamadi.');
        }

        return view('admin/products/product_edit', [
            'title' => 'Urun Duzenle',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'product' => $product,
            'authors' => $this->productsService->getAdminAuthors(),
            'validation' => session('validation'),
        ]);
    }

    public function update(string $id)
    {
        $productsModel = new ProductsModel();
        $product = $productsModel->find($id);

        if (! $product) {
            return redirect()->to(site_url('admin/products'))->with('error', 'Urun bulunamadi.');
        }

        $rules = [
            'product_name' => 'required|min_length[2]|max_length[255]',
            'author_id' => 'required|max_length[64]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'description' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Lutfen form alanlarini kontrol edin.');
        }

        $authorId = trim((string) $this->request->getPost('author_id'));
        if ($authorId === '__new__') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lutfen once yeni yazari olusturun.');
        }
        if ((new AuthorModel())->find($authorId) === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Secilen yazar bulunamadi.');
        }

        // Stok alanlarini bilerek disarida birakiyoruz.
        $updateData = [
            'product_name' => trim((string) $this->request->getPost('product_name')),
            'author_id' => $authorId,
            'price' => (float) $this->request->getPost('price'),
            'description' => trim((string) ($this->request->getPost('description') ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (! $productsModel->update($id, $updateData)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Urun guncellenemedi.');
        }

        return redirect()->to(site_url('admin/products'))
            ->with('success', 'Urun bilgileri guncellendi.');
    }


    public function createAuthor()
    {
        $return = trim((string) ($this->request->getGet('return') ?? site_url('admin/products')));

        return view('admin/authors/create', [
            'title' => 'Yeni Yazar',
            'returnUrl' => $return,
            'validation' => session('validation'),
        ]);
    }

    public function storeAuthor()
    {
        $returnUrl = trim((string) ($this->request->getPost('return_url') ?? site_url('admin/products')));
        $name = trim((string) ($this->request->getPost('name') ?? ''));

        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'return_url' => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Yazar bilgisi gecersiz.');
        }

        $authorId = (string) $this->productsService->findOrCreateAuthorByName($name);
        if ($authorId === '' || $authorId === '0') {
            return redirect()->back()->withInput()->with('error', 'Yazar olusturulamadi.');
        }

        return redirect()->to($returnUrl)
            ->with('success', 'Yazar kaydedildi.')
            ->with('new_author_id', $authorId);
    }

    public function createCategory()
    {
        $return = trim((string) ($this->request->getGet('return') ?? site_url('admin/products/create')));

        return view('admin/categories/create', [
            'title' => 'Yeni Kategori',
            'returnUrl' => $return,
            'validation' => session('validation'),
        ]);
    }

    public function storeCategory()
    {
        $returnUrl = trim((string) ($this->request->getPost('return_url') ?? site_url('admin/products/create')));
        $name = trim((string) ($this->request->getPost('category_name') ?? ''));

        $rules = [
            'category_name' => 'required|min_length[2]|max_length[255]',
            'return_url' => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Kategori bilgisi gecersiz.');
        }

        $categoryId = (string) $this->productsService->findOrCreateCategoryByName($name);
        if ($categoryId === '' || $categoryId === '0') {
            return redirect()->back()->withInput()->with('error', 'Kategori olusturulamadi.');
        }

        return redirect()->to($returnUrl)
            ->with('success', 'Kategori kaydedildi.')
            ->with('new_category_id', $categoryId);
    }
}




