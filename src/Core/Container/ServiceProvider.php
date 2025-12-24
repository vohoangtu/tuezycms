<?php

declare(strict_types=1);

namespace Core\Container;

/**
 * Base Service Provider class
 * Simplified version inspired by Illuminate\Support\ServiceProvider
 */
abstract class ServiceProvider
{
    /**
     * The container instance.
     *
     * @var ServiceContainer
     */
    protected ServiceContainer $container;

    /**
     * Create a new service provider instance.
     *
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}

