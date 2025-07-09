<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *  path="/api/v1/users",
     *  tags={"Users"},
     *  summary="Get list of users (Admin only)",
     *  security={{"sanctum":{}}},
     *  @OA\Response(
     *      response=200,
     *      description="Users retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  )
     * )
     */
    public function index()
    {
        $users = $this->userService->getAll();
        return $this->success($users, 'Users retrieved successfully');
    }

    /**
     * @OA\Get(
     *  path="/api/v1/users/{id}",
     *  tags={"Users"},
     *  summary="Get user details",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="User retrieved successfully",
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
     *      description="User not found",
     *  )
     * )
     */
    public function show(Request $request, $id)
    {
        $auth = $request->user();
        if (!$auth->isAdmin() && $auth->id !== (int)$id) {
            return $this->error('Unauthorized access', [], 403);
        }

        $user = $this->userService->details($id);
        if (!$user) {
            return $this->error('User not found', [], 404);
        }

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/users/{id}",
     *  tags={"Users"},
     *  summary="Update user",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      @OA\JsonContent(
     *          @OA\Property(property="name", type="string", example="John Doe"),
     *          @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *          @OA\Property(property="role", type="string", enum={"member", "admin"}, example="member")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="User updated successfully",
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
     *      description="User not found",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function update(Request $request, $id)
    {
        $auth = $request->user();
        if (!$auth->isAdmin() && $auth->id !== (int)$id) {
            return $this->error('Unauthorized access', [], 403);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $id,
        ];

        if ($auth->isAdmin()) {
            $rules['role'] = 'sometimes|required|in:member,admin';
        }

        $data = $request->validate($rules);
        if (!$auth->isAdmin() && isset($data['role'])) {
            unset($data['role']);
        }

        $user = $this->userService->update($id, $data);
        if (!$user) {
            return $this->error('User not found or update failed', [], 404);
        }

        return $this->success($user, 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/users/{id}",
     *  tags={"Users"},
     *  summary="Delete user",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=204,
     *      description="User deleted successfully",
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
     *      description="User not found",
     *  )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $auth = $request->user();
        if (!$auth->isAdmin() && $auth->id !== (int)$id) {
            return $this->error('Unauthorized access', [], 403);
        }

        $this->userService->delete($id);

        return $this->success(null, 'User deleted successfully', 204);
    }
}
