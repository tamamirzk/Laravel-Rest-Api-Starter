<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->register_repositories();
    }
    
    /**
     * Configure global rate limiter
     *
     * @return void
     */
    public function boot()
    {
        app(\Illuminate\Cache\RateLimiter::class)->for('global', function () {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by(request()->ip());
        });
        $this->register_repositories();
    }

    private function register_repositories()
    {
        $this->app->bind('App\Repositories\Contracts\ICategoryRepository', 'App\Repositories\CategoryRepository');
        $this->app->bind('App\Repositories\Contracts\IProductRepository', 'App\Repositories\ProductRepository');
        $this->app->bind('App\Repositories\Contracts\IVariantRepository', 'App\Repositories\VariantRepository');
        $this->app->bind('App\Repositories\Contracts\IShippingRepository', 'App\Repositories\ShippingRepository');
        $this->app->bind('App\Repositories\Contracts\ICartRepository', 'App\Repositories\CartRepository');
        $this->app->bind('App\Repositories\Contracts\IMarketProductRepository', 'App\Repositories\MarketProductRepository');
        $this->app->bind('App\Repositories\Contracts\IMarketVoucherRepository', 'App\Repositories\MarketVoucherRepository');
        $this->app->bind('App\Repositories\Contracts\IMarketShippingMethodRepository', 'App\Repositories\MarketShippingMethodRepository');
        $this->app->bind('App\Repositories\Contracts\IMarketBannerRepository', 'App\Repositories\MarketBannerRepository');
        $this->app->bind('App\Repositories\Contracts\IMarketRepository', 'App\Repositories\MarketRepository');
        $this->app->bind('App\Repositories\Contracts\IUserRepository', 'App\Repositories\UserRepository');
        $this->app->bind('App\Repositories\Contracts\IOrderRepository', 'App\Repositories\OrderRepository');
        $this->app->bind('App\Repositories\Contracts\IOrderComplaintRepository', 'App\Repositories\OrderComplaintRepository');
    }

}
