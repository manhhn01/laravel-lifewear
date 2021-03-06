<?php

namespace App\Providers;

use App\Repositories\CartProducts\CartProductRepository;
use App\Repositories\CartProducts\CartProductRepositoryInterface;
use App\Repositories\Categories\CategoryRepository;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Repositories\OAuthProvider\OAuthProviderRepositoryInterface;
use App\Repositories\OAuthProvider\OAuthProviderRepository;
use App\Repositories\Options\OptionRepository;
use App\Repositories\Options\OptionRepositoryInterface;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\OrderRepositoryInterface;
use App\Repositories\Products\ProductRepository;
use App\Repositories\Products\ProductRepositoryInterface;
use App\Repositories\ProductVariants\ProductVariantsRepository;
use App\Repositories\ProductVariants\ProductVariantsRepositoryInterface;
use App\Repositories\ResetPasswords\ResetPasswordRepository;
use App\Repositories\ResetPasswords\ResetPasswordRepositoryInterface;
use App\Repositories\Users\UserRepository;
use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public $singletons = [
        ProductRepositoryInterface::class => ProductRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        ResetPasswordRepositoryInterface::class => ResetPasswordRepository::class,
        ProductVariantsRepositoryInterface::class => ProductVariantsRepository::class,
        CartProductRepositoryInterface::class => CartProductRepository::class,
        OptionRepositoryInterface::class => OptionRepository::class,
        OAuthProviderRepositoryInterface::class => OAuthProviderRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
