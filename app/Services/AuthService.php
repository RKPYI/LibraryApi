<?php

namespace App\Services;

use App\Repositories\Contracts\AuthRepositoryInterface;

class AuthService
{
    protected AuthRepositoryInterface $authRepo;

    public function __construct(AuthRepositoryInterface $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function authenticate(array $credentials)
    {
        $user = $this->authRepo->findUserByEmail($credentials['email']);

        if ($user && password_verify($credentials['password'], $user->password)) {
            return $user;
        }

        return null;
    }

    public function register(array $data)
    {
        return $this->authRepo->createUser($data);
    }
}
