<?php

namespace App\Providers;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Policies\OwnedCatalogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(MuscleGroup::class, OwnedCatalogPolicy::class);
        Gate::policy(Exercise::class, OwnedCatalogPolicy::class);
    }
}
