<?php

namespace App\Providers;

use App\Models\Order;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\Contracts\BorrowRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Eloquent\BorrowRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(BookRepositoryInterface::class, BookRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BorrowRepositoryInterface::class, BorrowRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Making sure its not redirecting to a web page
        Authenticate::redirectUsing(function (Request $request) {
            throw new AuthenticationException(
                response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                    'code' => 401,
                ], 401)
            );
        });
    }
}
