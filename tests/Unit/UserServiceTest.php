<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    protected $userRepoMock;
    protected $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepoMock = Mockery::mock(UserRepositoryInterface::class);
        $this->userService = new UserService($this->userRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_all_users()
    {
        $this->userRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn(new Collection([new User(), new User()]));

        $users = $this->userService->getAll();

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);
    }

    #[Test]
    public function it_can_get_user_details()
    {
        $user = new User(['id' => 1, 'name' => 'Test User']);
        $this->userRepoMock
            ->shouldReceive('details')
            ->once()
            ->with(1)
            ->andReturn($user);

        $foundUser = $this->userService->details(1);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals('Test User', $foundUser->name);
    }

    #[Test]
    public function it_can_update_a_user()
    {
        $data = ['name' => 'Updated Name'];
        $user = new User(['id' => 1, 'name' => 'Original Name']);

        $this->userRepoMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($user->fill($data));

        $updatedUser = $this->userService->update(1, $data);

        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals('Updated Name', $updatedUser->name);
    }

    #[Test]
    public function it_can_delete_a_user()
    {
        $this->userRepoMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->userService->delete(1);

        $this->assertTrue($result);
    }
}
