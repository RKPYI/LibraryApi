<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use App\Repositories\Contracts\BorrowRepositoryInterface;
use App\Services\BorrowService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BorrowServiceTest extends TestCase
{
    protected $borrowRepoMock;
    protected $borrowService;
    protected $adminUser;
    protected $memberUser;
    protected $book;
    protected $borrow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->borrowRepoMock = Mockery::mock(BorrowRepositoryInterface::class);
        $this->borrowService = new BorrowService($this->borrowRepoMock);

        // Create test users
        $this->adminUser = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        $this->memberUser = new User([
            'id' => 2,
            'name' => 'Member User',
            'email' => 'member@example.com',
            'role' => 'member'
        ]);

        // Create test book (mocked to avoid database interactions)
        $this->book = Mockery::mock(Book::class);
        $this->book->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->book->shouldReceive('getAttribute')->with('title')->andReturn('Test Book');
        $this->book->shouldReceive('getAttribute')->with('author')->andReturn('Test Author');
        $this->book->shouldReceive('getAttribute')->with('stock')->andReturn(5);
        $this->book->shouldReceive('offsetGet')->with('stock')->andReturn(5);
        $this->book->shouldReceive('offsetExists')->with('stock')->andReturn(true);

        // Create test borrow
        $this->borrow = new Borrow([
            'id' => 1,
            'user_id' => $this->memberUser->id,
            'book_id' => 1,
            'status' => Borrow::STATUS_PENDING,
            'borrow_date' => null,
            'due_date' => null,
            'return_date' => null,
            'notes' => null
        ]);
        $this->borrow->user = $this->memberUser;
        $this->borrow->book = $this->book;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_all_borrows_for_admin()
    {
        $filters = ['status' => 'pending'];
        $borrows = new Collection([$this->borrow]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->with($filters)
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->adminUser, $filters);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_can_get_all_borrows_for_member_with_user_filter()
    {
        $filters = [];
        $expectedFilters = ['user_id' => $this->memberUser->id];
        $borrows = new Collection([$this->borrow]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->with($expectedFilters)
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->memberUser, $filters);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_updates_borrowed_status_to_overdue_when_past_due_date()
    {
        $borrowedBorrow = new Borrow([
            'id' => 1,
            'status' => Borrow::STATUS_BORROWED,
            'due_date' => Carbon::now()->subDays(1) // Past due date
        ]);

        $borrows = new Collection([$borrowedBorrow]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->adminUser);

        $this->assertEquals(Borrow::STATUS_OVERDUE, $borrowedBorrow->status);
    }

    #[Test]
    public function it_can_get_overdue_borrows_for_admin()
    {
        $overdueBorrows = new Collection([]);

        $this->borrowRepoMock
            ->shouldReceive('getOverdueBorrows')
            ->once()
            ->andReturn($overdueBorrows);

        $result = $this->borrowService->getOverdueBorrows($this->adminUser);

        $this->assertInstanceOf(Collection::class, $result);
    }

    #[Test]
    public function it_can_get_overdue_borrows_for_member()
    {
        $overdueBorrows = new Collection([]);

        $this->borrowRepoMock
            ->shouldReceive('getUserOverdueBorrows')
            ->once()
            ->with($this->memberUser->id)
            ->andReturn($overdueBorrows);

        $result = $this->borrowService->getOverdueBorrows($this->memberUser);

        $this->assertInstanceOf(Collection::class, $result);
    }

    #[Test]
    public function it_can_get_borrow_details_for_admin()
    {
        $this->borrowRepoMock
            ->shouldReceive('details')
            ->once()
            ->with(1)
            ->andReturn($this->borrow);

        $result = $this->borrowService->details(1, $this->adminUser);

        $this->assertEquals($this->borrow, $result);
    }

    #[Test]
    public function it_can_get_borrow_details_for_owner()
    {
        $this->borrowRepoMock
            ->shouldReceive('details')
            ->once()
            ->with(1)
            ->andReturn($this->borrow);

        $result = $this->borrowService->details(1, $this->memberUser);

        $this->assertEquals($this->borrow, $result);
    }

    #[Test]
    public function it_throws_authorization_exception_when_non_owner_accesses_borrow_details()
    {
        $otherUser = new User([
            'id' => 3,
            'name' => 'Other User',
            'role' => 'member'
        ]);
        $this->borrow->user_id = 2; // assign manual, karena ID tidak auto-set
        $this->borrow->setAttribute('user_id', 2); // opsional, eksplisit

        $otherUser->id = 3; // assign manual juga


        $this->borrowRepoMock
            ->shouldReceive('details')
            ->once()
            ->with(1)
            ->andReturn($this->borrow);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Unauthorized access to borrow details.');

        $this->borrowService->details(1, $otherUser);
    }

    #[Test]
    public function it_can_create_borrow_request()
    {
        $data = [
            'book_id' => 1,
            'due_date' => Carbon::now()->addDays(14),
            'notes' => 'Test notes'
        ];

        $this->borrowRepoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->memberUser->id &&
                       $arg['book_id'] === 1 &&
                       $arg['status'] === 'pending' &&
                       $arg['borrow_date'] === null;
            }))
            ->andReturn($this->borrow);

        $result = $this->borrowService->create($data, $this->memberUser);

        $this->assertEquals($this->borrow, $result);
    }

    #[Test]
    public function it_can_approve_borrow_request()
    {
        $borrowRequest = new Borrow([
            'id' => 1,
            'status' => Borrow::STATUS_PENDING,
            'user_id' => $this->memberUser->id,
            'book_id' => 1
        ]);

        // Mock the book with proper stock management
        $mockBook = Mockery::mock(Book::class)->shouldAllowMockingProtectedMethods();
        $mockBook->shouldReceive('getAttribute')->with('stock')->andReturn(5);
        $mockBook->shouldReceive('decrement')->with('stock')->once();
        $borrowRequest->book = $mockBook;

        $approvalData = [
            'borrow_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(14),
            'notes' => 'Approved'
        ];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($borrowRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_BORROWED &&
                       isset($data['borrow_date']) &&
                       isset($data['due_date']) &&
                       $data['return_date'] === null;
            }))
            ->andReturn($borrowRequest);

        $result = $this->borrowService->approveBorrow($borrowRequest, $approvalData);

        $this->assertEquals($borrowRequest, $result);
    }

    #[Test]
    public function it_throws_exception_when_approving_non_pending_borrow()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_BORROWED
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot approve borrow request unless it is pending.');

        $this->borrowService->approveBorrow($borrowRequest, []);
    }

    #[Test]
    public function it_throws_exception_when_book_out_of_stock()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_PENDING
        ]);

        $outOfStockBook = Mockery::mock(Book::class);
        $outOfStockBook->shouldReceive('getAttribute')->with('stock')->andReturn(0);
        $borrowRequest->book = $outOfStockBook;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Book is out of stock.');

        $this->borrowService->approveBorrow($borrowRequest, []);
    }

    #[Test]
    public function it_can_reject_borrow_request_by_admin()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_PENDING,
            'user_id' => $this->memberUser->id
        ]);

        $rejectionData = ['notes' => 'Not available'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($borrowRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_CANCELLED &&
                       $data['due_date'] === null &&
                       $data['return_date'] === null &&
                       str_contains($data['notes'], 'by: admin');
            }))
            ->andReturn($borrowRequest);

        $result = $this->borrowService->rejectBorrow($borrowRequest, $rejectionData, $this->adminUser);

        $this->assertEquals($borrowRequest, $result);
    }

    #[Test]
    public function it_can_reject_borrow_request_by_user()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_PENDING,
            'user_id' => $this->memberUser->id
        ]);

        $rejectionData = ['notes' => 'Changed mind'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($borrowRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_CANCELLED &&
                       str_contains($data['notes'], 'by: user');
            }))
            ->andReturn($borrowRequest);

        $result = $this->borrowService->rejectBorrow($borrowRequest, $rejectionData, $this->memberUser);

        $this->assertEquals($borrowRequest, $result);
    }

    #[Test]
    public function it_throws_exception_when_rejecting_non_pending_borrow()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_BORROWED,
            'user_id' => $this->memberUser->id
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reject borrow request unless it is pending.');

        $this->borrowService->rejectBorrow($borrowRequest, [], $this->memberUser);
    }

    #[Test]
    public function it_throws_authorization_exception_when_unauthorized_user_rejects_borrow()
    {
        $otherUser = new User([
            'id' => 3,
            'role' => 'member'
        ]);

        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_PENDING,
            'user_id' => $this->memberUser->id
        ]);

        $this->borrow->user_id = 2; // assign manual, karena ID tidak auto-set
        $this->borrow->setAttribute('user_id', 2); // opsional, eksplisit
        $otherUser->id = 3; // assign manual juga

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Unauthorized to reject this borrow request.');

        $this->borrowService->rejectBorrow($borrowRequest, [], $otherUser);
    }

    #[Test]
    public function it_can_delete_borrow()
    {
        $this->borrowRepoMock
            ->shouldReceive('delete')
            ->once()
            ->with($this->borrow)
            ->andReturn(true);

        $result = $this->borrowService->delete($this->borrow);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_request_return_book_from_borrowed_status()
    {
        $borrowedBook = new Borrow([
            'status' => Borrow::STATUS_BORROWED
        ]);

        $returnData = ['notes' => 'Returning book'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($borrowedBook, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURN_REQUESTED &&
                       isset($data['return_date']) &&
                       $data['notes'] === 'Returning book';
            }))
            ->andReturn($borrowedBook);

        $result = $this->borrowService->requestReturnBook($borrowedBook, $returnData);

        $this->assertEquals($borrowedBook, $result);
    }

    #[Test]
    public function it_can_request_return_book_from_overdue_status()
    {
        $overdueBorrow = new Borrow([
            'status' => Borrow::STATUS_OVERDUE
        ]);

        $returnData = ['notes' => 'Late return'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($overdueBorrow, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURN_REQUESTED;
            }))
            ->andReturn($overdueBorrow);

        $result = $this->borrowService->requestReturnBook($overdueBorrow, $returnData);

        $this->assertEquals($overdueBorrow, $result);
    }

    #[Test]
    public function it_can_request_return_book_from_return_rejected_status()
    {
        $rejectedReturn = new Borrow([
            'status' => Borrow::STATUS_RETURN_REJECTED
        ]);

        $returnData = ['notes' => 'Requesting again'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($rejectedReturn, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURN_REQUESTED;
            }))
            ->andReturn($rejectedReturn);

        $result = $this->borrowService->requestReturnBook($rejectedReturn, $returnData);

        $this->assertEquals($rejectedReturn, $result);
    }

    #[Test]
    public function it_throws_exception_when_requesting_return_from_invalid_status()
    {
        $pendingBorrow = new Borrow([
            'status' => Borrow::STATUS_PENDING
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot return book unless it is currently borrowed.');

        $this->borrowService->requestReturnBook($pendingBorrow, []);
    }

    #[Test]
    public function it_can_approve_return_book()
    {
        $returnRequest = new Borrow([
            'status' => Borrow::STATUS_RETURN_REQUESTED,
            'notes' => 'Original notes'
        ]);

        $mockBook = Mockery::mock(Book::class)->shouldAllowMockingProtectedMethods();
        $mockBook->shouldReceive('increment')->with('stock')->once();
        $returnRequest->book = $mockBook;

        $approvalData = ['notes' => 'Book returned in good condition'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($returnRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURNED &&
                       isset($data['return_date']) &&
                       $data['notes'] === 'Book returned in good condition';
            }))
            ->andReturn($returnRequest);

        $result = $this->borrowService->approveReturnBook($returnRequest, $approvalData);

        $this->assertEquals($returnRequest, $result);
    }

    #[Test]
    public function it_throws_exception_when_approving_return_from_invalid_status()
    {
        $borrowedBook = new Borrow([
            'status' => Borrow::STATUS_BORROWED
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot approve return unless it is requested.');

        $this->borrowService->approveReturnBook($borrowedBook, []);
    }

    #[Test]
    public function it_can_reject_return_book()
    {
        $returnRequest = new Borrow([
            'status' => Borrow::STATUS_RETURN_REQUESTED,
            'due_date' => Carbon::now()->addDays(7),
            'notes' => 'Original notes'
        ]);

        $rejectionData = ['notes' => 'Book is damaged'];

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($returnRequest, Mockery::on(function ($data) use ($returnRequest) {
                return $data['status'] === Borrow::STATUS_RETURN_REJECTED &&
                       $data['due_date'] === $returnRequest->due_date &&
                       $data['return_date'] === null &&
                       $data['notes'] === 'Book is damaged';
            }))
            ->andReturn($returnRequest);

        $result = $this->borrowService->rejectReturnBook($returnRequest, $rejectionData);

        $this->assertEquals($returnRequest, $result);
    }

    #[Test]
    public function it_throws_exception_when_rejecting_return_from_invalid_status()
    {
        $borrowedBook = new Borrow([
            'status' => Borrow::STATUS_BORROWED
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reject return unless it is requested.');

        $this->borrowService->rejectReturnBook($borrowedBook, []);
    }

    #[Test]
    public function it_uses_default_values_when_approving_borrow_without_dates()
    {
        $borrowRequest = new Borrow([
            'status' => Borrow::STATUS_PENDING
        ]);

        $mockBook = Mockery::mock(Book::class)->shouldAllowMockingProtectedMethods();
        $mockBook->shouldReceive('getAttribute')->with('stock')->andReturn(5);
        $mockBook->shouldReceive('decrement')->with('stock')->once();
        $borrowRequest->book = $mockBook;

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($borrowRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_BORROWED &&
                       $data['borrow_date'] instanceof Carbon &&
                       $data['due_date'] instanceof Carbon &&
                       $data['return_date'] === null;
            }))
            ->andReturn($borrowRequest);

        $result = $this->borrowService->approveBorrow($borrowRequest, []);

        $this->assertEquals($borrowRequest, $result);
    }

    #[Test]
    public function it_preserves_existing_notes_when_approving_return_without_new_notes()
    {
        $returnRequest = new Borrow([
            'status' => Borrow::STATUS_RETURN_REQUESTED,
            'notes' => 'Original notes'
        ]);

        $mockBook = Mockery::mock(Book::class)->shouldAllowMockingProtectedMethods();
        $mockBook->shouldReceive('increment')->with('stock')->once();
        $returnRequest->book = $mockBook;

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($returnRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURNED &&
                       $data['notes'] === 'Original notes';
            }))
            ->andReturn($returnRequest);

        $result = $this->borrowService->approveReturnBook($returnRequest, []);

        $this->assertEquals($returnRequest, $result);
    }

    #[Test]
    public function it_preserves_existing_notes_when_rejecting_return_without_new_notes()
    {
        $returnRequest = new Borrow([
            'status' => Borrow::STATUS_RETURN_REQUESTED,
            'due_date' => Carbon::now()->addDays(7),
            'notes' => 'Original notes'
        ]);

        $this->borrowRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($returnRequest, Mockery::on(function ($data) {
                return $data['status'] === Borrow::STATUS_RETURN_REJECTED &&
                       $data['notes'] === 'Original notes';
            }))
            ->andReturn($returnRequest);

        $result = $this->borrowService->rejectReturnBook($returnRequest, []);

        $this->assertEquals($returnRequest, $result);
    }

    #[Test]
    public function it_applies_user_filter_when_member_has_existing_filters()
    {
        $filters = ['status' => 'borrowed', 'book_id' => 1];
        $expectedFilters = ['status' => 'borrowed', 'book_id' => 1, 'user_id' => $this->memberUser->id];
        $borrows = new Collection([]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->with($expectedFilters)
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->memberUser, $filters);

        $this->assertInstanceOf(Collection::class, $result);
    }

    #[Test]
    public function it_does_not_update_status_when_due_date_is_null()
    {
        $borrowedBorrow = new Borrow([
            'id' => 1,
            'status' => Borrow::STATUS_BORROWED,
            'due_date' => null // No due date
        ]);

        $borrows = new Collection([$borrowedBorrow]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->adminUser);

        $this->assertEquals(Borrow::STATUS_BORROWED, $borrowedBorrow->status);
    }

    #[Test]
    public function it_does_not_update_status_when_due_date_is_future()
    {
        $borrowedBorrow = new Borrow([
            'id' => 1,
            'status' => Borrow::STATUS_BORROWED,
            'due_date' => Carbon::now()->addDays(1) // Future due date
        ]);

        $borrows = new Collection([$borrowedBorrow]);

        $this->borrowRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($borrows);

        $result = $this->borrowService->getAll($this->adminUser);

        $this->assertEquals(Borrow::STATUS_BORROWED, $borrowedBorrow->status);
    }
}
