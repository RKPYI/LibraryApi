<?php

namespace App\Services;

use App\Models\Borrow;
use App\Models\User;
use App\Repositories\Contracts\BorrowRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BorrowService
{
    protected $borrowRepo;

    public function __construct(BorrowRepositoryInterface $borrowRepo)
    {
        $this->borrowRepo = $borrowRepo;
    }

    public function getAll($user, array $filters = [])
    {

        if (!$user->isAdmin()) {
            $filters['user_id'] = $user->id;
        }

        $borrows = $this->borrowRepo->getAll($filters);

        foreach ($borrows as $borrow) {
            if (
                $borrow->status === Borrow::STATUS_BORROWED &&
                $borrow->due_date &&
                now()->greaterThan($borrow->due_date)
            ) {
                $borrow->status = Borrow::STATUS_OVERDUE;
            }
        }

        return $borrows;
    }

    public function getOverdueBorrows(User $user)
    {
        if (!$user->isAdmin()) {
            return $this->borrowRepo->getUserOverdueBorrows($user->id);
        }

        return $this->borrowRepo->getOverdueBorrows();
    }

    public function details($id, User $user)
    {
        $borrow = $this->borrowRepo->details($id);
        if (!$borrow) {
            throw new ModelNotFoundException("Borrow not found.");
        }

        if ($user->isAdmin() || $borrow->user_id === $user->id) {
            return $borrow;
        }

        throw new AuthorizationException('Unauthorized access to borrow details.');
    }

    public function create(array $data, User $user)
    {
        $data['user_id'] = $user->id;
        $data['borrow_date'] = null;
        $data['status'] = 'pending';

        return $this->borrowRepo->create($data);
    }

    public function approveBorrow(Borrow $borrow, array $data)
    {
        if ($borrow->status !== Borrow::STATUS_PENDING) {
            throw new \Exception("Cannot approve borrow request unless it is pending.");
        }

        $book = $borrow->book;
        if ($book->stock < 1) {
            throw new \Exception("Book is out of stock.");
        }

        $book->decrement('stock');

        return $this->borrowRepo->update($borrow, [
            'status' => Borrow::STATUS_BORROWED,
            'borrow_date' => $data['borrow_date'] ?? now(),
            'due_date' => $data['due_date'] ?? now()->addDays(14),
            'return_date' => null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    // User can cancel their own borrow request
    public function rejectBorrow(Borrow $borrow, array $data, User $user)
    {
        if ($borrow->status !== Borrow::STATUS_PENDING) {
            throw new \Exception("Cannot reject borrow request unless it is pending.");
        }

        // Only the user who created the borrow request or an admin can reject it
        if ($borrow->user_id !== $user->id && !$user->isAdmin()) {
            throw new AuthorizationException('Unauthorized to reject this borrow request.');
        }

        $cancelledBy = $user->isAdmin() ? 'admin' : 'user';
        $note = $data['notes'] ?? 'Borrow request was cancelled';
        $note .= ' by: ' . $cancelledBy;

        return $this->borrowRepo->update($borrow, [
            'status' => Borrow::STATUS_CANCELLED,
            'due_date' => null,
            'return_date' => null,
            'notes' => $note,
        ]);

    }

    public function delete($borrow)
    {
        return $this->borrowRepo->delete($borrow);
    }

    public function requestReturnBook(Borrow $borrow, array $data)
    {
        $allowedStatuses = [
            Borrow::STATUS_BORROWED,
            Borrow::STATUS_OVERDUE,
            Borrow::STATUS_RETURN_REJECTED,
        ];

        if (!in_array($borrow->status, $allowedStatuses)) {
            throw new \Exception("Cannot return book unless it is currently borrowed.");
        }

        return $this->borrowRepo->update($borrow, [
            'status' => Borrow::STATUS_RETURN_REQUESTED,
            'return_date' => now(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function approveReturnBook(Borrow $borrow, array $data)
    {
        if ($borrow->status !== Borrow::STATUS_RETURN_REQUESTED) {
            throw new \Exception("Cannot approve return unless it is requested.");
        }

        $book = $borrow->book;
        $book->increment('stock');

        return $this->borrowRepo->update($borrow, [
            'status' => Borrow::STATUS_RETURNED,
            'return_date' => now(),
            'notes' => $data['notes'] ?? $borrow->notes ?? null,
        ]);
    }

    public function rejectReturnBook(Borrow $borrow, array $data)
    {
        if ($borrow->status !== Borrow::STATUS_RETURN_REQUESTED) {
            throw new \Exception("Cannot reject return unless it is requested.");
        }

        return $this->borrowRepo->update($borrow, [
            'status' => Borrow::STATUS_RETURN_REJECTED,
            'due_date' => $borrow->due_date, // Keep the original due date
            'return_date' => null, // Reset return date
            'notes' => $data['notes'] ?? $borrow->notes ?? null,
        ]);
    }

    // public function search($query)
    // {
    //     // Implementation for searching borrow records
    // }
}
