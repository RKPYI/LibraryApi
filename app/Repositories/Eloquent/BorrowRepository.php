<?php

namespace App\Repositories\Eloquent;

use App\Models\Borrow;
use App\Repositories\Contracts\BorrowRepositoryInterface;

class BorrowRepository implements BorrowRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Borrow::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('borrow_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('borrow_date', '<=', $filters['to_date']);
        }

        return $query->with(['user', 'book'])
            ->orderBy('borrow_date', 'desc')
            ->get();
    }

    public function getOverdueBorrows()
    {
        return Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getUserOverdueBorrows($userId)
    {
        return Borrow::where('user_id', $userId)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function details($id)
    {
        return Borrow::with(['user', 'book'])->find($id);
    }

    public function create(array $data)
    {
        return Borrow::create($data);
    }

    public function update(Borrow $borrow, array $data)
    {
        $borrow->update($data);
        return $borrow;
    }

    public function delete($borrow)
    {
        $borrow->delete();
        return true;
    }

    public function getReturnedBorrows()
    {
        return Borrow::where('status', 'returned')
            ->orderBy('return_date', 'desc')
            ->get();
    }

    // public function search($query)
    // {
    //     // Implementation for searching borrow records
    // }
}
