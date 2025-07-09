<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *  path="/api/v1/auth/register",
     *  tags={"Authentication"},
     *  summary="Register a new user",
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"name", "email", "password", "password_confirmation"},
     *          @OA\Property(property="name", type="string", example="John Doe"),
     *          @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *          @OA\Property(property="password", type="string", format="password", example="password123"),
     *          @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *      ),
     *  ),
     *  @OA\Response(
     *      response=201,
     *      description="User registered successfully",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->authService->register($data);

        return $this->success($user, 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/auth/login",
     *  tags={"Authentication"},
     *  summary="Login a user",
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"email", "password"},
     *          @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *          @OA\Property(property="password", type="string", format="password", example="password123")
     *      ),
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="User logged in successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Invalid credentials",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = $this->authService->authenticate($credentials);

        if (!$user) {
            return $this->error('Invalid credentials', [], 401);
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return $this->success(['user' => $user, 'token' => $token], 'User logged in successfully');
    }

    /**
     * @OA\Post(
     *  path="/api/v1/auth/logout",
     *  tags={"Authentication"},
     *  summary="Logout a user",
     *  security={{"sanctum":{}}},
     *  @OA\Response(
     *      response=204,
     *      description="User logged out successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  )
     * )
     */
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'User logged out successfully', 204);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/auth/me",
     *  tags={"Authentication"},
     *  summary="Get current user details",
     *  security={{"sanctum":{}}},
     *  @OA\Response(
     *      response=200,
     *      description="User retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  )
     * )
     */
    public function me(Request $request)
    {
        return $this->success($request->user(), 'User retrieved successfully');
    }
}
