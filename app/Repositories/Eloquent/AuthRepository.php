<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function findUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function createUser(array $data)
    {
        return User::create($data);
    }
}
