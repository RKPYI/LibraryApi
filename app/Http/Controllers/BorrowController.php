<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Services\BorrowService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    use ApiResponse;

    protected $borrowService;

    public function __construct(BorrowService $borrowService)
    {
        $this->borrowService = $borrowService;
    }

    /**
     * @OA\Get(
     *  path="/api/v1/borrows",
     *  tags={"Borrows"},
     *  summary="Get list of borrows",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="status",
     *      in="query",
     *      @OA\Schema(type="string", enum={"pending", "borrowed", "overdue", "returned", "cancelled", "return_requested", "return_rejected"})
     *  ),
     *  @OA\Parameter(
     *      name="book_id",
     *      in="query",
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Parameter(
     *      name="from_date",
     *      in="query",
     *      @OA\Schema(type="string", format="date")
     *  ),
     *  @OA\Parameter(
     *      name="to_date",
     *      in="query",
     *      @OA\Schema(type="string", format="date")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Borrow records retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'book_id', 'from_date', 'to_date']);
        $borrows = $this->borrowService->getAll($request->user(), $filters);

        return $this->success($borrows, 'Borrow records retrieved successfully');
    }

    /**
     * @OA\Post(
     *  path="/api/v1/borrows",
     *  tags={"Borrows"},
     *  summary="Request a borrow (Member action)",
     *  security={{"sanctum":{}}},
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"book_id"},
     *          @OA\Property(property="book_id", type="integer", example=1),
     *          @OA\Property(property="notes", type="string", example="I would like to borrow this book.")
     *      )
     *  ),
     *  @OA\Response(
     *      response=201,
     *      description="Borrow record created successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'due_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $borrow = $this->borrowService->create($data, $request->user());

        return $this->success($borrow, 'Borrow record created successfully', 201);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/borrows/{borrow}",
     *  tags={"Borrows"},
     *  summary="Get borrow details",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Borrow record retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function show(Borrow $borrow)
    {
        $borrowDetails = $this->borrowService->details($borrow->id, request()->user());
        return $this->success($borrowDetails, 'Borrow record retrieved successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/borrows/{borrow}",
     *  tags={"Borrows"},
     *  summary="Approve a borrow (Admin action)",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="due_date", type="string", format="date", example="2025-07-23"),
     *          @OA\Property(property="notes", type="string", example="Approved")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Borrow record updated successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function approveBorrow(Request $request, Borrow $borrow)
    {
        $data = $request->validate([
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $updatedBorrow = $this->borrowService->approveBorrow($borrow, $data);

        return $this->success($updatedBorrow, 'Borrow record updated successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/borrows/{borrow}/reject",
     *  tags={"Borrows"},
     *  summary="Reject & Cancel a borrow",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="notes", type="string", example="Rejected due to some reason.")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Borrow request rejected successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function rejectBorrow(Request $request, Borrow $borrow)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $updatedBorrow = $this->borrowService->rejectBorrow($borrow, $data, $request->user());

        return $this->success($updatedBorrow, 'Borrow request rejected successfully');
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/borrows/{borrow}",
     *  tags={"Borrows"},
     *  summary="Delete a borrow record (Admin action)",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=204,
     *      description="Borrow record deleted successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function destroy(Borrow $borrow)
    {
        $this->borrowService->delete($borrow);

        return $this->success(null, 'Borrow record deleted successfully', 204);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/borrows/overdue",
     *  tags={"Borrows"},
     *  summary="Get overdue borrows",
     *  security={{"sanctum":{}}},
     *  @OA\Response(
     *      response=200,
     *      description="Overdue borrow records retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  )
     * )
     */
    public function overdue(Request $request)
    {
        $overdueBorrows = $this->borrowService->getOverdueBorrows($request->user());

        return $this->success($overdueBorrows, 'Overdue borrow records retrieved successfully');
    }

    /**
     * @OA\Post(
     *  path="/api/v1/returns/{borrow}/request",
     *  tags={"Returns"},
     *  summary="Request to return a book",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="notes", type="string", example="Returning the book.")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Return request submitted successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function requestReturn(Request $request, Borrow $borrow)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $updatedBorrow = $this->borrowService->requestReturnBook($borrow, $data);

        return $this->success($updatedBorrow, 'Return request submitted successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/returns/{borrow}/approve",
     *  tags={"Returns"},
     *  summary="Approve a book return (Admin action)",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="notes", type="string", example="Return approved.")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Return approved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function approveReturn(Request $request, Borrow $borrow)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $updatedBorrow = $this->borrowService->approveReturnBook($borrow, $data);

        return $this->success($updatedBorrow, 'Return approved successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/returns/{borrow}/reject",
     *  tags={"Returns"},
     *  summary="Reject a book return (Admin action)",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="borrow",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="notes", type="string", example="Return rejected.")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Return rejected successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Borrow not found",
     *  )
     * )
     */
    public function rejectReturn(Request $request, Borrow $borrow)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $updatedBorrow = $this->borrowService->rejectReturnBook($borrow, $data);

        return $this->success($updatedBorrow, 'Return rejected successfully');
    }
}
