<?php

namespace App\Repositories\Contracts;

interface AuthRepositoryInterface
{
    public function findUserByEmail(string $email);
    public function createUser(array $data);
}
