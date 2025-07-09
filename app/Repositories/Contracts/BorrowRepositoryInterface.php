<?php

namespace App\Repositories\Contracts;

use App\Models\Borrow;

Interface BorrowRepositoryInterface
{
    public function getAll(array $filters = []);
    public function getOverdueBorrows();
    public function getUserOverdueBorrows($userId);
    public function details($id);
    public function create(array $data);
    public function update(Borrow $borrow, array $data);
    public function delete(Borrow $borrow);

    // Return
    public function getReturnedBorrows();
    // public function search($query);
}
