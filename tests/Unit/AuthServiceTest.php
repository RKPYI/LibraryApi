<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected $authRepoMock;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a mock for the AuthRepositoryInterface
        $this->authRepoMock = Mockery::mock(AuthRepositoryInterface::class);
        // Create an instance of AuthService with the mock
        $this->authService = new AuthService($this->authRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_register_a_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        // We expect the createUser method to be called once with the user data
        $this->authRepoMock
            ->shouldReceive('createUser')
            ->once()
            ->with($userData)
            ->andReturn(new User($userData));

        $user = $this->authService->register($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['email'], $user->email);
    }

    #[Test]
    public function it_can_authenticate_a_user_with_valid_credentials()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        $user = new User([
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);

        // Mock the repository to return the user
        $this->authRepoMock
            ->shouldReceive('findUserByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        $authenticatedUser = $this->authService->authenticate($credentials);

        $this->assertInstanceOf(User::class, $authenticatedUser);
        $this->assertEquals($credentials['email'], $authenticatedUser->email);
    }

    #[Test]
    public function it_returns_null_for_authentication_with_invalid_password()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $user = new User([
            'email' => $credentials['email'],
            'password' => Hash::make('correctpassword')
        ]);

        $this->authRepoMock
            ->shouldReceive('findUserByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn($user);

        $result = $this->authService->authenticate($credentials);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_for_a_nonexistent_user()
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password'
        ];

        // Mock the repository to return null (user not found)
        $this->authRepoMock
            ->shouldReceive('findUserByEmail')
            ->once()
            ->with($credentials['email'])
            ->andReturn(null);

        $result = $this->authService->authenticate($credentials);

        $this->assertNull($result);
    }
}
