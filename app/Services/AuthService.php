<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Models\UserModel;

class AuthService
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function registerUser(UserDTO $userDTO)
    {
        return $this->userModel->save([
            'username' => $userDTO->username,
            'email'    => $userDTO->email,
            'password' => $userDTO->password,
        ]);
    }

    public function attemptLogin($email, $password)
    {
        $user = $this->userModel->findByEmail((string) $email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
