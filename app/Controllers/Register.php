<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\DTO\UserDTO;
use App\Services\AuthService;

class Register extends BaseController
{
    // KayÄ±t sayfasÄ±nÄ± (View) ekrana getiren fonksiyon
    public function index()
    {
        return view('register'); // Birazdan bu View dosyasÄ±nÄ± oluÅŸturacaÄŸÄ±z
    }

    // KayÄ±t iÅŸlemini gerÃ§ekleÅŸtiren fonksiyon
    public function save()
    {
        // 1. Formdan gelen verileri alÄ±p DTO paketine koyuyoruz
       // DTO ÅŸifreyi otomatik hash'liyor.
        $userDTO = new UserDTO($this->request->getPost());
        // 2. Modeli Ã§aÄŸÄ±rÄ±yoruz
        // $userModel = new UserModel
        // 2. Ä°ÅŸ MantÄ±ÄŸÄ± iÃ§in Servis katmanÄ±nÄ± Ã§aÄŸÄ±rÄ±yoruz
        $authService = new AuthService();

        // 3. VeritabanÄ±na kaydÄ± atÄ±yoruz
        /*$userModel->save([
            'username' => $userDTO->username,
            'email'    => $userDTO->email,
            'password' => $userDTO->password
        ]);*/
    // 3. KayÄ±t iÅŸlemini Servise devrediyoruz
    // Servis arka planda Model ile konuÅŸup veriyi kaydedecek.
       if ($authService->registerUser($userDTO)) {
        // 4. Ä°ÅŸlem bitince kullanÄ±cÄ±yÄ± login sayfasÄ±na yÃ¶nlendirip mesaj verelim
        return redirect()->to(base_url('login'))->with('success', 'Kayıt başarılı. Giriş yapabilirsiniz.');
    } else {
        // Hata durumunda geri gÃ¶nder
        return redirect()->back()->with('error', 'Kayıt sırasında bir hata oluştu.');
    }
    }
}
