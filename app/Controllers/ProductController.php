<?php

namespace App\Controllers;

// DÄ±ÅŸarÄ±daki sÄ±nÄ±flarÄ± kullanabilmek iÃ§in 'use' ifadelerini ekliyoruz
use App\Services\ProductsService;
use App\DTO\ProductDTO;

class ProductController extends BaseController
{
    // Servis katmanÄ±nÄ± saklayacaÄŸÄ±mÄ±z deÄŸiÅŸken
    protected $productsService;

    /**
     * Constructor (YapÄ±cÄ± Metot)
     * HatayÄ± dÃ¼zeltmek iÃ§in dÄ±ÅŸarÄ±dan parametre almayÄ± bÄ±raktÄ±k.
     */
    public function __construct()
    {
        // Servisi manuel olarak burada oluÅŸturuyoruz
        $this->productsService = new ProductsService();
    }

    /**
     * ÃœrÃ¼nleri Listeleme EkranÄ±
     */
    public function index()
    {
        $products = $this->productsService->getActiveProducts();

        return view('site/products/index', [
            'products' => $products,
            'title'    => 'Kitap Dünyası | Tüm Kitaplar'
        ]);
    }


    public function detail($id)
    {
        $product = $this->productsService->getProductById($id);

        return view('site/products/product_detail', [
            'product' => $product
        ]);
    }

    public function selection()
    {
        return view('site/products/product_selection');
    }

    public function listByType($type)
    {
        // 1. Service'e gidip "Bana sadece bu tipteki Ã¼rÃ¼nleri getir" diyoruz
        $products = $this->productsService->getProductsByType($type);
        $categories = $this->productsService->getCategoriesByType($type);

        // 2. Sayfa baÅŸlÄ±ÄŸÄ±nÄ± dinamik yapalÄ±m (Ã–rn: BasÄ±lÄ± ÃœrÃ¼nler Koleksiyonu)
        $data = [
            'products'    => $products,
            'categories'  => $categories, // Bu satÄ±r butonlarÄ±n Ã§Ä±kmasÄ±nÄ± saÄŸlar
            'selectedCat' => 'all',       // Ä°lk giriÅŸte "TÃœMÃœ" aktif gÃ¶rÃ¼nsÃ¼n
            'type'        => $type,
            'title'       => ($type == 'basili' ? 'BasılıKitaplar' : ($type == 'dijital' ? 'Dijital Kitaplar' : 'Ortak Paketler'))
        ];

        // HatÄ±rlarsan products_view.php iÃ§inde kategori butonlarÄ±nÄ± ve kartlarÄ± tasarlamÄ±ÅŸtÄ±k
        return view('site/products/index', $data);
    }

    public function listByCategory($type, $categoryId = null) {
            // 1. Kategorileri de Ã§ekiyoruz (MenÃ¼de gÃ¶rÃ¼nmesi iÃ§in)
            
        $categories = $this->productsService->getCategoriesByType($type);
        
        // 2. FiltrelenmiÅŸ Ã¼rÃ¼nleri Ã§ek (AsÄ±l kitap kartlarÄ± burada geliyor!)
         $products = $this->productsService->getFilteredProducts($type, $categoryId);

        /*$data = [
            'products'    => $products,
            'categories'  => $categories, // EKSÄ°KTÄ°: Eklendi
            'type'        => $type,
            'selectedCat' => 'all', // VarsayÄ±lan olarak 'all' yaptÄ±k ki hepsi listelensin
            'title'       => ($type == 'basili' ? 'BasÄ±lÄ± Kitaplar' : ($type == 'dijital' ? 'Dijital Kitaplar' : 'Ortak Paketler'))
        ];

        return view('products_view', $data);*/
        return view('site/products/index', [
        'type'        => $type,
        'categories'  => $categories,
        'products'    => $products, // Service'den gelen dolu liste
        'selectedCat' => $categoryId,
        'title'       => ($type == 'basili' ? 'Basılı Kitaplar' : 'Dijital Kitaplar')
        ]);
    }

    //Formun iÃ§indeki "Kategori" aÃ§Ä±lÄ±r listesini (dropdown) doldurabilmek iÃ§in
    //veritabanÄ±ndaki tÃ¼m kategorileri Ã§ekip View'a gÃ¶ndermemiz gerekiyor.
            

}
