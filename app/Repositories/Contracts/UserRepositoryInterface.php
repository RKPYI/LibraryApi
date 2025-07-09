<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function getAll();
    public function details($id);
    public function update($id, array $data);
    public function delete($id);
}
